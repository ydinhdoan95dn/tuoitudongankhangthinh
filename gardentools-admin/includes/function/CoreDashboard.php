<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Chung
 * @param $role_id
 */
function showCoreCategory ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'category'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallCategory" value="ok" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
		<?php
		$db->table = "category_type";
		$db->condition = "is_active = 1";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxCategory" name="variable[]" <?=(in_array($row['slug'],$privilege)) ? "checked" : "" ?> value="<?=$row['slug']?>" ><?=stripslashes($row['name'])?>
				</label>
			</div>
		<?php
		}
		?>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxCategory" name="variable[]" <?=(in_array("plugin_page",$privilege)) ? "checked" : "" ?> value="plugin_page" >Phần bổ sung
				</label>
			</div>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallCategory').click(function(event) {
			if(this.checked) {
				$('.checkboxCategory').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxCategory').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Bài viết
 * @param $role_id
 */
function showCoreArticle ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'article'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 1";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallArt<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_list;<?=$row['category_id']?>">Danh sách bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_add;<?=$row['category_id']?>">Thêm bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_edit;<?=$row['category_id']?>">Chỉnh sửa bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxArt<?=$row['slug']?>" name="variable[]" <?=(in_array("article_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_del;<?=$row['category_id']?>">Xóa bài viết
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallArt<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxArt<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxArt<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Hình ảnh
 * @param $role_id
 */
function showCoreGallery ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'gallery'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 2";
		$db->order = "sort ASC";
		$rows = $db->select();
		$i = 0;
		$countList = 0;
		$countList = $db->RowCount;
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallGal<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_list;<?=$row['category_id']?>">Danh sách hình ảnh
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_add;<?=$row['category_id']?>">Thêm hình ảnh
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_edit;<?=$row['category_id']?>">Chỉnh sửa hình ảnh
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGal<?=$row['slug']?>" name="variable[]" <?=(in_array("gallery_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gallery_del;<?=$row['category_id']?>">Xóa hình ảnh
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallGal<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxGal<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxGal<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Văn bản / Tài liệu
 * @param $role_id
 */
function showCoreDocument ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'document'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 21";
		$db->order = "sort ASC";
		$rows = $db->select();
		$i = 0;
		$countList = 0;
		$countList = $db->RowCount;
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallDoc<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_list;<?=$row['category_id']?>">Danh sách hình ảnh
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_add;<?=$row['category_id']?>">Thêm hình ảnh
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_edit;<?=$row['category_id']?>">Chỉnh sửa hình ảnh
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxDoc<?=$row['slug']?>" name="variable[]" <?=(in_array("document_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="document_del;<?=$row['category_id']?>">Xóa hình ảnh
					</label>
				</div>
				<script>
					$(document).ready(function() {
						$('#selecctallDoc<?=$row['slug']?>').click(function(event) {
							if(this.checked) {
								$('.checkboxDoc<?=$row['slug']?>').each(function() {
									this.checked = true;
								});
							}else{
								$('.checkboxDoc<?=$row['slug']?>').each(function() {
									this.checked = false;
								});
							}
						});
					});
				</script>
			</div>
			<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<?php
}
//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Dự án đầu tư
 * @param $role_id
 */
function showCoreProject ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'project'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 17";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallProject<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_list;<?=$row['category_id']?>">Danh sách bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_add;<?=$row['category_id']?>">Thêm bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_edit;<?=$row['category_id']?>">Chỉnh sửa bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxProject<?=$row['slug']?>" name="variable[]" <?=(in_array("project_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="project_del;<?=$row['category_id']?>">Xóa bài viết
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallProject<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxProject<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxProject<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Sản phẩm
 * @param $role_id
 */
