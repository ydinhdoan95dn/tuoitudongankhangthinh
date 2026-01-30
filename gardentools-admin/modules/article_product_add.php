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
$article_product_menu_id = isset($_GET['id']) ? $_GET['id'] + 0 : $article_product_menu_id + 0;
$db->table = "article_product_menu";
$db->condition = "article_product_menu_id = " . $article_product_menu_id;
$rows = $db->select();
$menu_name = "";
$category_id = 3; // Default category for products
foreach ($rows as $row) {
	$menu_name = $row['name'];
	if (isset($row['category_id'])) {
		$category_id = $row['category_id'];
	}
}
if ($db->RowCount == 0)
	loadPageAdmin("Thể loại không tồn tại.", "?" . TTH_PATH . "=article_product_manager");

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
			<a href="?<?= TTH_PATH ?>=article_product_list&id=<?= $article_product_menu_id ?>"><i class="fa fa-list"></i>
				<?= stripslashes($menu_name) ?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm sản phẩm
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
if ($typeFunc == 'add') {
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

		if ($OK) {
			$id_query = 0;
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

			// Trường bổ sung - Mã sản phẩm & Giá khuyến mãi
			$product_code = isset($_POST['product_code']) ? $_POST['product_code'] : '';
			$area_carpet = isset($_POST['area_carpet']) && $_POST['area_carpet'] !== '' ? floatval($_POST['area_carpet']) : 0;
			$price_negotiable = isset($_POST['price_negotiable']) ? 1 : 0;
			$sale = isset($_POST['sale']) ? preg_replace('/[^0-9]/', '', $_POST['sale']) : '';
			$discount_percent = isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '' ? floatval($_POST['discount_percent']) : 0;
			$discount_text = isset($_POST['discount_text']) ? $_POST['discount_text'] : '';
			$booking_fee = isset($_POST['booking_fee']) ? preg_replace('/[^0-9]/', '', $_POST['booking_fee']) : '';
			$handover_standard = isset($_POST['handover_standard']) ? $_POST['handover_standard'] : '';

			// Đất nền / Nhà phố / Biệt thự
			$frontage = isset($_POST['frontage']) && $_POST['frontage'] !== '' ? floatval($_POST['frontage']) : 0;
			$depth = isset($_POST['depth']) && $_POST['depth'] !== '' ? floatval($_POST['depth']) : 0;
			$floors_count = isset($_POST['floors_count']) && $_POST['floors_count'] !== '' ? intval($_POST['floors_count']) : 0;
			$alley_width = isset($_POST['alley_width']) && $_POST['alley_width'] !== '' ? floatval($_POST['alley_width']) : 0;

			// Liên kết bài viết dự án
			$article_project_id = isset($_POST['article_project_id']) ? intval($_POST['article_project_id']) : 0;

			$data = array(
				'article_product_menu_id' => $article_product_menu_id + 0,
				'article_project_id' => $article_project_id,
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
				// Trường bổ sung
				'product_code' => $db->clearText($product_code),
				'area_carpet' => $area_carpet,
				'price_negotiable' => $price_negotiable,
				'sale' => $sale ? intval($sale) : 0,
				'discount_percent' => $discount_percent,
				'discount_text' => $db->clearText($discount_text),
				'booking_fee' => $booking_fee ? intval($booking_fee) : 0,
				'handover_standard' => $db->clearText($handover_standard),
				// Đất nền / Nhà phố / Biệt thự
				'frontage' => $frontage,
				'depth' => $depth,
				'floors_count' => $floors_count,
				'alley_width' => $alley_width,
				'created_time' => strtotime($date->dmYtoYmd($created_time)),
				'modified_time' => time(),
				'user_id' => $_SESSION["user_id"]
			);

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
				}
			}

			$db->insert($data);
			$id_query = $db->LastInsertID;

			// Handle location image
			if (isset($_FILES['product_location_img']) && $_FILES['product_location_img']['size'] > 0) {
				$dir_product = ROOT_DIR . DS . 'uploads' . DS . 'product';
				if (!is_dir($dir_product)) {
					mkdir($dir_product, 0755, true);
				}

				$locImgUp = new Upload($_FILES['product_location_img']);
				$locImgUp->file_max_size = $file_max_size;
				if ($locImgUp->uploaded) {
					$stringObj = new StringHelper();
					$loc_name = 'loc_' . getRandomString() . '-' . $id_query . '-' . substr($stringObj->getSlug($name), 0, 50);
					$locImgUp->file_new_name_body = $loc_name;
					$locImgUp->image_resize = true;
					$locImgUp->image_x = 1200;
					$locImgUp->image_ratio_y = true;
					$locImgUp->Process($dir_product);

					if ($locImgUp->processed) {
						$db->table = "article_product";
						$db->condition = "article_product_id = " . $id_query;
						$db->update(array('product_location_img' => $locImgUp->file_dst_name));
					}
					$locImgUp->Clean();
				}
			}

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

			// Update upload_tmp status
			$db->table = "uploads_tmp";
			$data = array('status' => 1);
			$db->condition = "upload_id = " . ($upload_img_id + 0);
			$db->update($data);

			// Finalize Article Product Gallery Manager data
			$db->table = "article_product_gallery_category";
			$db->condition = "article_product_id = 0";
			$db->update(array('article_product_id' => $id_query));

			$db->table = "article_product_gallery_image";
			$db->condition = "article_product_id = 0";
			$db->update(array('article_product_id' => $id_query));

			$db->table = "article_product_gallery_tab";
			$db->condition = "article_product_id = 0";
			$db->update(array('article_product_id' => $id_query));

			loadPageSucces("Đã thêm Sản phẩm thành công.", "?" . TTH_PATH . "=article_product_list&id=" . $article_product_menu_id);
			$OK = true;
		}
	}
} else {
	$upload_img_id = 0;

	// Create main upload_id
	$db->table = "uploads_tmp";
	$data = array('created_time' => time());
	$db->insert($data);
	$upload_img_id = $db->LastInsertID;

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

	// Product specific
	$product_status = "";
	$sale_status = "available";
	$product_area = "";
	$product_price_text = "";
	$product_type_text = "";
	$product_location_img = "";
	$product_video = "";
	$product_video_type = "";
	$product_video_enabled = 0;
	$contact_button_text = "";
	$article_tags = "";

	// Đặc điểm BĐS - khởi tạo rỗng
	$price = "";
	$price_per_m2 = "";
	$area = "";
	$area_land = "";
	$area_construction = "";
	$bedrooms = "";
	$bathrooms = "";
	$block = "";
	$floor = "";
	$direction = "";
	$direction_balcony = "";
	$view_type = "";
	$legal_status = "";
	$furniture_status = "";

	// Tiện ích đi kèm - mặc định
	$has_elevator = 0;
	$has_parking = 0;
	$parking_car = 0;
	$parking_motor = 0;
	$has_pool = 0;
	$has_garden = 0;
	$has_rooftop = 0;
	$balconies = 0;

	// Trường bổ sung - mặc định
	$product_code = "";
	$area_carpet = "";
	$price_negotiable = 0;
	$sale = "";
	$discount_percent = "";
	$discount_text = "";
	$booking_fee = "";
	$handover_standard = "";

	// Đất nền / Nhà phố / Biệt thự - mặc định
	$frontage = "";
	$depth = "";
	$floors_count = "";
	$alley_width = "";

	// Liên kết dự án - mặc định
	$article_project_id = 0;
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
		// Trường bổ sung
		'product_code' => $product_code,
		'area_carpet' => $area_carpet,
		'price_negotiable' => $price_negotiable,
		'sale' => $sale,
		'discount_percent' => $discount_percent,
		'discount_text' => $discount_text,
		'booking_fee' => $booking_fee,
		'handover_standard' => $handover_standard,
		// Đất nền / Nhà phố / Biệt thự
		'frontage' => $frontage,
		'depth' => $depth,
		'floors_count' => $floors_count,
		'alley_width' => $alley_width
	);
	articleProductStandalone("?" . TTH_PATH . "=article_product_add", "add", 0, $article_product_menu_id, $name, $title, $description, $keywords, $img, $img_note, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error, $productData);
}
?>