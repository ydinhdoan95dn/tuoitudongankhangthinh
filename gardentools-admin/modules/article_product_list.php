<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//

if(isset($_POST['idDel'])){
	$dir_dest = ROOT_DIR . DS .'uploads';

	$upload_id = array();
	$idDel = implode(',', $_POST['idDel']);

	$db->table = "article_product";
	$db->condition = "article_product_id IN (".$idDel.")";
	$db->order = "";
	$rows = $db->select();
	$i = 0;
	foreach($rows as $row) {
		// Xóa hình article
		$mask = $dir_dest . DS . "article" . DS . '*'.$row['img'];
		if(!empty($row['img']) && glob($mask)) {
			array_map("unlink", glob($mask));
		}

		// Xóa hình vị trí sản phẩm (nếu có)
		if(!empty($row['product_location_img'])) {
			$loc_path = $dir_dest . DS . "product" . DS . $row['product_location_img'];
			if(file_exists($loc_path)) {
				unlink($loc_path);
			}
		}

		// Xóa video sản phẩm (nếu có)
		if(!empty($row['product_video']) && $row['product_video_type'] == 'upload') {
			$video_path = $dir_dest . DS . "product" . DS . "video" . DS . $row['product_video'];
			if(file_exists($video_path)) {
				@unlink($video_path);
			}
		}

		$upload_id[$i] = $row['upload_id'];

		$list_img = "";
		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->order = "";
		$rows_tmp = $db->select();
		foreach ($rows_tmp as $row_tmp){
			$list_img = $row_tmp['list_img'];
		}
		$img = explode(";",$list_img);
		if(count($img)>0) {
			for($j=0;$j<count($img);$j++) {
				if($img[$j] != ""){
					$mask = $dir_dest . DS . "photos" . DS . '*'.$img[$j];
					if (glob($mask))
						array_map("unlink", glob($mask));
				}
			}
		}

		// Xóa product gallery images
		$article_product_id = $row['article_product_id'];
		$db->table = "article_product_gallery_image";
		$db->condition = "article_product_id = ".$article_product_id;
		$db->order = "";
		$gallery_images = $db->select();
		foreach($gallery_images as $gi) {
			$mask = $dir_dest . DS . "photos" . DS . '*' . pathinfo($gi['filename'], PATHINFO_FILENAME) . '.*';
			if (glob($mask)) {
				array_map("unlink", glob($mask));
			}
		}
		$db->table = "article_product_gallery_image";
		$db->condition = "article_product_id = ".$article_product_id;
		$db->delete();

		$db->table = "article_product_gallery_category";
		$db->condition = "article_product_id = ".$article_product_id;
		$db->delete();

		$db->table = "article_product_gallery_tab";
		$db->condition = "article_product_id = ".$article_product_id;
		$db->delete();

		$i++;
	}

	if(count($upload_id) > 0) {
		$upload_id = implode(',', $upload_id);
		$db->table = "uploads_tmp";
		$db->condition = "upload_id IN (".$upload_id.")";
		$db->delete();
	}

	$db->table = "article_product";
	$db->condition = "article_product_id IN (".$idDel.")";
	$db->delete();

	loadPageSucces("Đã xóa sản phẩm thành công.", "?".TTH_PATH."=article_product_list&id=".$_POST['list_id']);
}

$article_product_menu_id = isset($_GET['id']) ? $_GET['id']+0 : 0;
$category_id_core = 3; // Sản phẩm

