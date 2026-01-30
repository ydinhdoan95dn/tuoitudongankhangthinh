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
			<a href="?<?=TTH_PATH?>=document_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=document_manager"><i class="fa fa-folder-open-o"></i> Văn bản, Tài liệu</a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa chuyên mục
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "document_menu.php");
if(empty($typeFunc)) $typeFunc = "no";
$document_menu_id =  isset($_GET['id']) ? $_GET['id']+0 : $document_menu_id+0;
$db->table = "document_menu";
$db->condition = "document_menu_id = ".$document_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Chuyên mục không tồn tại.","?".TTH_PATH."=document_manager");

$OK = false;
$error = '';
if($typeFunc=='edit'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên chuyên mục.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'document_menu';
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

			$slug = (empty($slug)) ? $name : $slug;
			$slug = $stringObj->getSlug($slug);
			$db->table = "document_menu";
			$db->condition = "slug = '".$slug."'";
			$rows = $db->select();
			$id = 0;
			foreach ($rows as $row) {
				$id = $row['document_menu_id'];
			}
			if($db->RowCount > 0 && $id != $document_menu_id) {
				$slug = $slug. '-' .$stringObj->getSlug(getRandomString(10));
			}

			$id_query = 0;
			$db->table = "document_menu";
			$data = array(
				'name'=>$db->clearText($name),
				'slug'=>$db->clearText($slug),
				'title'=>$db->clearText($title),
				'description'=>$db->clearText($description),
				'keywords'=>$db->clearText($keywords),
				'comment'=>$db->clearText($comment),
				'is_active'=>$is_active+0,
				'hot'=>$hot+0,
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->condition = "document_menu_id = ".$document_menu_id;
			$db->update($data);
			$id_query = $document_menu_id;

			if($handleUploadImg) {
				$stringObj = new StringHelper();

				if(glob($dir_dest.'*'.$img)) array_map("unlink", glob($dir_dest . DS .'*'.$img));

				$imgUp->file_new_name_body    = getRandomString().'-'.$id_query.'-'.$stringObj->getSlug($name);
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_y               = 360;
				$imgUp->image_x               = 480;

				$imgUp->Process($dir_dest);
				if($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "document_menu";
					$data = array(
						'img'=>$db->clearText($name_img)
					);
					$db->condition = "document_menu_id = ".$id_query;
					$db->update($data);
				}
                else {
                    loadPageAdmin("Lỗi tải hình: ".$imgUp->error,"?".TTH_PATH."=document_manager");
                }

				$imgUp-> Clean();
			}

			loadPageSucces("Đã chỉnh sửa Chuyên mục thành công.","?".TTH_PATH."=document_manager");
			$OK = true;
		}
	}
}
else {
	$db->table = "document_menu";
	$db->condition = "document_menu_id = ".$document_menu_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$category_id    = $row['category_id']+0;
		$name			= $row['name'];
		$slug           = $row['slug'];
		$title			= $row['title'];
		$description	= $row['description'];
		$keywords		= $row['keywords'];
		$parent			= $row['parent'];
		$comment		= $row['comment'];
		$is_active		= $row['is_active']+0;
		$hot			= $row['hot']+0;
		$img            = $row['img'];
	}
}
if(!$OK) documentMenu("?".TTH_PATH."=document_menu_edit", "edit", $document_menu_id, $category_id, $name, $slug, $title, $description, $keywords, $parent, $comment, $is_active, $hot, $img, $error);
?>
