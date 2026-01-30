<?php
if (!defined('TTH_SYSTEM')) {
	die('Please stop!');
}

// Helper function to create slug from Vietnamese text
if (!function_exists('createSlugSimple')) {
	function createSlugSimple($str)
	{
		$vietnamese = array(
			'à',
			'á',
			'ạ',
			'ả',
			'ã',
			'â',
			'ầ',
			'ấ',
			'ậ',
			'ẩ',
			'ẫ',
			'ă',
			'ằ',
			'ắ',
			'ặ',
			'ẳ',
			'ẵ',
			'è',
			'é',
			'ẹ',
			'ẻ',
			'ẽ',
			'ê',
			'ề',
			'ế',
			'ệ',
			'ể',
			'ễ',
			'ì',
			'í',
			'ị',
			'ỉ',
			'ĩ',
			'ò',
			'ó',
			'ọ',
			'ỏ',
			'õ',
			'ô',
			'ồ',
			'ố',
			'ộ',
			'ổ',
			'ỗ',
			'ơ',
			'ờ',
			'ớ',
			'ợ',
			'ở',
			'ỡ',
			'ù',
			'ú',
			'ụ',
			'ủ',
			'ũ',
			'ư',
			'ừ',
			'ứ',
			'ự',
			'ử',
			'ữ',
			'ỳ',
			'ý',
			'ỵ',
			'ỷ',
			'ỹ',
			'đ',
			'À',
			'Á',
			'Ạ',
			'Ả',
			'Ã',
			'Â',
			'Ầ',
			'Ấ',
			'Ậ',
			'Ẩ',
			'Ẫ',
			'Ă',
			'Ằ',
			'Ắ',
			'Ặ',
			'Ẳ',
			'Ẵ',
			'È',
			'É',
			'Ẹ',
			'Ẻ',
			'Ẽ',
			'Ê',
			'Ề',
			'Ế',
			'Ệ',
			'Ể',
			'Ễ',
			'Ì',
			'Í',
			'Ị',
			'Ỉ',
			'Ĩ',
			'Ò',
			'Ó',
			'Ọ',
			'Ỏ',
			'Õ',
			'Ô',
			'Ồ',
			'Ố',
			'Ộ',
			'Ổ',
			'Ỗ',
			'Ơ',
			'Ờ',
			'Ớ',
			'Ợ',
			'Ở',
			'Ỡ',
			'Ù',
			'Ú',
			'Ụ',
			'Ủ',
			'Ũ',
			'Ư',
			'Ừ',
			'Ứ',
			'Ự',
			'Ử',
			'Ữ',
			'Ỳ',
			'Ý',
			'Ỵ',
			'Ỷ',
			'Ỹ',
			'Đ'
		);
		$ascii = array(
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'i',
			'i',
			'i',
			'i',
			'i',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'y',
			'y',
			'y',
			'y',
			'y',
			'd',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'e',
			'i',
			'i',
			'i',
			'i',
			'i',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'o',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'u',
			'y',
			'y',
			'y',
			'y',
			'y',
			'd'
		);
		$str = str_replace($vietnamese, $ascii, $str);
		$str = strtolower($str);
		$str = preg_replace('/[^a-z0-9\s-]/', '', $str);
		$str = preg_replace('/[\s-]+/', '-', $str);
		return trim($str, '-');
	}
}

//
$article_project_id = isset($_GET['id']) ? $_GET['id'] + 0 : (isset($article_project_id) ? $article_project_id + 0 : 0);

// Nếu không có ID, redirect về trang quản lý
if ($article_project_id == 0) {
	loadPageAdmin("Vui lòng chọn dự án để chỉnh sửa.", "?" . TTH_PATH . "=article_project_manager");
}

$db->table = "article_project";
$db->condition = "article_project_id = " . $article_project_id;
$db->order = "";
$rows = $db->select();
$menu_id = 0;
foreach ($rows as $row) {
	$menu_id = $row['article_project_menu_id'];
}
if ($db->RowCount == 0)
	loadPageAdmin("Dự án không tồn tại.", "?" . TTH_PATH . "=article_project_manager");

