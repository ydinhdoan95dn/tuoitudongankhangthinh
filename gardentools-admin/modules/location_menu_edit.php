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
			<a href="?<?=TTH_PATH?>=location_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=location_manager"><i class="fa fa-map-marker"></i> Vị trí địa lý</a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa mục
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "location_menu.php");
if(empty($typeFunc)) $typeFunc = "no";
$location_menu_id =  isset($_GET['id']) ? $_GET['id']+0 : $location_menu_id+0;
$db->table = "location_menu";
$db->condition = "location_menu_id = ".$location_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=location_manager");

$OK = false;
$error = '';
if($typeFunc=='edit'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên mục.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'location_menu';
		$file_size = $_FILES['img']['size'];

		if($file_size>0) {
			$imgUp = new Upload($_FILES['img']);

			$imgUp->file_max_size = $file_max_size;
			if ($imgUp->uploaded) {
				$handleUploadImg = true;
				$OK = true;
			}
			else {
				$error = '<span class="show-error">Lỗi tải hình: '.$imgUp->error.'</span>';
			}
		}
		else {
			$handleUploadImg = false;
			$OK = true;
		}

		if($OK) {
			$stringObj = new StringHelper();

			$id_query = 0;
			$db->table = "location_menu";
			$data = array(
				'name'=>$db->clearText($name),
				'is_active'=>$is_active+0,
				'hot'=>$hot+0,
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->condition = "location_menu_id = ".$location_menu_id;
			$db->update($data);
			$id_query = $location_menu_id;

			if($handleUploadImg) {
				$stringObj = new StringHelper();

				if(glob($dir_dest . DS .'*'.$img)) array_map("unlink", glob($dir_dest . DS .'*'.$img));

				$img_name_file = getRandomString().'-'.$id_query.'-'.substr($stringObj->getSlug($name),0,120);

				$imgUp->file_new_name_body    = $img_name_file;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_x         = true;
				$imgUp->image_y               = 65;

				$imgUp->Process($dir_dest);
				if($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "location_menu";
					$data = array(
						'img'=>$db->clearText($name_img)
					);
					$db->condition = "location_menu_id = ".$id_query;
					$db->update($data);
				}
				else {
					loadPageAdmin("Lỗi tải hình: ".$imgUp->error,"?".TTH_PATH."=location_manager");
				}

				$imgUp-> Clean();
			}

			loadPageSucces("Đã chỉnh sửa Mục thành công.","?".TTH_PATH."=location_manager");
			$OK = true;
		}
	}
}
else {
	$db->table = "location_menu";
	$db->condition = "location_menu_id = ".$location_menu_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$category_id    = $row['category_id']+0;
		$name			= $row['name'];
		$parent			= $row['parent'];
		$is_active		= $row['is_active']+0;
		$hot			= $row['hot']+0;
		$img            = $row['img'];
	}
}
if(!$OK) locationMenu("?".TTH_PATH."=location_menu_edit", "edit", $location_menu_id, $category_id, $name, $parent, $is_active, $hot, $img, $error);
?>
