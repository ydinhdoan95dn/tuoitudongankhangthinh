<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$gallery_menu_id = isset($_GET['id']) ? $_GET['id']+0 : $gallery_menu_id+0;
$db->table = "gallery_menu";
$db->condition = "gallery_menu_id = ".$gallery_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=gallery_manager");
$category_id = 0;
foreach($rows as $row) {
	$category_id =	$row["category_id"]+0;
}
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=gallery_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=gallery_manager"><i class="fa fa-image"></i> Hình ảnh</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=gallery_list&id=<?=$gallery_menu_id?>"><i class="fa fa-list"></i> <?=getNameMenuGal($gallery_menu_id)?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm hình ảnh
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "gallery.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();

$file_max_size = FILE_MAX_SIZE;
$dir_dest = ROOT_DIR . DS .'uploads' . DS . "gallery";
$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên hình.</span>';
	else {
		$handleUploadImg = false;
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
			$id_query = 0;
			$db->table = "gallery";
			$data = array(
				'gallery_menu_id'=>$gallery_menu_id+0,
				'name'=>$db->clearText($name),
				'upload_id'=>$upload_img_id+0,
				'comment'=>$db->clearText($comment),
				'content'=>$db->clearText($content),
				'link'=>$db->clearText($link),
				'is_active'=>$is_active+0,
				'hot'=>$hot+0,
				'created_time'=>strtotime($date->dmYtoYmd($created_time)),
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->insert($data);
			$id_query = $db->LastInsertID;

			if($handleUploadImg) {
				$stringObj = new StringHelper();

				$name_image = getRandomString().'-'.$id_query.'-'.substr($stringObj->getSlug($name),0,120);

				$imgUp->file_new_name_body      = 'full_' . $name_image;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body    = $name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_y               = 286;
				$imgUp->image_x               = 360;
				$imgUp->Process($dir_dest);

				if($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "gallery";
					$data = array(
						'img'=>$db->clearText($name_img)
					);
					$db->condition = "gallery_id = ".$id_query;
					$db->update($data);
				}
                else {
                    loadPageAdmin("Lỗi tải hình: ".$imgUp->error,"?".TTH_PATH."=gallery_list&id=".$gallery_menu_id);
                }

				$imgUp->file_new_name_body    = 'slider_'.$name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_x               = 1140;
				$imgUp->image_y               = 536;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body    = 'banner_'.$name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_x               = 1133;
				$imgUp->image_y               = 252;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body    = 'adv_'.$name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_x               = 555;
				$imgUp->image_y               = 170;
				$imgUp->Process($dir_dest);

				// Home-5 Revolution Slider (Full HD)
				$imgUp->file_new_name_body    = 'home5_'.$name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_x               = 1920;
				$imgUp->image_y               = 1080;
				$imgUp->Process($dir_dest);

				// Kiểm tra nếu gallery thuộc projects_home (gallery_menu_id = 6 hoặc parent = 6)
				$isProjectsHome = false;
				$db->table = "gallery_menu";
				$db->condition = "gallery_menu_id = ".$gallery_menu_id;
				$db->order = "";
				$db->limit = "1";
				$menuRows = $db->select();
				if($db->RowCount > 0) {
					$menuParent = $menuRows[0]['parent'] + 0;
					// gallery_menu_id = 6 là projects_home, hoặc parent = 6
					if($gallery_menu_id == 6 || $menuParent == 6) {
						$isProjectsHome = true;
					}
				}

				// Nếu là projects_home, tạo thêm hình cho trang chủ
				if($isProjectsHome) {
					// home_width: 800x500px (ngang)
					$imgUp->file_new_name_body = 'home_width_' . $name_image;
					$imgUp->image_resize = true;
					$imgUp->image_ratio_crop = true;
					$imgUp->image_x = 800;
					$imgUp->image_y = 500;
					$imgUp->Process($dir_dest);

					// home_height: 800x1120px (dọc)
					$imgUp->file_new_name_body = 'home_height_' . $name_image;
					$imgUp->image_resize = true;
					$imgUp->image_ratio_crop = true;
					$imgUp->image_x = 800;
					$imgUp->image_y = 1120;
					$imgUp->Process($dir_dest);
				}

				$imgUp-> Clean();
			}

			// Xử lý upload ảnh Mobile (chỉ cho Slide - gallery_menu_id = 1)
			$has_mobile_img = isset($_POST['has_mobile_img']) ? $_POST['has_mobile_img']+0 : 0;
			if($gallery_menu_id == 1 && $has_mobile_img == 1 && isset($_FILES['img_mobile_file']) && $_FILES['img_mobile_file']['size'] > 0) {
				$imgMobileUp = new Upload($_FILES['img_mobile_file']);
				$imgMobileUp->file_max_size = $file_max_size;

				if($imgMobileUp->uploaded) {
					$stringObj = new StringHelper();
					$name_image_mobile = 'mobi_' . getRandomString() . '-' . $id_query . '-' . substr($stringObj->getSlug($name), 0, 100);

					// Lưu ảnh full cho mobile (giữ nguyên tỷ lệ 9:16)
					$imgMobileUp->file_new_name_body = $name_image_mobile;
					$imgMobileUp->image_resize = true;
					$imgMobileUp->image_x = 1080;  // Width cho mobile
					$imgMobileUp->image_y = 1920;  // Height 9:16
					$imgMobileUp->image_ratio_crop = true;
					$imgMobileUp->Process($dir_dest);

					if($imgMobileUp->processed) {
						$name_img_mobile = $imgMobileUp->file_dst_name;
						$db->table = "gallery";
						$data = array(
							'img_mobile' => $db->clearText($name_img_mobile)
						);
						$db->condition = "gallery_id = ".$id_query;
						$db->update($data);
					}

					$imgMobileUp->Clean();
				}
			}

			$db->table = "uploads_tmp";
			$data = array(
				'status'=>1
			);
			$db->condition = "upload_id = ".($upload_img_id+0);
			$db->update($data);
			$_SESSION['upload_id'] = 0;

			loadPageSucces("Đã thêm Hình ảnh thành công.","?".TTH_PATH."=gallery_list&id=".$gallery_menu_id);
			$OK = true;
		}
	}
}
else {
	$upload_img_id  = 0;
	if($upload_img_id==0) {
		$db->table = "uploads_tmp";
		$data = array(
				'created_time'=>time()
		);
		$db->insert($data);
		$upload_img_id = $db->LastInsertID;
	}
	$name			= "";
	$comment        = "";
	$content        = "";
	$link           = "";
	$is_active		= 1;
	$hot			= 0;
	$created_time   = $date->vnDateTime(time());
}
if(!$OK) gallery("?".TTH_PATH."=gallery_add", "add", 0, $gallery_menu_id, $name, "", $comment, $content, $link, $is_active, $hot, $created_time, $upload_img_id, $error, "", 0);
?>