function showCoreProduct ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'product'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 6";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallPro<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_list;<?=$row['category_id']?>">Danh sách tin rao
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_add;<?=$row['category_id']?>">Thêm tin rao mới
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_edit;<?=$row['category_id']?>">Chỉnh sửa tin rao
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("article_product_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="article_product_del;<?=$row['category_id']?>">Xóa tin rao
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("owner_real;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="owner_real;<?=$row['category_id']?>">Tin rao: BĐS sở hữu Thiên Nhân
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxPro<?=$row['slug']?>" name="variable[]" <?=(in_array("owner_cus;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="owner_cus;<?=$row['category_id']?>">Tin rao: BĐS khách hàng ký gửi
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallPro<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxPro<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						} else{
							$('.checkboxPro<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}


//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Danh gia
 * @param $role_id
 */
function showCoreComment ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'comment'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallcomment" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxcomment" name="variable[]" <?=(in_array("comment_add",$privilege)) ? "checked" : "" ?> value="comment_add">Thêm trang
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxcomment" name="variable[]" <?=(in_array("comment_edit",$privilege)) ? "checked" : "" ?> value="comment_edit">Chỉnh sửa trang
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxcomment" name="variable[]" <?=(in_array("comment_del",$privilege)) ? "checked" : "" ?> value="comment_del">Xóa trang
				</label>
			</div>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
		$(document).ready(function() {
			$('#selecctallcomment').click(function(event) {
				if(this.checked) {
					$('.checkboxcomment').each(function() {
						this.checked = true;
					});
				}else{
					$('.checkboxcomment').each(function() {
						this.checked = false;
					});
				}
			});
		});
	</script>
	<?php
}



//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > BĐS Kinh doanh
 * @param $role_id
 */
function showCoreBdsBusiness ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'bds_business'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 18";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallBds<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_list;<?=$row['category_id']?>">Danh sách tin rao
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_add;<?=$row['category_id']?>">Thêm tin rao mới
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_edit;<?=$row['category_id']?>">Chỉnh sửa tin rao
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxBds<?=$row['slug']?>" name="variable[]" <?=(in_array("bds_business_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="bds_business_del;<?=$row['category_id']?>">Xóa tin rao
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallBds<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxBds<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxBds<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Tour du lịch
 * @param $role_id
 */
function showCoreTour ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'tour'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 9";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallTour<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_list;<?=$row['category_id']?>">Danh sách bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_add;<?=$row['category_id']?>">Thêm bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_edit;<?=$row['category_id']?>">Chỉnh sửa bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxTour<?=$row['slug']?>" name="variable[]" <?=(in_array("tour_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="tour_del;<?=$row['category_id']?>">Xóa bài viết
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallTour<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxTour<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxTour<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Thuê xe
 * @param $role_id
 */
function showCoreCar ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'car'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 11";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallCar<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_list;<?=$row['category_id']?>">Danh sách bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_add;<?=$row['category_id']?>">Thêm bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_edit;<?=$row['category_id']?>">Chỉnh sửa bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxCar<?=$row['slug']?>" name="variable[]" <?=(in_array("car_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="car_del;<?=$row['category_id']?>">Xóa bài viết
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallCar<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxCar<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxCar<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Đồ lưu niệm
 * @param $role_id
 */
function showCoreGift ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'gift'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 10";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallGift<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_list;<?=$row['category_id']?>">Danh sách bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_add;<?=$row['category_id']?>">Thêm bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_edit;<?=$row['category_id']?>">Chỉnh sửa bài viết
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxGift<?=$row['slug']?>" name="variable[]" <?=(in_array("gift_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="gift_del;<?=$row['category_id']?>">Xóa bài viết
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallGift<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxGift<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxGift<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Vị trí địa lý
 * @param $role_id
 */
function showCoreLocation ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'location'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 12";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallLocation<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_list;<?=$row['category_id']?>">Danh sách nội dung
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_add;<?=$row['category_id']?>">Thêm nội dung
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_edit;<?=$row['category_id']?>">Chỉnh sửa nội dung
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxLocation<?=$row['slug']?>" name="variable[]" <?=(in_array("location_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="location_del;<?=$row['category_id']?>">Xóa nội dung
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallLocation<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxLocation<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxLocation<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Dữ liệu đường phố
 * @param $role_id
 */
function showCoreStreet ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'street'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallStreet" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxStreet" name="variable[]" <?=(in_array("street_add",$privilege)) ? "checked" : "" ?> value="street_add">Thêm mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxStreet" name="variable[]" <?=(in_array("street_edit",$privilege)) ? "checked" : "" ?> value="street_edit">Chỉnh sửa mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxStreet" name="variable[]" <?=(in_array("street_del",$privilege)) ? "checked" : "" ?> value="street_del">Xóa mục
				</label>
			</div>
			<script>
			$(document).ready(function() {
				$('#selecctallStreet').click(function(event) {
					if(this.checked) {
						$('.checkboxStreet').each(function() {
							this.checked = true;
						});
					}else{
						$('.checkboxStreet').each(function() {
							this.checked = false;
						});
					}
				});
			});
			</script>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}


//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Dữ liệu tên dự án
 * @param $role_id
 */
function showCorePrjname ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'prjname'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallPrjname" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxPrjname" name="variable[]" <?=(in_array("prjname_add",$privilege)) ? "checked" : "" ?> value="prjname_add">Thêm mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxPrjname" name="variable[]" <?=(in_array("prjname_edit",$privilege)) ? "checked" : "" ?> value="prjname_edit">Chỉnh sửa mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxPrjname" name="variable[]" <?=(in_array("prjname_del",$privilege)) ? "checked" : "" ?> value="prjname_del">Xóa mục
				</label>
			</div>
			<script>
			$(document).ready(function() {
				$('#selecctallPrjname').click(function(event) {
					if(this.checked) {
						$('.checkboxPrjname').each(function() {
							this.checked = true;
						});
					}else{
						$('.checkboxPrjname').each(function() {
							this.checked = false;
						});
					}
				});
			});
			</script>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Chiều rộng đường
 * @param $role_id
 */
function showCoreRoad ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'road'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallRoad" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxRoad" name="variable[]" <?=(in_array("road_add",$privilege)) ? "checked" : "" ?> value="road_add">Thêm mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxRoad" name="variable[]" <?=(in_array("road_edit",$privilege)) ? "checked" : "" ?> value="road_edit">Chỉnh sửa mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxRoad" name="variable[]" <?=(in_array("road_del",$privilege)) ? "checked" : "" ?> value="road_del">Xóa mục
				</label>
			</div>
			<script>
			$(document).ready(function() {
				$('#selecctallRoad').click(function(event) {
					if(this.checked) {
						$('.checkboxRoad').each(function() {
							this.checked = true;
						});
					}else{
						$('.checkboxRoad').each(function() {
							this.checked = false;
						});
					}
				});
			});
			</script>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Dữ liệu phương hướng
 * @param $role_id
 */
function showCoreDirection ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'direction'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallDirection" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxDirection" name="variable[]" <?=(in_array("direction_add",$privilege)) ? "checked" : "" ?> value="direction_add">Thêm mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxDirection" name="variable[]" <?=(in_array("direction_edit",$privilege)) ? "checked" : "" ?> value="direction_edit">Chỉnh sửa mục
				</label>
			</div>
			<div class="checkbox" style="padding-left: 10px;">
				<label>
					<input type="checkbox" class="checkboxDirection" name="variable[]" <?=(in_array("direction_del",$privilege)) ? "checked" : "" ?> value="direction_del">Xóa mục
				</label>
			</div>
			<script>
			$(document).ready(function() {
				$('#selecctallDirection').click(function(event) {
					if(this.checked) {
						$('.checkboxDirection').each(function() {
							this.checked = true;
						});
					}else{
						$('.checkboxDirection').each(function() {
							this.checked = false;
						});
					}
				});
			});
			</script>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Dữ liệu khác
 * @param $role_id
 */
function showCoreOthers ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'others'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<?php
		$db->table = "category";
		$db->condition = "type_id = 15";
		$db->order = "sort ASC";
		$rows = $db->select();
		foreach($rows as $row) {
			?>
			<div style="float: left; padding: 10px; border-left: 1px solid #ddd;">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="selecctallOthers<?=$row['slug']?>" ><b><?=stripslashes($row['name'])?></b>
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("category_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="category_edit;<?=$row['category_id']?>">Chỉnh sửa thể loại
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_menu_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_menu_add;<?=$row['category_id']?>">Thêm mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_menu_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_menu_edit;<?=$row['category_id']?>">Chỉnh sửa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 10px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_menu_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_menu_del;<?=$row['category_id']?>">Xóa mục
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_list;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_list;<?=$row['category_id']?>">Danh sách nội dung
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_add;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_add;<?=$row['category_id']?>">Thêm nội dung
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_edit;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_edit;<?=$row['category_id']?>">Chỉnh sửa nội dung
					</label>
				</div>
				<div class="checkbox" style="padding-left: 20px;">
					<label>
						<input type="checkbox" class="checkboxOthers<?=$row['slug']?>" name="variable[]" <?=(in_array("others_del;".$row['category_id'],$privilege)) ? "checked" : "" ?> value="others_del;<?=$row['category_id']?>">Xóa nội dung
					</label>
				</div>
				<script>
				$(document).ready(function() {
					$('#selecctallOthers<?=$row['slug']?>').click(function(event) {
						if(this.checked) {
							$('.checkboxOthers<?=$row['slug']?>').each(function() {
								this.checked = true;
							});
						}else{
							$('.checkboxOthers<?=$row['slug']?>').each(function() {
								this.checked = false;
							});
						}
					});
				});
				</script>
			</div>
		<?php
		}
		?>
		<div class="clearfix"></div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản lý nội dung > Phần bổ sung
 * @param $role_id
 */
function showCorePages ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'pages'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div style="padding: 10px; border-left: 1px solid #ddd;">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="selecctallPages" ><b>Chọn tất cả / Hủy tất cả</b>
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxPages" name="variable[]" <?=(in_array("plugin_page_add",$privilege)) ? "checked" : "" ?> value="plugin_page_add">Thêm trang
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxPages" name="variable[]" <?=(in_array("plugin_page_edit",$privilege)) ? "checked" : "" ?> value="plugin_page_edit">Chỉnh sửa trang
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" class="checkboxPages" name="variable[]" <?=(in_array("plugin_page_del",$privilege)) ? "checked" : "" ?> value="plugin_page_del">Xóa trang
				</label>
			</div>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallPages').click(function(event) {
			if(this.checked) {
				$('.checkboxPages').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxPages').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Cơ sở dữ liệu
 * @param $role_id
 */
function showCoreBackup ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'backup'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="selecctallTwo" ><b>Chọn tất cả / Hủy tất cả</b>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxBackup" name="variable[]" <?=(in_array("backup_data",$privilege)) ? "checked" : "" ?> value="backup_data">Sao lưu dữ liệu
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxBackup" name="variable[]" <?=(in_array("backup_config",$privilege)) ? "checked" : "" ?> value="backup_config">Cấu hình sao lưu
			</label>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallTwo').click(function(event) {
			if(this.checked) {
				$('.checkboxBackup').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxBackup').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Cấu hình
 * @param $role_id
 */
function showCoreConfig ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'config'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="selecctallThree" ><b>Chọn tất cả / Hủy tất cả</b>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_general",$privilege)) ? "checked" : "" ?> value="config_general">Cấu hình chung
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_smtp",$privilege)) ? "checked" : "" ?> value="config_smtp">Cấu hình SMTP
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_datetime",$privilege)) ? "checked" : "" ?> value="config_datetime">Cấu hình thời gian
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_plugins",$privilege)) ? "checked" : "" ?> value="config_plugins">Trình cắm bổ sung
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_socialnetwork",$privilege)) ? "checked" : "" ?> value="config_socialnetwork">Mạng xã hội
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_search",$privilege)) ? "checked" : "" ?> value="config_search">Máy chủ tìm kiếm
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("config_upload",$privilege)) ? "checked" : "" ?> value="config_upload">Cấu hình upload
			</label>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallThree').click(function(event) {
			if(this.checked) {
				$('.checkboxConfig').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxConfig').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Công cụ hỗ trợ
 * @param $role_id
 */
function showCoreTool ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'tool'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="selecctallFour" ><b>Chọn tất cả / Hủy tất cả</b>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxTool" name="variable[]" <?=(in_array("tool_delete",$privilege)) ? "checked" : "" ?> value="tool_delete">Dọn dẹp hệ thống
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxTool" name="variable[]" <?=(in_array("tool_site",$privilege)) ? "checked" : "" ?> value="tool_site">Chuẩn đoán site
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxTool" name="variable[]" <?=(in_array("tool_keywords",$privilege)) ? "checked" : "" ?> value="tool_keywords">Hạng site theo từ khóa
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxConfig" name="variable[]" <?=(in_array("tool_ipdie",$privilege)) ? "checked" : "" ?> value="tool_ipdie">Quản lý IP cấm
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxTool" name="variable[]" <?=(in_array("tool_update",$privilege)) ? "checked" : "" ?> value="tool_update">Kiểm tra phiên bản
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxTool" name="variable[]" <?=(in_array("tool_analytics",$privilege)) ? "checked" : "" ?> value="tool_analytics">Thống kê truy cập
			</label>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallFour').click(function(event) {
			if(this.checked) {
				$('.checkboxTool').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxTool').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Quản trị hệ thống
 * @param $role_id
 */
function showCoreCore ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'core'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>

	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="selecctallFive" ><b>Chọn tất cả / Hủy tất cả</b>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxCore" name="variable[]" <?=(in_array("core_role",$privilege)) ? "checked" : "" ?> value="core_role">Nhóm quản trị
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxCore" name="variable[]" <?=(in_array("core_user",$privilege)) ? "checked" : "" ?> value="core_user">Quản lý thành viên
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxCore" name="variable[]" <?=(in_array("core_dashboard",$privilege)) ? "checked" : "" ?> value="core_dashboard">Phân quyền quản trị
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxCore" name="variable[]" <?=(in_array("core_mail",$privilege)) ? "checked" : "" ?> value="core_mail">Gửi mail thành viên
			</label>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallFive').click(function(event) {
			if(this.checked) {
				$('.checkboxCore').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxCore').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Thông tin hệ thống
 * @param $role_id
 */
function showCoreInfo ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'info'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="selecctallSix" ><b>Chọn tất cả / Hủy tất cả</b>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxInfo" name="variable[]" <?=(in_array("sys_info_diary",$privilege)) ? "checked" : "" ?> value="sys_info_diary">Thống kê hoạt động
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxInfo" name="variable[]" <?=(in_array("sys_info_site",$privilege)) ? "checked" : "" ?> value="sys_info_site">Cấu hình site
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxInfo" name="variable[]" <?=(in_array("sys_info_php",$privilege)) ? "checked" : "" ?> value="sys_info_php">Cấu hình PHP
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxInfo" name="variable[]" <?=(in_array("sys_info_expansion",$privilege)) ? "checked" : "" ?> value="sys_info_expansion">Tiện ích mở rộng
			</label>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallSix').click(function(event) {
			if(this.checked) {
				$('.checkboxInfo').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxInfo').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}

//----------------------------------------------------------------------------------------------------------------------
/**
 * Cập nhật quyền quản trị: Landing Page
 * @param $role_id
 */
function showCoreLanding ($role_id) {
	global $db;

	$privilege = array();
	$db->table = "core_privilege";
	$db->condition = "role_id = ".$role_id. " and type = 'landing'";
	$db->order = "";
	$db->limit = "";
	$rows = $db->select();
	$stt = 0;
	foreach ($rows as $row) {
		$privilege[$stt] = $row['privilege_slug'];
		$stt++;
	}
	?>
	<input type="hidden" name="role_id" value="<?=$role_id?>" />
	<div class="form-group">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="selecctallLanding" ><b>Chọn tất cả / Hủy tất cả</b>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxLanding" name="variable[]" <?=(in_array("landing_manager",$privilege)) ? "checked" : "" ?> value="landing_manager">Xem danh sách Landing Page
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxLanding" name="variable[]" <?=(in_array("landing_add",$privilege)) ? "checked" : "" ?> value="landing_add">Thêm Landing Page mới
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxLanding" name="variable[]" <?=(in_array("landing_edit",$privilege)) ? "checked" : "" ?> value="landing_edit">Sửa Landing Page
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" class="checkboxLanding" name="variable[]" <?=(in_array("landing_delete",$privilege)) ? "checked" : "" ?> value="landing_delete">Xóa Landing Page
			</label>
		</div>
		<label><button type="submit" class="btn btn-form-primary btn-form">Xác nhận</button></label>
	</div>
	<script>
	$(document).ready(function() {
		$('#selecctallLanding').click(function(event) {
			if(this.checked) {
				$('.checkboxLanding').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxLanding').each(function() {
					this.checked = false;
				});
			}
		});
	});
	</script>
<?php
}