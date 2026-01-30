<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
?>

<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<i class="fa fa-edit"></i> Quản lý nội dung
		</li>
		<li>
			<i class="fa fa-shopping-cart"></i> Sản phẩm BĐS
		</li>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
$del = isset($_GET['del']) ? $_GET['del']+0 : 0;
if($del != 0) {
	$dir_dest = ROOT_DIR . DS . 'uploads';

	// Lấy id menu cha.
	$parent = 0;
	$db->table = "article_product_menu";
	$db->condition = "article_product_menu_id = ".$del;
	$db->order = "";
	$rows = $db->select();
	foreach($rows as $row) {
		$parent = $row['parent']+0;
	}

	// Cập nhật menu con.
	$db->table = "article_product_menu";
	$data = array(
		'parent'=>$parent,
		'modified_time'=>time(),
		'user_id'=>$_SESSION["user_id"]
	);
	$db->condition = "parent = ".$del;
	$db->update($data);

	// Xóa ảnh bài viết liên quan.
	$db->table = "article_product";
	$db->condition = "article_product_menu_id = ".$del;
	$db->order = "";
	$rows = $db->select();
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

		// Xóa upload_tmp
		$list_img = "";
		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->order = "";
		$rows_it = $db->select();
		foreach ($rows_it as $row_it){
			$list_img = $row_it['list_img'];
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

		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->delete();

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
	}

	// Xóa csdl bài viết liên quan.
	$db->table = "article_product";
	$db->condition = "article_product_menu_id = ".$del;
	$db->delete();

	// Xóa ảnh menu.
	$db->table = "article_product_menu";
	$db->condition = "article_product_menu_id = ".$del;
	$rows = $db->select();
	foreach($rows as $row) {
		if(!empty($row['img']) && glob($dir_dest . DS .'article_product_menu'. DS . '*'.$row['img'])) {
			array_map("unlink", glob($dir_dest . DS .'article_product_menu'. DS . '*'.$row['img']));
		}
	}

	// Xóa csld menu.
	$db->table = "article_product_menu";
	$db->condition = "article_product_menu_id = ".$del;
	$db->delete();

	loadPageSucces("Đã xóa Chuyên mục sản phẩm thành công.", "?".TTH_PATH."=article_product_manager");
}
?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="panel-heading">
				<i class="fa fa-shopping-cart"></i> Quản lý Sản phẩm BĐS
				<a class="btn btn-success btn-sm pull-right" href="?<?=TTH_PATH?>=article_product_menu_add" style="margin-top: -5px;">
					<i class="fa fa-plus"></i> Thêm thể loại sản phẩm
				</a>
			</div>
			<div class="table-responsive">
				<table class="table table-manager table-hover">
					<thead>
					<tr>
						<th colspan="2">Thể loại sản phẩm</th>
						<th>Sắp xếp</th>
						<th>Trạng thái</th>
						<th>Nổi bật</th>
						<th>Hình ảnh</th>
						<th>Chức năng</th>
						<th>Nội dung</th>
					</tr>
					</thead>
					<tbody>
					<?php
					// Load menu sản phẩm (không cần kiểm tra category_id vì bảng riêng)
					loadProductMenuCategory($db, 0, 0);
					?>
					</tbody>
				</table>
			</div>
			<!-- /.table-responsive -->
		</div>
		<!-- /.panel -->
	</div>
	<!-- /.col-lg-6 -->
</div>

<?php
/**
 * Load menu sản phẩm từ bảng dxmt_article_product_menu
 * @param $db
 * @param $level
 * @param $parent
 */
