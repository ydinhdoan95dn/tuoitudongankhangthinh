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
			<a href="?<?=TTH_PATH?>=article_project_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=article_project_manager"><i class="fa fa-building"></i> Dự án BĐS</a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm thể loại dự án
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "article_project_menu_standalone.php");
if(empty($typeFunc)) $typeFunc = "no";

$parent_id = isset($_GET['parent_id']) ? $_GET['parent_id']+0 : 0;

$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên thể loại.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'article_project_menu';

		// Create directory if not exists
		if(!is_dir($dir_dest)) {
			mkdir($dir_dest, 0755, true);
		}

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
			$db->table = "article_project_menu";
			$db->condition = "slug = '".$slug."'";
			$db->select();
			if($db->RowCount > 0) {
				$slug = $slug. '-' .$stringObj->getSlug(getRandomString(10));
			}

			$id_query = 0;
			$db->table = "article_project_menu";

			// Lấy home_image_type từ form
			$home_image_type = isset($_POST['home_image_type']) ? $_POST['home_image_type'] : 'home_width';
			if(!in_array($home_image_type, array('home_width', 'home_height'))) {
				$home_image_type = 'home_width';
			}

			// Lấy location từ form
			$location = isset($_POST['location']) ? trim($_POST['location']) : '';

			// Lấy parent từ form (có thể khác với parent_id từ URL nếu user chọn khác trong dropdown)
			$parent_value = isset($parent) ? $parent+0 : $parent_id;

			// Tính sort dựa trên parent thực sự từ form
			$db->table = "article_project_menu";
			$db->condition = "parent = ".$parent_value;
			$db->limit = "";
			$db->select();
			$sortOrder = $db->RowCount + 1;

			$data = array(
				'category_id'=>2, // Default category for projects
				'name'=>$db->clearText($name),
				'slug'=>$db->clearText($slug),
				'title'=>$db->clearText($title),
				'description'=>$db->clearText($description),
				'keywords'=>$db->clearText($keywords),
				'parent'=>$parent_value,
				'sort'=>$sortOrder,
				'comment'=>$db->clearText($comment),
				'icon'=>$db->clearText($font_icon),
				'is_active'=>$is_active+0,
				'hot'=>$hot+0,
				'home_image_type'=>$db->clearText($home_image_type),
				'location'=>$db->clearText($location),
				'created_time'=>time(),
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->insert($data);
			$id_query = $db->LastInsertID;

			if($handleUploadImg) {
				$stringObj = new StringHelper();

				$img_name_file = getRandomString().'-'.$id_query.'-'.substr($stringObj->getSlug($name),0,120);

				$imgUp->file_new_name_body = $img_name_file;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_y = 256;
				$imgUp->image_x = 490;
				$imgUp->Process($dir_dest);
				if($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "article_project_menu";
					$db->condition = "article_project_menu_id = ".$id_query;
					$db->update(array('img'=>$db->clearText($name_img)));
				}
				else {
					loadPageAdmin("Lỗi tải hình: ".$imgUp->error,"?".TTH_PATH."=article_project_manager");
				}

				$imgUp->file_new_name_body = 'icon-' . $img_name_file;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_y = 64;
				$imgUp->image_x = 82;
				$imgUp->Process($dir_dest);

				// Tạo thêm hình cho trang chủ
				// home_width: 800x500px (ngang)
				$imgUp->file_new_name_body = 'home_width_' . $img_name_file;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_x = 800;
				$imgUp->image_y = 500;
				$imgUp->Process($dir_dest);

				// home_height: 800x1120px (dọc)
				$imgUp->file_new_name_body = 'home_height_' . $img_name_file;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_x = 800;
				$imgUp->image_y = 1120;
				$imgUp->Process($dir_dest);

				$imgUp->Clean();
			}

			loadPageSucces("Đã thêm thể loại dự án thành công.","?".TTH_PATH."=article_project_manager");
			$OK = true;
		}
	}
}
else {
	$name = "";
	$slug = "";
	$title = "";
	$description = "";
	$keywords = "";
	$parent = $parent_id;
	$comment = "";
	$font_icon = "";
	$is_active = 1;
	$hot = 0;
	$img = "";
	$home_image_type = "home_width";
	$location = "";
}
if(!$OK) articleProjectMenuStandalone("?".TTH_PATH."=article_project_menu_add", "add", 0, $name, $slug, $title, $description, $keywords, $parent, $comment, $font_icon, $is_active, $hot, $img, $error, $home_image_type, $location);
?>
