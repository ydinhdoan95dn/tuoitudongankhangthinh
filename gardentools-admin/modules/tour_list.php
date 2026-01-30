<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//

if(isset($_POST['idDel'])){
	$dir_dest = ROOT_DIR . DS .'uploads';

	$upload_id = array();
	$idDel = implode(',',$_POST['idDel']);
	$db->table = "tour";
	$db->condition = "tour_id IN (".$idDel.")";
	$db->order = "";
	$rows = $db->select();
	$i = 0;
	foreach($rows as $row) {
		$mask = $dir_dest . DS . "tour" . DS . '*'.$row['img'];
		if(!empty($row['img']) && glob($mask)) {
			array_map("unlink", glob($mask));
		}

		$upload_id[$i] = $row['upload_id'];

		$list_img = "";
		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->order = "";
		$rows = $db->select();
		foreach ($rows as $row){
			$list_img = $row['list_img'];
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
		$i++;
	}

	$upload_id = implode(',',$upload_id);
	$db->table = "uploads_tmp";
	$db->condition = "upload_id IN (".$upload_id.")";
	$db->delete();

	$db->table = "tour";
	$db->condition = "tour_id IN (".$idDel.")";
	$db->delete();
	loadPageSucces("Đã xóa dữ liệu thành công.","?".TTH_PATH."=tour_list&id=".$_POST['list_id']);
}

$tour_menu_id =  isset($_GET['id']) ? $_GET['id']+0 : 0;
$category_id_core = 0;
$db->table = "tour_menu";
$db->condition = "tour_menu_id = ".$tour_menu_id;
$rows = $db->select();
foreach($rows as $row){
	$category_id_core = $row['category_id'];
}
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=tour_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=tour_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=tour_manager"><i class="fa fa-globe"></i> Tour du lịch</a>
		</li>
		<li>
			<i class="fa fa-list"></i> <?=getNameMenu($tour_menu_id, 'tour')?>
		</li>
		<a class="btn-add-new" href="?<?=TTH_PATH?>=tour_add&id=<?=$tour_menu_id?>">Thêm tour</a>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdminTwo("tour_list;".$category_id_core)?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=tour_list" method="post" enctype="multipart/form-data" id="deleteArt">
					<input type="hidden" name="list_id" value="<?=$tour_menu_id?>" />
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th>STT</th>
							<th>Hình ảnh</th>
							<th>Tiêu đề</th>
							<th>Trạng thái</th>
							<th>Nổi bật</th>
							<th>Ghim</th>
							<th>Lượt xem</th>
							<th>Ngày đăng</th>
							<th>Người đăng</th>
							<th>Chức năng</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$date = new DateClass();

						$db->table = "tour";
						$db->condition = "tour_menu_id = ".$tour_menu_id;
						$db->order = "created_time DESC";
						$rows = $db->select();

						$totalpages = 0;
						$perpage = 40;
						$total = $db->RowCount;
						if($total%$perpage==0) $totalpages=$total/$perpage;
						else $totalpages = floor($total/$perpage)+1;
						if(isset($_GET["page"])) $page=$_GET["page"]+0;
						else $page=1;
						$start=($page-1)*$perpage;
						$i=0+($page-1)*$perpage;

						$db->table = "tour";
						$db->condition = "tour_menu_id = ".$tour_menu_id;
						$db->order = "created_time DESC";
						$db->limit = $start.','.$perpage;
						$rows = $db->select();

						foreach($rows as $row) {
							$i++;
							?>
							<tr>
								<td align="center"><?=$i?></td>
								<td align="center">
									<?=($row["img"]=='no')?
										'<img data-toggle="tooltip" data-placement="top" title="Không có hình" src="images/error.png">'
										:
										'<img id="popover-'.$i.'" class="btn-popover" title="'.stripslashes($row["name"]).'" src="images/OK.png">
										<script>
											var image = \'<img src="../uploads/tour/'.$row["img"].'">\';
											$(\'#popover-'.$i.'\').popover({placement: \'bottom\', content: image, html: true});
										</script>'
									?>
								</td>
								<td><?=stripslashes($row['name'])?><?=($row['tour_keys']=='') ? ''  : '&nbsp; (Mã: <span style="font-weight: bold; color: #ff0000;">'.stripslashes($row['tour_keys']) .'</span>)' ?></td>
								<?php
								//-----------Kiểm tra phân quyền ---------------------------------------
								if(in_array("tour_edit;".$category_id_core,$corePrivilegeSlug)) {
									?>
									<td align="center">
										<?=($row["is_active"]+0==0)?
												'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["tour_id"].', \'is_active\', \'tour\');" rel="1">0</div>'
												:
												'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["tour_id"].', \'is_active\', \'tour\');" rel="0">1</div>'
										?>
									</td>
									<td align="center">
										<?=($row["hot"]+0==0)?
												'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["tour_id"].', \'hot\', \'tour\');" rel="1">0</div>'
												:
												'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["tour_id"].', \'hot\', \'tour\');" rel="0">1</div>'
										?>
									</td>
									<td align="center">
										<?=($row["pin"]+0==0)?
												'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["tour_id"].', \'pin\', \'tour\');" rel="1">0</div>'
												:
												'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["tour_id"].', \'pin\', \'tour\');" rel="0">1</div>'
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
										<?=($row["pin"]+0==0)?
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
								//----------- end if ------------
								?>
								<td align="center"><?=formatNumberVN($row['views']+0);?></td>
								<td align="center"><?=$date->vnDateTime($row['created_time'])?></td>
								<td align="center"><?=getUserName($row['user_id']);?></td>
								<td class="details-control" align="center">
									<div class="checkbox">
										<a href="?<?=TTH_PATH?>=tour_edit&id=<?=$row['tour_id']?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a>&nbsp;&nbsp;
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkboxArt" name="idDel[]" value="<?=$row['tour_id']?>">
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
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages,'?'.TTH_PATH.'=tour_list&id='.$tour_menu_id.'&page=')?></div>
						<div class="col-sm-6" align="right" style="padding: 7px 0;">
							<label class="radio-inline"><input type="checkbox" id="selecctall"  data-toggle="tooltip" data-placement="top" title="Chọn xóa tất cả" ></label>
							<input type="button" class="btn btn-primary btn-xs <?=in_array("tour_del;".$category_id_core,$corePrivilegeSlug)? "confirmManager" : "alertManager"?> " value="Xóa" name="deleteArt">
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
	$(document).ready(function() {
		$('#dataTablesList').dataTable( {
			"language": {
				"url": "<?=ADMIN_DIR?>/js/plugins/dataTables/de_DE.txt"
			},
			"aoColumnDefs" : [ {
				"bSortable" : false,
				"aTargets" : [ 1,3,4,8, "no-sort" ]
			} ],
			"paging":   false,
			"info":     false,
			"order": [ 0, "asc" ]
		} );

		$('#selecctall').click(function(event) {
			if(this.checked) {
				$('.checkboxArt').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxArt').each(function() {
					this.checked = false;
				});
			}
		});
	});
</script>
<script>
	$(".confirmManager").click(function() {
		var element = $(this);
		var action = element.attr("id");
		confirm("Tất cả các dữ liệu liên quan đến bài viết sẽ được xóa và không thể phục hồi.\nBạn có muốn thực hiện không?", function() {
			if(this.data == true) document.getElementById("deleteArt").submit();
		});
	});
	$(".alertManager").boxes('alert', 'Bạn không được phân quyền với chức năng này.');
</script>