// Get menu name
$menu_name = "";
$category_id = 2;
$db->table = "article_project_menu";
$db->condition = "article_project_menu_id = " . $menu_id;
$rows = $db->select();
foreach ($rows as $row) {
	$menu_name = $row['name'];
	if (isset($row['category_id'])) {
		$category_id = $row['category_id'];
	}
}

// Include project template
include_once(_A_TEMPLATES . DS . "article_project_standalone.php");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_project_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_project_manager"><i class="fa fa-building"></i> Dự án BĐS</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_project_list&id=<?= $menu_id ?>"><i class="fa fa-list"></i>
				<?= stripslashes($menu_name) ?></a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa dự án
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
if (empty($typeFunc))
	$typeFunc = "no";

$date = new DateClass();

$OK = false;
$error = '';

if ($typeFunc == 'edit') {
	$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';
	$content = (isset($_POST['content'])) ? $_POST['content'] : '';

	if (empty($name))
		$error = '<span class="show-error">Vui lòng nhập tên dự án.</span>';
	elseif (empty($content))
		$error = '<span class="show-error">Vui lòng nhập nội dung chi tiết.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'project';
		// Tạo folder nếu chưa có
		if (!is_dir($dir_dest)) {
			mkdir($dir_dest, 0755, true);
		}
		$file_size = $_FILES['img']['size'];

		if ($file_size > 0) {
			$imgUp = new Upload($_FILES['img']);
			$imgUp->file_max_size = $file_max_size;
			if ($imgUp->uploaded) {
				$handleUploadImg = true;
				$OK = true;
			} else {
				$error = '<span class="show-error">Lỗi tải hình: ' . $imgUp->error . '</span>';
				$OK = false;
			}
		} else {
			$handleUploadImg = false;
			$OK = true;
		}

		if (isset($del_img)) {
			$handleUploadImg = false;
			if (glob($dir_dest . DS . '*' . $img))
				array_map("unlink", glob($dir_dest . DS . '*' . $img));

			$db->table = "article_project";
			$db->condition = "article_project_id = " . $article_project_id;
			$db->update(array('img' => 'no'));
		}

		if ($OK) {
			$db->table = "article_project";

			// Process article_tags from POST
			$articleTagsText = '';
			if (isset($_POST['article_tags']) && !empty($_POST['article_tags'])) {
				$tagData = json_decode($_POST['article_tags'], true);
				if (is_array($tagData)) {
					$tagNames = array_map(function ($t) {
						return is_array($t) ? trim($t['name']) : trim($t);
					}, $tagData);
					$tagNames = array_filter($tagNames);
					$articleTagsText = implode(',', $tagNames);
				}
			}

			// Get project fields
			$project_status = isset($_POST['project_status']) ? $_POST['project_status'] : '';
			$project_area = isset($_POST['project_area']) ? $_POST['project_area'] : '';
			$project_price_text = isset($_POST['project_price_text']) ? $_POST['project_price_text'] : '';
			$project_type_text = isset($_POST['project_type_text']) ? $_POST['project_type_text'] : '';
			$contact_button_text = isset($_POST['contact_button_text']) ? $_POST['contact_button_text'] : '';

			// Thông tin chủ đầu tư / nhà phát triển / nhà thầu
			$investor = isset($_POST['investor']) ? $_POST['investor'] : '';
			$developer = isset($_POST['developer']) ? $_POST['developer'] : '';
			$contractor = isset($_POST['contractor']) ? $_POST['contractor'] : '';

			$data = array(
				'article_project_menu_id' => $article_project_menu_id + 0,
				'name' => $db->clearText($name),
				'title' => $db->clearText($title),
				'description' => $db->clearText($description),
				'keywords' => $db->clearText($keywords),
				'article_tags' => $db->clearText($articleTagsText),
				'img_note' => $db->clearText($img_note),
				'comment' => $db->clearText($comment),
				'content' => $db->clearText($content),
				'is_active' => $is_active + 0,
				'hot' => $hot + 0,
				'project_status' => $db->clearText($project_status),
				'project_area' => $db->clearText($project_area),
				'project_price_text' => $db->clearText($project_price_text),
				'project_type_text' => $db->clearText($project_type_text),
				'contact_button_text' => $db->clearText($contact_button_text),
				'investor' => $db->clearText($investor),
				'developer' => $db->clearText($developer),
				'contractor' => $db->clearText($contractor),
				'created_time' => strtotime($date->dmYtoYmd($created_time)),
				'modified_time' => time(),
				'user_id' => $_SESSION["user_id"]
			);

			// Handle location image
			$dir_project = ROOT_DIR . DS . 'uploads' . DS . 'project';
			if (!is_dir($dir_project)) {
				mkdir($dir_project, 0755, true);
			}

			if (isset($_FILES['project_location_img']) && $_FILES['project_location_img']['size'] > 0) {
				$locImgUp = new Upload($_FILES['project_location_img']);
				$locImgUp->file_max_size = $file_max_size;
				if ($locImgUp->uploaded) {
					// Delete old image
					$old_loc_img = isset($_POST['project_location_img_old']) ? $_POST['project_location_img_old'] : '';
					if ($old_loc_img != '' && file_exists($dir_project . DS . $old_loc_img)) {
						unlink($dir_project . DS . $old_loc_img);
					}

					$stringObj = new StringHelper();
					$loc_name = 'loc_' . getRandomString() . '-' . $article_project_id . '-' . substr($stringObj->getSlug($name), 0, 50);
					$locImgUp->file_new_name_body = $loc_name;
					$locImgUp->image_resize = true;
					$locImgUp->image_x = 1200;
					$locImgUp->image_ratio_y = true;
					$locImgUp->Process($dir_project);

					if ($locImgUp->processed) {
						$data['project_location_img'] = $locImgUp->file_dst_name;
					}
					$locImgUp->Clean();
				}
			}

			// Handle delete location image
			if (isset($_POST['del_location_img'])) {
				$old_loc_img = isset($_POST['project_location_img_old']) ? $_POST['project_location_img_old'] : '';
				if ($old_loc_img != '' && file_exists($dir_project . DS . $old_loc_img)) {
					unlink($dir_project . DS . $old_loc_img);
				}
				$data['project_location_img'] = '';
			}

			// Handle video
			$project_video_enabled = isset($_POST['project_video_enabled']) ? 1 : 0;
			$data['project_video_enabled'] = $project_video_enabled;

			$video_type = isset($_POST['project_video_type']) ? $_POST['project_video_type'] : '';
			$data['project_video_type'] = $db->clearText($video_type);

			if ($project_video_enabled && $video_type) {
				if ($video_type === 'youtube') {
					$youtube_url = isset($_POST['project_video_youtube']) ? trim($_POST['project_video_youtube']) : '';
					$data['project_video'] = $db->clearText($youtube_url);
				} else {
					$project_video = isset($_POST['project_video']) ? trim($_POST['project_video']) : '';
					$data['project_video'] = $db->clearText($project_video);

					// Delete old video if changed
					$old_video = isset($_POST['project_video_old']) ? trim($_POST['project_video_old']) : '';
					if ($old_video != '' && $old_video != $project_video) {
						$dir_video = ROOT_DIR . DS . 'uploads' . DS . 'project' . DS . 'video';
						if (file_exists($dir_video . DS . $old_video)) {
							@unlink($dir_video . DS . $old_video);
						}
					}
				}
			} else {
				if (!$project_video_enabled) {
					$data['project_video_type'] = isset($_POST['project_video_type_old']) ? $_POST['project_video_type_old'] : '';
					$data['project_video'] = isset($_POST['project_video_old']) ? $_POST['project_video_old'] : '';
				}
			}

			$db->condition = "article_project_id = " . $article_project_id;
			$db->update($data);
			$id_query = $article_project_id;

			if ($handleUploadImg) {
				$stringObj = new StringHelper();

				if (glob($dir_dest . DS . '*' . $img))
					array_map("unlink", glob($dir_dest . DS . '*' . $img));

				$name_image = getRandomString() . '-' . $id_query . '-' . substr($stringObj->getSlug($name), 0, 120);

				$imgUp->file_new_name_body = 'full_' . $name_image;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body = $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_y = 256;
				$imgUp->image_x = 490;
				$imgUp->Process($dir_dest);

				if ($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "article_project";
					$db->condition = "article_project_id = " . $id_query;
					$db->update(array('img' => $db->clearText($name_img)));
				} else {
					loadPageAdmin("Lỗi tải hình: " . $imgUp->error, "?" . TTH_PATH . "=article_project_list&id=" . $article_project_menu_id);
				}

				// Create thumbnail variants
				$imgUp->file_new_name_body = 'project_' . $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_x = 480;
				$imgUp->image_y = 880;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body = 'blog_' . $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_x = 600;
				$imgUp->image_y = 400;
				$imgUp->Process($dir_dest);

				// Card Retina (800x500) - Size mới cho card lớn
				$imgUp->file_new_name_body = 'card_' . $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_x = 800;
				$imgUp->image_y = 500;
				$imgUp->Process($dir_dest);

				$imgUp->Clean();
			}

			loadPageSucces("Đã chỉnh sửa Dự án thành công.", "?" . TTH_PATH . "=article_project_list&id=" . $article_project_menu_id);
			$OK = true;
		}
	}
} else {
	$db->table = "article_project";
	$db->condition = "article_project_id = " . $article_project_id;
	$rows = $db->select();
	foreach ($rows as $row) {
		$article_project_menu_id = $row['article_project_menu_id'] + 0;
		$name = $row['name'];
		$title = $row['title'];
		$description = $row['description'];
		$keywords = $row['keywords'];
		$article_tags = isset($row['article_tags']) ? $row['article_tags'] : '';
		$img = $row['img'];
		$img_note = $row['img_note'];
		$upload_img_id = $row['upload_id'] + 0;
		$comment = $row['comment'];
		$content = $row['content'];
		$is_active = $row['is_active'] + 0;
		$hot = $row['hot'] + 0;
		$created_time = $date->vnDateTime($row['created_time']);

		// Project specific fields
		$project_status = isset($row['project_status']) ? $row['project_status'] : '';
		$project_area = isset($row['project_area']) ? $row['project_area'] : '';
		$project_price_text = isset($row['project_price_text']) ? $row['project_price_text'] : '';
		$project_type_text = isset($row['project_type_text']) ? $row['project_type_text'] : '';
		$project_location_img = isset($row['project_location_img']) ? $row['project_location_img'] : '';
		$contact_button_text = isset($row['contact_button_text']) ? $row['contact_button_text'] : '';

		// Thông tin chủ đầu tư
		$investor = isset($row['investor']) ? $row['investor'] : '';
		$developer = isset($row['developer']) ? $row['developer'] : '';
		$contractor = isset($row['contractor']) ? $row['contractor'] : '';

		// Video fields
		$project_video = isset($row['project_video']) ? $row['project_video'] : '';
		$project_video_type = isset($row['project_video_type']) ? $row['project_video_type'] : '';
		$project_video_enabled = isset($row['project_video_enabled']) ? $row['project_video_enabled'] + 0 : 0;
	}

	// Create upload_id if not exists
	if ($upload_img_id == 0) {
		$db->table = "uploads_tmp";
		$db->insert(array('created_time' => time()));
		$upload_img_id = $db->LastInsertID;

		$db->table = "article_project";
		$db->condition = "article_project_id = " . $article_project_id;
		$db->update(array('upload_id' => $upload_img_id));
	}
}

if (!$OK) {
	$projectData = array(
		'project_status' => $project_status,
		'project_area' => $project_area,
		'project_price_text' => $project_price_text,
		'project_type_text' => $project_type_text,
		'project_location_img' => $project_location_img,
		'contact_button_text' => $contact_button_text,
		'article_tags' => $article_tags,
		'project_video' => $project_video,
		'project_video_type' => $project_video_type,
		'project_video_enabled' => $project_video_enabled,
		'investor' => $investor,
		'developer' => $developer,
		'contractor' => $contractor
	);
	articleProjectStandalone("?" . TTH_PATH . "=article_project_edit&id=" . $article_project_id, "edit", $article_project_id, $article_project_menu_id, $name, $title, $description, $keywords, $img, $img_note, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error, $projectData);
}
?>