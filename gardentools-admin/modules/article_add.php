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
$article_menu_id = isset($_GET['id']) ? $_GET['id'] + 0 : $article_menu_id + 0;
$db->table = "article_menu";
$db->condition = "article_menu_id = " . $article_menu_id;
$rows = $db->select();
if ($db->RowCount == 0)
	loadPageAdmin("Mục không tồn tại.", "?" . TTH_PATH . "=article_manager");

// Check if this is a project article (category_id = 2, slug = 'du-an')
include_once(_A_TEMPLATES . DS . "article_project.php");
$isProject = isProjectArticle($db, $article_menu_id);
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_manager"><i class="fa fa-newspaper-o"></i>
				<?= $isProject ? 'Dự án' : 'Bài viết' ?></a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_list&id=<?= $article_menu_id ?>"><i class="fa fa-list"></i>
				<?= getNameMenuArt($article_menu_id) ?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm <?= $isProject ? 'dự án' : 'bài viết' ?>
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
if (!$isProject) {
	include_once(_A_TEMPLATES . DS . "article.php");
}
if (empty($typeFunc))
	$typeFunc = "no";

$date = new DateClass();

$OK = false;
$error = '';
if ($typeFunc == 'add') {
	$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';
	$content = (isset($_POST['content'])) ? $_POST['content'] : '';
	$is_project_form = isset($_POST['is_project']) ? $_POST['is_project'] : 0;

	if (empty($name))
		$error = '<span class="show-error">Vui lòng nhập tiêu đề bài viết.</span>';
	elseif (empty($content))
		$error = '<span class="show-error">Vui lòng nhập nội dung chi tiết.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'article';
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

		if ($OK) {
			$id_query = 0;
			$db->table = "article";

			// Process article_tags from POST (JSON array from tags-manager.js)
			$articleTagsText = '';
			if (isset($_POST['article_tags']) && !empty($_POST['article_tags'])) {
				$tagData = json_decode($_POST['article_tags'], true);
				if (is_array($tagData)) {
					// Extract tag names and join with comma
					$tagNames = array_map(function ($t) {
						return is_array($t) ? trim($t['name']) : trim($t);
					}, $tagData);
					$tagNames = array_filter($tagNames); // Remove empty
					$articleTagsText = implode(',', $tagNames);
				}
			}

			$data = array(
				'article_menu_id' => $article_menu_id + 0,
				'name' => $db->clearText($name),
				'title' => $db->clearText($title),
				'description' => $db->clearText($description),
				'keywords' => $db->clearText($keywords),
				'article_tags' => $db->clearText($articleTagsText),
				'img_note' => $db->clearText($img_note),
				'upload_id' => $upload_img_id + 0,
				'comment' => $db->clearText($comment),
				'content' => $db->clearText($content),
				'is_active' => $is_active + 0,
				'hot' => $hot + 0,
				'created_time' => strtotime($date->dmYtoYmd($created_time)),
				'modified_time' => time(),
				'user_id' => $_SESSION["user_id"]
			);

			// If this is a project, add project-specific fields
			if ($is_project_form == 1) {
				$project_status = isset($_POST['project_status']) ? $_POST['project_status'] : '';
				$project_area = isset($_POST['project_area']) ? $_POST['project_area'] : '';
				$project_price_text = isset($_POST['project_price_text']) ? $_POST['project_price_text'] : '';
				$project_type_text = isset($_POST['project_type_text']) ? $_POST['project_type_text'] : '';
				$project_utilities_upload_id = isset($_POST['project_utilities_upload_id']) ? $_POST['project_utilities_upload_id'] + 0 : 0;
				$project_floor_upload_id = isset($_POST['project_floor_upload_id']) ? $_POST['project_floor_upload_id'] + 0 : 0;
				$project_gallery_upload_id = isset($_POST['project_gallery_upload_id']) ? $_POST['project_gallery_upload_id'] + 0 : 0;
				$project_progress_upload_id = isset($_POST['project_progress_upload_id']) ? $_POST['project_progress_upload_id'] + 0 : 0;
				$project_policy_upload_id = isset($_POST['project_policy_upload_id']) ? $_POST['project_policy_upload_id'] + 0 : 0;

				$data['project_status'] = $db->clearText($project_status);
				$data['project_area'] = $db->clearText($project_area);
				$data['project_price_text'] = $db->clearText($project_price_text);
				$data['project_type_text'] = $db->clearText($project_type_text);
				$data['project_utilities_upload_id'] = $project_utilities_upload_id;
				$data['project_floor_upload_id'] = $project_floor_upload_id;
				$data['project_gallery_upload_id'] = $project_gallery_upload_id;
				$data['project_progress_upload_id'] = $project_progress_upload_id;
				$data['project_policy_upload_id'] = $project_policy_upload_id;

				// Handle project video
				// Video đã được upload qua API riêng, chỉ cần lưu thông tin
				$project_video_enabled = isset($_POST['project_video_enabled']) ? 1 : 0;
				$data['project_video_enabled'] = $project_video_enabled;

				$video_type = isset($_POST['project_video_type']) ? $_POST['project_video_type'] : '';
				$data['project_video_type'] = $db->clearText($video_type);

				if ($project_video_enabled && $video_type) {
					if ($video_type === 'youtube') {
						// YouTube: lấy URL từ input youtube
						$youtube_url = isset($_POST['project_video_youtube']) ? trim($_POST['project_video_youtube']) : '';
						$data['project_video'] = $db->clearText($youtube_url);
					} else {
						// Upload: video đã upload qua API, lấy filename từ hidden field
						$project_video = isset($_POST['project_video']) ? trim($_POST['project_video']) : '';
						$data['project_video'] = $db->clearText($project_video);
					}
				}
			}

			$db->insert($data);
			$id_query = $db->LastInsertID;

			// Handle location image for project
			if ($is_project_form == 1 && isset($_FILES['project_location_img']) && $_FILES['project_location_img']['size'] > 0) {
				$dir_project = ROOT_DIR . DS . 'uploads' . DS . 'project';
				if (!is_dir($dir_project)) {
					mkdir($dir_project, 0755, true);
				}

				$locImgUp = new Upload($_FILES['project_location_img']);
				$locImgUp->file_max_size = $file_max_size;
				if ($locImgUp->uploaded) {
					$stringObj = new StringHelper();
					$loc_name = 'loc_' . getRandomString() . '-' . $id_query . '-' . substr($stringObj->getSlug($name), 0, 50);
					$locImgUp->file_new_name_body = $loc_name;
					$locImgUp->image_resize = true;
					$locImgUp->image_x = 1200;
					$locImgUp->image_ratio_y = true;
					$locImgUp->Process($dir_project);

					if ($locImgUp->processed) {
						$db->table = "article";
						$db->condition = "article_id = " . $id_query;
						$db->update(array('project_location_img' => $locImgUp->file_dst_name));
					}
					$locImgUp->Clean();
				}
			}

			// Note: Video đã được upload qua API riêng (upload_project_video.php)
			// Không cần xử lý upload video ở đây nữa

			if ($handleUploadImg) {
				$stringObj = new StringHelper();
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
					$db->table = "article";
					$data = array(
						'img' => $db->clearText($name_img)
					);
					$db->condition = "article_id = " . $id_query;
					$db->update($data);
				} else {
					loadPageAdmin("Lỗi tải hình: " . $imgUp->error, "?" . TTH_PATH . "=article_list&id=" . $article_menu_id);
				}

				$imgUp->file_new_name_body = 'hoa-' . $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_y = 426;
				$imgUp->image_x = 224;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body = 'hoavo-' . $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_y = 228;
				$imgUp->image_x = 354;
				$imgUp->Process($dir_dest);

				// Home-5 Project (Portrait)
				$imgUp->file_new_name_body = 'project_' . $name_image;
				$imgUp->image_resize = true;
				$imgUp->image_ratio_crop = true;
				$imgUp->image_x = 480;
				$imgUp->image_y = 880;
				$imgUp->Process($dir_dest);

				// Home-5 Blog (Landscape)
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

			$db->table = "uploads_tmp";
			$data = array(
				'status' => 1
			);
			$db->condition = "upload_id = " . ($upload_img_id + 0);
			$db->update($data);

			// Update status for project upload_ids (legacy - kept for backward compatibility)
			if ($is_project_form == 1) {
				$projectUploadIds = array($project_utilities_upload_id, $project_floor_upload_id, $project_gallery_upload_id, $project_progress_upload_id, $project_policy_upload_id);
				foreach ($projectUploadIds as $uid) {
					if ($uid > 0) {
						$db->table = "uploads_tmp";
						$db->condition = "upload_id = " . $uid;
						$db->update(array('status' => 1));
					}
				}

				// Finalize Project Gallery Manager data (move from temp article_id=0 to real article_id)
				// Update categories
				$db->table = "project_gallery_category";
				$db->condition = "article_id = 0";
				$db->update(array('article_id' => $id_query));

				// Update images
				$db->table = "project_gallery_image";
				$db->condition = "article_id = 0";
				$db->update(array('article_id' => $id_query));

				// Update tab descriptions
				$db->table = "project_gallery_tab";
				$db->condition = "article_id = 0";
				$db->update(array('article_id' => $id_query));
			}

			// Sync tags to dxmt_tags + dxmt_article_tags for Related Articles feature
			// Note: article_tags text already saved in dxmt_article above
			try {
				// Check if tags tables exist
				$tagsTableCheck = $db->query("SHOW TABLES LIKE '" . TTH_DATA_PREFIX . "tags'");
				$articleTagsTableCheck = $db->query("SHOW TABLES LIKE '" . TTH_DATA_PREFIX . "article_tags'");

				if (!empty($tagsTableCheck) && !empty($articleTagsTableCheck) && !empty($articleTagsText)) {
					$tagNames = array_filter(array_map('trim', explode(',', $articleTagsText)));

					foreach ($tagNames as $tagName) {
						if (empty($tagName))
							continue;

						$tagSlug = createSlugSimple($tagName);

						// Find or create tag - Note: $db->table auto-adds prefix, so just use "tags"
						$db->table = "tags";
						$db->condition = "slug = '" . $db->clearText($tagSlug) . "'";
						$db->order = "";
						$db->limit = "1";
						$existingTag = $db->select("id");

						if (!empty($existingTag)) {
							$tagId = intval($existingTag[0]['id']);
							// Update usage count
							$db->query("UPDATE " . TTH_DATA_PREFIX . "tags SET usage_count = usage_count + 1 WHERE id = $tagId");
						} else {
							// Insert new tag
							$db->table = "tags";
							$db->insert([
								'name' => $db->clearText($tagName),
								'slug' => $db->clearText($tagSlug),
								'tag_type' => 'general',
								'usage_count' => 1,
								'is_active' => 1
							]);
							$tagId = $db->LastInsertID;
						}

						// Link article to tag
						if ($tagId > 0) {
							$db->table = "article_tags";
							$db->insert([
								'article_id' => $id_query,
								'tag_id' => $tagId
							]);
						}
					}
				}
			} catch (Exception $e) {
				// Tags tables may not exist yet, silently ignore
				error_log("[Tags Sync] Error: " . $e->getMessage());
			}

			loadPageSucces("Đã thêm " . ($isProject ? "Dự án" : "Bài viết") . " thành công.", "?" . TTH_PATH . "=article_list&id=" . $article_menu_id);
			$OK = true;
		}
	}
} else {
	$upload_img_id = 0;
	// Note: Project gallery upload_ids no longer needed - using ProjectGalleryManager with AJAX
	$project_utilities_upload_id = 0;
	$project_floor_upload_id = 0;
	$project_gallery_upload_id = 0;
	$project_progress_upload_id = 0;
	$project_policy_upload_id = 0;

	// Create main upload_id (for main article image only)
	$db->table = "uploads_tmp";
	$data = array('created_time' => time());
	$db->insert($data);
	$upload_img_id = $db->LastInsertID;

	// Note: Project gallery images now managed via ProjectGalleryManager API
	// No need to create separate upload_ids for each tab

	$name = "";
	$title = "";
	$description = "";
	$keywords = "";
	$img = "";
	$img_note = "";
	$comment = "";
	$content = "";
	$is_active = 1;
	$hot = 0;
	$created_time = $date->vnDateTime(time());

	// Project specific
	$project_status = "";
	$project_area = "";
	$project_price_text = "";
	$project_type_text = "";
	$project_location_img = "";
	$project_video = "";
	$project_video_type = "";
	$project_video_enabled = 0;
}

if (!$OK) {
	if ($isProject) {
		// Use project template
		$projectData = array(
			'project_status' => $project_status,
			'project_area' => $project_area,
			'project_price_text' => $project_price_text,
			'project_type_text' => $project_type_text,
			'project_location_img' => $project_location_img,
			'project_utilities_upload_id' => $project_utilities_upload_id,
			'project_floor_upload_id' => $project_floor_upload_id,
			'project_gallery_upload_id' => $project_gallery_upload_id,
			'project_progress_upload_id' => $project_progress_upload_id,
			'project_policy_upload_id' => $project_policy_upload_id,
			'project_video' => $project_video,
			'project_video_type' => $project_video_type,
			'project_video_enabled' => $project_video_enabled
		);
		articleProject("?" . TTH_PATH . "=article_add", "add", 0, $article_menu_id, $name, $title, $description, $keywords, $img, $img_note, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error, $projectData);
	} else {
		// Use normal article template
		article("?" . TTH_PATH . "=article_add", "add", 0, $article_menu_id, $name, $title, $description, $keywords, $img, $img_note, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error);
	}
}
?>