function loadProductMenuCategory($db, $level, $parent){
	global $corePrivilegeSlug;

	$db->table = "article_product_menu";
	$db->condition = "parent = ".$parent;
	$db->order = "sort ASC";
	$db->limit = "";
	$rows = $db->select();
	$countList = $db->RowCount;

	foreach($rows as $row) {
		// Đếm số bài viết trong thể loại này
		$menuId = $row['article_product_menu_id'] + 0;
		$db->table = "article_product";
		$db->condition = "article_product_menu_id = ".$menuId;
		$db->order = "";
		$db->limit = "";
		$db->select();
		$articleCount = $db->RowCount;

		// Lấy category_id để kiểm tra quyền (mặc định 3 cho product)
		$category_id = isset($row['category_id']) ? $row['category_id'] : 3;
		?>
		<tr>
			<td>&nbsp;</td>
			<td style="padding: 0 0 0 <?=$level?>px;">
				<img src="images/node.png" /> <?=stripslashes($row['name'])?>
				<?php if($articleCount > 0): ?>
				<span class="label label-info" style="margin-left: 8px; font-size: 11px; padding: 3px 8px; border-radius: 10px;"><?=$articleCount?></span>
				<?php else: ?>
				<span class="label label-default" style="margin-left: 8px; font-size: 11px; padding: 3px 8px; border-radius: 10px; opacity: 0.6;">0</span>
				<?php endif; ?>
			</td>
			<?php
			// Kiểm tra phân quyền - dùng article_product_menu_edit hoặc fallback article_menu_edit
			// Role 1 (Admin) và 17 (CVKD) được bỏ qua check quyền cho trạng thái/nổi bật
			$canEdit = isAdminOrCVKD() ||
			           in_array("article_product_menu_edit;".$category_id, $corePrivilegeSlug) ||
			           in_array("article_menu_edit;".$category_id, $corePrivilegeSlug);
			$canDel = isAdminOrCVKD() ||
			          in_array("article_product_menu_del;".$category_id, $corePrivilegeSlug) ||
			          in_array("article_menu_del;".$category_id, $corePrivilegeSlug);

			if($parent==0) $width = '80%';
			else $width = '70%';

			if($canEdit) {
			?>
			<td align="right">
				<?=showSort("sort_".$row["article_product_menu_id"]."", $countList, $row["sort"], $width, 0, $row["article_product_menu_id"], 'article_product_menu', 1);?>
			</td>
			<td align="center">
				<?=($row["is_active"]+0==0)?
					'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["article_product_menu_id"].', \'is_active\', \'article_product_menu\');" rel="1"></div>'
					:
					'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["article_product_menu_id"].', \'is_active\', \'article_product_menu\');" rel="0"></div>'
				?>
			</td>
			<td align="center">
				<?=($row["hot"]+0==0)?
					'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["article_product_menu_id"].', \'hot\', \'article_product_menu\');" rel="1"></div>'
					:
					'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["article_product_menu_id"].', \'hot\', \'article_product_menu\');" rel="0"></div>'
				?>
			</td>
			<?php
			} else {
			?>
			<td align="right">
				<?=showSort("sort_".$row["article_product_menu_id"]."", $countList, $row["sort"], $width, 0, $row["article_product_menu_id"], 'article_product_menu', 0);?>
			</td>
			<td align="center">
				<?=($row["is_active"]+0==0)?
					'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở"></div>'
					:
					'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
				?>
			</td>
			<td align="center">
				<?=($row["hot"]+0==0)?
					'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở"></div>'
					:
					'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
				?>
			</td>
			<?php }
			?>
			<td align="center">
				<?=($row["img"]=='no' || empty($row["img"]))?
					'<img data-toggle="tooltip" data-placement="top" title="Không có hình" src="images/error.png">'
					:
					'<img id="popover-pm-'.$row["article_product_menu_id"].'" class="btn-popover" title="'.stripslashes($row["name"]).'" src="images/OK.png">
					<script>
							var image = \'<img src="../uploads/article_menu/'.$row["img"].'">\';
							$(\'#popover-pm-'.$row["article_product_menu_id"].'\').popover({placement: \'bottom\', content: image, html: true});
					</script>'
				?>
			</td>
			<td align="center">
				<?php if ($level < 30){ ?>
				<a href="?<?=TTH_PATH?>=article_product_menu_add&parent_id=<?=$row["article_product_menu_id"]?>"><img data-toggle="tooltip" data-placement="left" title="Thêm thể loại con" src="images/add.png"></a>
				&nbsp;
				<?php } else { ?>
				<span style="width: 16px; height: 1px; display: inline-block;""></span>
				&nbsp;
				<?php } ?>
				<a href="?<?=TTH_PATH?>=article_product_menu_edit&id=<?=$row["article_product_menu_id"]?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a>
				&nbsp;
				<?php if(!$canDel) { ?>
				<a class="alertManager" style="cursor: pointer;"><img data-toggle="tooltip" data-placement="right" title="Xóa" src="images/remove.png"></a>
				<?php } else { ?>
				<a class="confirmManager" style="cursor: pointer;" id="?<?=TTH_PATH?>=article_product_manager&del=<?=$row["article_product_menu_id"]?>"><img data-toggle="tooltip" data-placement="right" title="Xóa" src="images/remove.png"></a>
				<?php } ?>
			</td>
			<td align="center">
				<a href="?<?=TTH_PATH?>=article_product_list&id=<?=$row['article_product_menu_id']?>"><img data-toggle="tooltip" data-placement="top" title="Danh sách sản phẩm" src="images/list.png"></a>
			</td>
			<?php
			if ($level < 30){
				loadProductMenuCategory($db, $level+30, $row["article_product_menu_id"]+0);
			}
			?>
		</tr>
	<?php
	}
}
?>
<script>
	$(".confirmManager").click(function() {
		var element = $(this);
		var action = element.attr("id");
		confirm("Tất cả các Dữ liệu, Hình ảnh liên quan sẽ được xóa và không thể phục hồi.\nMục con của mục này sẽ được đẩy lên một bậc.\nBạn có muốn thực hiện không?", function() {
			if(this.data == true) window.location.href = action;
		});
	});
	$(".alertManager").boxes('alert', 'Bạn không được phân quyền với chức năng này.');
</script>
