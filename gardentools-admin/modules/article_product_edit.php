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
$article_product_id = isset($_GET['id']) ? $_GET['id'] + 0 : $article_product_id + 0;
$db->table = "article_product";
$db->condition = "article_product_id = " . $article_product_id;
$db->order = "";
$rows = $db->select();
$menu_id = 0;
foreach ($rows as $row) {
	$menu_id = $row['article_product_menu_id'];
}
if ($db->RowCount == 0)
	loadPageAdmin("Sản phẩm không tồn tại.", "?" . TTH_PATH . "=article_product_manager");

// Get menu name
$menu_name = "";
$category_id = 3; // Default category for products
$db->table = "article_product_menu";
$db->condition = "article_product_menu_id = " . $menu_id;
$rows = $db->select();
foreach ($rows as $row) {
	$menu_name = $row['name'];
	if (isset($row['category_id'])) {
		$category_id = $row['category_id'];
	}
}

// Include product template
include_once(_A_TEMPLATES . DS . "article_product_standalone.php");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_product_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_product_manager"><i class="fa fa-shopping-cart"></i> Sản phẩm BĐS</a>
		</li>
		<li>
			<a href="?<?= TTH_PATH ?>=article_product_list&id=<?= $menu_id ?>"><i class="fa fa-list"></i>
				<?= stripslashes($menu_name) ?></a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa sản phẩm
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
		$error = '<span class="show-error">Vui lòng nhập tên sản phẩm.</span>';
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

		if (isset($del_img)) {
			$handleUploadImg = false;
			if (glob($dir_dest . DS . '*' . $img))
				array_map("unlink", glob($dir_dest . DS . '*' . $img));

			$db->table = "article_product";
			$db->condition = "article_product_id = " . $article_product_id;
			$db->update(array('img' => 'no'));
		}

		if ($OK) {
			$db->table = "article_product";

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

			// Get product fields
			$product_status = isset($_POST['product_status']) ? $_POST['product_status'] : '';
			$sale_status = isset($_POST['sale_status']) ? $_POST['sale_status'] : 'available';
			$product_area = isset($_POST['product_area']) ? $_POST['product_area'] : '';
			$product_price_text = isset($_POST['product_price_text']) ? $_POST['product_price_text'] : '';
			$product_type_text = isset($_POST['product_type_text']) ? $_POST['product_type_text'] : '';
			$contact_button_text = isset($_POST['contact_button_text']) ? $_POST['contact_button_text'] : '';

			// Property detail fields - Đặc điểm BĐS
			$price = isset($_POST['price']) ? preg_replace('/[^0-9]/', '', $_POST['price']) : '';
			$price_per_m2 = isset($_POST['price_per_m2']) ? preg_replace('/[^0-9]/', '', $_POST['price_per_m2']) : '';
			$area = isset($_POST['area']) && $_POST['area'] !== '' ? floatval($_POST['area']) : 0;
			$area_land = isset($_POST['area_land']) && $_POST['area_land'] !== '' ? floatval($_POST['area_land']) : 0;
			$area_construction = isset($_POST['area_construction']) && $_POST['area_construction'] !== '' ? floatval($_POST['area_construction']) : 0;
			$bedrooms = isset($_POST['bedrooms']) && $_POST['bedrooms'] !== '' ? intval($_POST['bedrooms']) : 0;
			$bathrooms = isset($_POST['bathrooms']) && $_POST['bathrooms'] !== '' ? intval($_POST['bathrooms']) : 0;
			$block = isset($_POST['block']) ? $_POST['block'] : '';
			$floor = isset($_POST['floor']) ? $_POST['floor'] : '';
			$direction = isset($_POST['direction']) ? $_POST['direction'] : '';
			$direction_balcony = isset($_POST['direction_balcony']) ? $_POST['direction_balcony'] : '';
			$view_type = isset($_POST['view_type']) ? $_POST['view_type'] : '';
			$legal_status = isset($_POST['legal_status']) ? $_POST['legal_status'] : '';
			$furniture_status = isset($_POST['furniture_status']) ? $_POST['furniture_status'] : '';

			// Tiện ích đi kèm
			$has_elevator = isset($_POST['has_elevator']) ? 1 : 0;
			$has_parking = isset($_POST['has_parking']) ? 1 : 0;
			$parking_car = isset($_POST['parking_car']) && $_POST['parking_car'] !== '' ? intval($_POST['parking_car']) : 0;
			$parking_motor = isset($_POST['parking_motor']) && $_POST['parking_motor'] !== '' ? intval($_POST['parking_motor']) : 0;
			$has_pool = isset($_POST['has_pool']) ? 1 : 0;
			$has_garden = isset($_POST['has_garden']) ? 1 : 0;
			$has_rooftop = isset($_POST['has_rooftop']) ? 1 : 0;
			$balconies = isset($_POST['balconies']) && $_POST['balconies'] !== '' ? intval($_POST['balconies']) : 0;

			// Liên kết bài viết dự án
			$article_project_id = isset($_POST['article_project_id']) ? intval($_POST['article_project_id']) : 0;

			// ========== FIELDS MỚI ==========
			// Mã căn
			$product_code = isset($_POST['product_code']) ? $_POST['product_code'] : '';

			// Diện tích thông thủy
			$area_carpet = isset($_POST['area_carpet']) && $_POST['area_carpet'] !== '' ? floatval($_POST['area_carpet']) : 0;

			// Giá & Khuyến mãi
			$price_negotiable = isset($_POST['price_negotiable']) ? 1 : 0;
			$sale = isset($_POST['sale']) ? preg_replace('/[^0-9]/', '', $_POST['sale']) : '';
			$discount_percent = isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '' ? floatval($_POST['discount_percent']) : 0;
			$discount_text = isset($_POST['discount_text']) ? $_POST['discount_text'] : '';
			$booking_fee = isset($_POST['booking_fee']) ? preg_replace('/[^0-9]/', '', $_POST['booking_fee']) : '';

			// Bàn giao
			$handover_standard = isset($_POST['handover_standard']) ? $_POST['handover_standard'] : '';

			// Virtual Tour
			$virtual_tour_url = isset($_POST['virtual_tour_url']) ? trim($_POST['virtual_tour_url']) : '';

			// Đất nền / Nhà phố / Biệt thự
			$frontage = isset($_POST['frontage']) && $_POST['frontage'] !== '' ? floatval($_POST['frontage']) : 0;
			$depth = isset($_POST['depth']) && $_POST['depth'] !== '' ? floatval($_POST['depth']) : 0;
			$floors_count = isset($_POST['floors_count']) && $_POST['floors_count'] !== '' ? intval($_POST['floors_count']) : 0;
			$alley_width = isset($_POST['alley_width']) && $_POST['alley_width'] !== '' ? floatval($_POST['alley_width']) : 0;

			$data = array(
				'article_product_menu_id' => $article_product_menu_id + 0,
				'article_project_id' => $article_project_id,
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
				'product_status' => $db->clearText($product_status),
				'sale_status' => $db->clearText($sale_status),
				'product_area' => $db->clearText($product_area),
				'product_price_text' => $db->clearText($product_price_text),
				'product_type_text' => $db->clearText($product_type_text),
				'contact_button_text' => $db->clearText($contact_button_text),
				// Đặc điểm BĐS
				'price' => $price ? intval($price) : 0,
				'price_per_m2' => $price_per_m2 ? intval($price_per_m2) : 0,
				'area' => $area,
				'area_land' => $area_land,
				'area_construction' => $area_construction,
				'bedrooms' => $bedrooms,
				'bathrooms' => $bathrooms,
				'block' => $db->clearText($block),
				'floor' => $db->clearText($floor),
				'direction' => $db->clearText($direction),
				'direction_balcony' => $db->clearText($direction_balcony),
				'view_type' => $db->clearText($view_type),
				'legal_status' => $db->clearText($legal_status),
				'furniture_status' => $db->clearText($furniture_status),
				// Tiện ích đi kèm
				'has_elevator' => $has_elevator,
				'has_parking' => $has_parking,
				'parking_car' => $parking_car,
				'parking_motor' => $parking_motor,
				'has_pool' => $has_pool,
				'has_garden' => $has_garden,
				'has_rooftop' => $has_rooftop,
				'balconies' => $balconies,
				// Fields mới
				'product_code' => $db->clearText($product_code),
				'area_carpet' => $area_carpet,
				'price_negotiable' => $price_negotiable,
				'sale' => $sale ? intval($sale) : 0,
				'discount_percent' => $discount_percent,
				'discount_text' => $db->clearText($discount_text),
				'booking_fee' => $booking_fee ? intval($booking_fee) : 0,
				'handover_standard' => $db->clearText($handover_standard),
				'virtual_tour_url' => $db->clearText($virtual_tour_url),
				'frontage' => $frontage,
				'depth' => $depth,
				'floors_count' => $floors_count,
				'alley_width' => $alley_width,
				'created_time' => strtotime($date->dmYtoYmd($created_time)),
				'modified_time' => time(),
				'user_id' => $_SESSION["user_id"]
			);

			// Handle location image
			$dir_product = ROOT_DIR . DS . 'uploads' . DS . 'product';
			if (!is_dir($dir_product)) {
				mkdir($dir_product, 0755, true);
			}

			if (isset($_FILES['product_location_img']) && $_FILES['product_location_img']['size'] > 0) {
				$locImgUp = new Upload($_FILES['product_location_img']);
				$locImgUp->file_max_size = $file_max_size;
				if ($locImgUp->uploaded) {
					// Delete old image
					$old_loc_img = isset($_POST['product_location_img_old']) ? $_POST['product_location_img_old'] : '';
					if ($old_loc_img != '' && file_exists($dir_product . DS . $old_loc_img)) {
						unlink($dir_product . DS . $old_loc_img);
					}

					$stringObj = new StringHelper();
					$loc_name = 'loc_' . getRandomString() . '-' . $article_product_id . '-' . substr($stringObj->getSlug($name), 0, 50);
					$locImgUp->file_new_name_body = $loc_name;
					$locImgUp->image_resize = true;
					$locImgUp->image_x = 1200;
					$locImgUp->image_ratio_y = true;
					$locImgUp->Process($dir_product);

					if ($locImgUp->processed) {
						$data['product_location_img'] = $locImgUp->file_dst_name;
					}
					$locImgUp->Clean();
				}
			}

			// Handle delete location image
			if (isset($_POST['del_location_img'])) {
				$old_loc_img = isset($_POST['product_location_img_old']) ? $_POST['product_location_img_old'] : '';
				if ($old_loc_img != '' && file_exists($dir_product . DS . $old_loc_img)) {
					unlink($dir_product . DS . $old_loc_img);
				}
				$data['product_location_img'] = '';
			}

			// Handle video
			$product_video_enabled = isset($_POST['product_video_enabled']) ? 1 : 0;
			$data['product_video_enabled'] = $product_video_enabled;

			$video_type = isset($_POST['product_video_type']) ? $_POST['product_video_type'] : '';
			$data['product_video_type'] = $db->clearText($video_type);

			if ($product_video_enabled && $video_type) {
				if ($video_type === 'youtube') {
					$youtube_url = isset($_POST['product_video_youtube']) ? trim($_POST['product_video_youtube']) : '';
					$data['product_video'] = $db->clearText($youtube_url);
				} else {
					$product_video = isset($_POST['product_video']) ? trim($_POST['product_video']) : '';
					$data['product_video'] = $db->clearText($product_video);

					// Delete old video if changed
					$old_video = isset($_POST['product_video_old']) ? trim($_POST['product_video_old']) : '';
					if ($old_video != '' && $old_video != $product_video) {
						$dir_video = ROOT_DIR . DS . 'uploads' . DS . 'product' . DS . 'video';
						if (file_exists($dir_video . DS . $old_video)) {
							@unlink($dir_video . DS . $old_video);
						}
					}
				}
			} else {
				if (!$product_video_enabled) {
					$data['product_video_type'] = isset($_POST['product_video_type_old']) ? $_POST['product_video_type_old'] : '';
					$data['product_video'] = isset($_POST['product_video_old']) ? $_POST['product_video_old'] : '';
				}
			}

			$db->condition = "article_product_id = " . $article_product_id;
			$db->update($data);
			$id_query = $article_product_id;

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
					$db->table = "article_product";
					$db->condition = "article_product_id = " . $id_query;
					$db->update(array('img' => $db->clearText($name_img)));
				} else {
					loadPageAdmin("Lỗi tải hình: " . $imgUp->error, "?" . TTH_PATH . "=article_product_list&id=" . $article_product_menu_id);
				}

				// Create thumbnail variants
				$imgUp->file_new_name_body = 'product_' . $name_image;
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

			loadPageSucces("Đã chỉnh sửa Sản phẩm thành công.", "?" . TTH_PATH . "=article_product_list&id=" . $article_product_menu_id);
			$OK = true;
		}
	}
} else {
	$db->table = "article_product";
	$db->condition = "article_product_id = " . $article_product_id;
	$rows = $db->select();
	foreach ($rows as $row) {
		$article_product_menu_id = $row['article_product_menu_id'] + 0;
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

		// Product specific fields
		$product_status = isset($row['product_status']) ? $row['product_status'] : '';
		$sale_status = isset($row['sale_status']) ? $row['sale_status'] : 'available';
		$product_area = isset($row['product_area']) ? $row['product_area'] : '';
		$product_price_text = isset($row['product_price_text']) ? $row['product_price_text'] : '';
		$product_type_text = isset($row['product_type_text']) ? $row['product_type_text'] : '';
		$product_location_img = isset($row['product_location_img']) ? $row['product_location_img'] : '';
		$contact_button_text = isset($row['contact_button_text']) ? $row['contact_button_text'] : '';

		// Video fields
		$product_video = isset($row['product_video']) ? $row['product_video'] : '';
		$product_video_type = isset($row['product_video_type']) ? $row['product_video_type'] : '';
		$product_video_enabled = isset($row['product_video_enabled']) ? $row['product_video_enabled'] + 0 : 0;

		// Property detail fields - Đặc điểm BĐS
		$price = isset($row['price']) ? $row['price'] : '';
		$price_per_m2 = isset($row['price_per_m2']) ? $row['price_per_m2'] : '';
		$area = isset($row['area']) ? $row['area'] : '';
		$area_land = isset($row['area_land']) ? $row['area_land'] : '';
		$area_construction = isset($row['area_construction']) ? $row['area_construction'] : '';
		$bedrooms = isset($row['bedrooms']) ? $row['bedrooms'] : '';
		$bathrooms = isset($row['bathrooms']) ? $row['bathrooms'] : '';
		$block = isset($row['block']) ? $row['block'] : '';
		$floor = isset($row['floor']) ? $row['floor'] : '';
		$direction = isset($row['direction']) ? $row['direction'] : '';
		$direction_balcony = isset($row['direction_balcony']) ? $row['direction_balcony'] : '';
		$view_type = isset($row['view_type']) ? $row['view_type'] : '';
		$legal_status = isset($row['legal_status']) ? $row['legal_status'] : '';
		$furniture_status = isset($row['furniture_status']) ? $row['furniture_status'] : '';

		// Tiện ích đi kèm
		$has_elevator = isset($row['has_elevator']) ? $row['has_elevator'] + 0 : 0;
		$has_parking = isset($row['has_parking']) ? $row['has_parking'] + 0 : 0;
		$parking_car = isset($row['parking_car']) ? $row['parking_car'] + 0 : 0;
		$parking_motor = isset($row['parking_motor']) ? $row['parking_motor'] + 0 : 0;
		$has_pool = isset($row['has_pool']) ? $row['has_pool'] + 0 : 0;
		$has_garden = isset($row['has_garden']) ? $row['has_garden'] + 0 : 0;
		$has_rooftop = isset($row['has_rooftop']) ? $row['has_rooftop'] + 0 : 0;
		$balconies = isset($row['balconies']) ? $row['balconies'] + 0 : 0;

		// Liên kết dự án
		$article_project_id = isset($row['article_project_id']) ? $row['article_project_id'] + 0 : 0;

		// ========== FIELDS MỚI ==========
		$product_code = isset($row['product_code']) ? $row['product_code'] : '';
		$area_carpet = isset($row['area_carpet']) ? $row['area_carpet'] : '';
		$price_negotiable = isset($row['price_negotiable']) ? $row['price_negotiable'] + 0 : 0;
		$sale = isset($row['sale']) ? $row['sale'] : '';
		$discount_percent = isset($row['discount_percent']) ? $row['discount_percent'] : '';
		$discount_text = isset($row['discount_text']) ? $row['discount_text'] : '';
		$booking_fee = isset($row['booking_fee']) ? $row['booking_fee'] : '';
		$handover_standard = isset($row['handover_standard']) ? $row['handover_standard'] : '';
		$frontage = isset($row['frontage']) ? $row['frontage'] : '';
		$depth = isset($row['depth']) ? $row['depth'] : '';
		$floors_count = isset($row['floors_count']) ? $row['floors_count'] : '';
		$alley_width = isset($row['alley_width']) ? $row['alley_width'] : '';
	}

	// Create upload_id if not exists
	if ($upload_img_id == 0) {
		$db->table = "uploads_tmp";
		$db->insert(array('created_time' => time()));
		$upload_img_id = $db->LastInsertID;

		$db->table = "article_product";
		$db->condition = "article_product_id = " . $article_product_id;
		$db->update(array('upload_id' => $upload_img_id));
	}
}