$db->table = "article_product_menu";
$db->condition = "article_product_menu_id = ".$article_product_menu_id;
$rows = $db->select();
$menu_name = "";
foreach($rows as $row){
	$menu_name = $row['name'];
	if(isset($row['category_id'])) {
		$category_id_core = $row['category_id'];
	}
}
if($db->RowCount==0) loadPageAdmin("Thể loại không tồn tại.", "?".TTH_PATH."=article_product_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=article_product_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=article_product_manager"><i class="fa fa-shopping-cart"></i> Sản phẩm BĐS</a>
		</li>
		<li>
			<i class="fa fa-list"></i> <?=stripslashes($menu_name)?>
		</li>
		<a class="btn-add-new" href="?<?=TTH_PATH?>=article_product_add&id=<?=$article_product_menu_id?>">Thêm sản phẩm</a>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdminTwo("article_product_list;".$category_id_core)?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=article_product_list" method="post" id="deleteProduct">
					<input type="hidden" name="list_id" value="<?=$article_product_menu_id?>" />
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th>STT</th>
							<th>Hình ảnh</th>
							<th>Tên sản phẩm</th>
							<th>Giá</th>
							<th>Trạng thái</th>
							<th>Nổi bật</th>
							<th>Lượt xem</th>
							<th>Ngày đăng</th>
							<th>Người đăng</th>
							<th>Chức năng</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$date = new DateClass();

						$db->table = "article_product";
						$db->condition = "article_product_menu_id = " . $article_product_menu_id;
						$db->limit = "";
						$db->order = "created_time DESC";
						$rows = $db->select();

						$totalpages = 0;
						$perpage = 50;
						$total = $db->RowCount;
						if($total%$perpage==0) $totalpages=$total/$perpage;
						else $totalpages = floor($total/$perpage)+1;
						if(isset($_GET["page"])) $page=$_GET["page"]+0;
						else $page=1;
						$start=($page-1)*$perpage;
						$i=0+($page-1)*$perpage;

						$db->table = "article_product";
						$db->condition = "article_product_menu_id = " . $article_product_menu_id;
						$db->order = "created_time DESC";
						$db->limit = $start.','.$perpage;
						$rows = $db->select();

						foreach($rows as $row) {
							$i++;
							// Hiển thị giá sản phẩm
							$priceText = !empty($row['product_price_text']) ? $row['product_price_text'] : '-';
							?>
							<tr>
								<td align="center"><?=$i?></td>
								<td align="center">
									<?=($row["img"]=='no' || empty($row["img"]))?
										'<img data-toggle="tooltip" data-placement="top" title="Không có hình" src="images/error.png">'
										:
										'<img id="popover-'.$i.'" class="btn-popover" title="'.stripslashes($row["name"]).'" src="images/OK.png">
										<script>
											var image = \'<img src="../uploads/article/'.$row["img"].'">\';
											$(\'#popover-'.$i.'\').popover({placement: \'bottom\', content: image, html: true});
										</script>'
									?>
								</td>
								<td><span class="tth-ellipsis"><?=stripslashes($row['name'])?></span></td>
								<td align="center">
									<span class="text-info" style="font-weight: 500;"><?=$priceText?></span>
								</td>
								<?php
								// Kiểm tra phân quyền
								// Role 1 (Admin) và 17 (CVKD) được bỏ qua check quyền cho trạng thái/nổi bật
								$canEdit = isAdminOrCVKD() ||
								           in_array("article_product_edit;".$category_id_core, $corePrivilegeSlug) ||
								           in_array("article_edit;".$category_id_core, $corePrivilegeSlug);
								$canDel = isAdminOrCVKD() ||
								          in_array("article_product_del;".$category_id_core, $corePrivilegeSlug) ||
								          in_array("article_del;".$category_id_core, $corePrivilegeSlug);

								if($canEdit) {
								?>
								<td align="center">
									<?=($row["is_active"]+0==0)?
										'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["article_product_id"].', \'is_active\', \'article_product\');" rel="1">0</div>'
										:
										'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["article_product_id"].', \'is_active\', \'article_product\');" rel="0">1</div>'
									?>
								</td>
								<td align="center">
									<?=($row["hot"]+0==0)?
										'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["article_product_id"].', \'hot\', \'article_product\');" rel="1">0</div>'
										:
										'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["article_product_id"].', \'hot\', \'article_product\');" rel="0">1</div>'
									?>
								</td>
								<?php
								} else {
								?>
								<td align="center">
									<?=($row["is_active"]+0==0)?
										'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở">0</div>'
										:
										'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng">1</div>'
									?>
								</td>
								<td align="center">
									<?=($row["hot"]+0==0)?
										'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở">0</div>'
										:
										'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng">1</div>'
									?>
								</td>
								<?php }
								?>
								<td align="center"><?=formatNumberVN($row['views']+0);?></td>
								<td align="center"><?=$date->vnDateTime($row['created_time'])?></td>
								<td align="center"><?=getUserName($row['user_id']);?></td>
								<td class="details-control" align="center">
									<div class="checkbox">
										<?php
										$href = '?'.TTH_PATH.'=article_product_edit&id='.$row['article_product_id'];
										?>
										<a href="<?=$href?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a> &nbsp;
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkboxProduct" name="idDel[]" value="<?=$row['article_product_id']?>">
										</label>
									</div>
								</td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
					<div class="row">
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages,'?'.TTH_PATH.'=article_product_list&id='.$article_product_menu_id.'&page=')?></div>
						<div class="col-sm-6" align="right" style="padding: 7px 0;">
							<label class="radio-inline"><input type="checkbox" id="selecctall" data-toggle="tooltip" data-placement="top" title="Chọn xóa tất cả" ></label>
							<input type="button" class="btn btn-primary btn-xs <?=$canDel ? "confirmManager" : "alertManager"?>" value="Xóa" name="deleteProduct">
						</div>
					</div>
				</form>
			</div>
			<!-- /.table-responsive -->
		</div>
		<!-- /.panel -->
	</div>
	<!-- /.col-lg-6 -->
</div>
<script>
	$('#dataTablesList').find('input[type="checkbox"]').shiftSelectable();
	$(document).ready(function() {
		$('#dataTablesList').dataTable( {
			"language": {
				"url": "<?=ADMIN_DIR?>/js/plugins/dataTables/de_DE.txt"
			},
			"aoColumnDefs" : [ {
				"bSortable" : false,
				"aTargets" : [ 1, 9, "no-sort" ]
			} ],
			"paging":   false,
			"info":     false,
			"order": [ 0, "asc" ]
		} );

		$('#selecctall').click(function(event) {
			if(this.checked) {
				$('.checkboxProduct').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxProduct').each(function() {
					this.checked = false;
				});
			}
		});
	});
	$(".confirmManager").click(function() {
		var element = $(this);
		var action = element.attr("id");
		confirm("Tất cả các dữ liệu liên quan đến sản phẩm sẽ được xóa và không thể phục hồi.\nBạn có muốn thực hiện không?", function() {
			if(this.data == true) document.getElementById("deleteProduct").submit();
		});
	});
	$(".alertManager").boxes('alert', 'Bạn không được phân quyền với chức năng này.');
</script>