if (!$OK) {
	$productData = array(
		'article_project_id' => $article_project_id,
		'product_status' => $product_status,
		'sale_status' => $sale_status,
		'product_area' => $product_area,
		'product_price_text' => $product_price_text,
		'product_type_text' => $product_type_text,
		'product_location_img' => $product_location_img,
		'contact_button_text' => $contact_button_text,
		'article_tags' => $article_tags,
		'product_video' => $product_video,
		'product_video_type' => $product_video_type,
		'product_video_enabled' => $product_video_enabled,
		// Đặc điểm BĐS
		'price' => $price,
		'price_per_m2' => $price_per_m2,
		'area' => $area,
		'area_land' => $area_land,
		'area_construction' => $area_construction,
		'bedrooms' => $bedrooms,
		'bathrooms' => $bathrooms,
		'block' => $block,
		'floor' => $floor,
		'direction' => $direction,
		'direction_balcony' => $direction_balcony,
		'view_type' => $view_type,
		'legal_status' => $legal_status,
		'furniture_status' => $furniture_status,
		// Tiện ích đi kèm
		'has_elevator' => $has_elevator,
		'has_parking' => $has_parking,
		'parking_car' => $parking_car,
		'parking_motor' => $parking_motor,
		'has_pool' => $has_pool,
		'has_garden' => $has_garden,
		'has_rooftop' => $has_rooftop,
		'balconies' => $balconies,
		// Fields mới
		'product_code' => $product_code,
		'area_carpet' => $area_carpet,
		'price_negotiable' => $price_negotiable,
		'sale' => $sale,
		'discount_percent' => $discount_percent,
		'discount_text' => $discount_text,
		'booking_fee' => $booking_fee,
		'handover_standard' => $handover_standard,
		'frontage' => $frontage,
		'depth' => $depth,
		'floors_count' => $floors_count,
		'alley_width' => $alley_width
	);
	articleProductStandalone("?" . TTH_PATH . "=article_product_edit", "edit", $article_product_id, $article_product_menu_id, $name, $title, $description, $keywords, $img, $img_note, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error, $productData);
}
?>