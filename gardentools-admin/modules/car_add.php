<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$car_menu_id = isset($_GET['id']) ? $_GET['id']+0 : $car_menu_id+0;
$db->table = "car_menu";
$db->condition = "car_menu_id = ".$car_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=car_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=car_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=car_manager"><i class="fa fa-car"></i> Thuê xe</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=car_list&id=<?=$car_menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($car_menu_id, 'car')?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm xe
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "car.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();

$OK = false;
$error = '';
if($typeFunc=='add'){
	$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';
	$content = (isset($_POST['content'])) ? $_POST['content'] : '';
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tiêu đề.</span>';
	elseif(empty($comment)) $error = '<span class="show-error">Vui lòng nhập mô tả</span>';
	elseif(empty($content)) $error = '<span class="show-error">Vui lòng nhập nội dung chi tiết.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'car';
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
                $OK = false;
			}
		}
		else {
			$handleUploadImg = false;
			$OK = true;
		}

		if($OK) {
			$id_query = 0;
			$db->table = "car";
			$data = array(
				'car_menu_id'=>$car_menu_id+0,
				'name'=>$db->clearText($name),
				'title'=>$db->clearText($title),
				'description'=>$db->clearText($description),
				'keywords'=>$db->clearText($keywords),
				'img_note'=>$db->clearText($img_note),
				'model'=>$db->clearText($model),
				'year'=>$db->clearText($year),
				'seat'=>$db->clearText(mb_convert_case($seat, MB_CASE_LOWER, "UTF-8")),
				'seat_sort'=>getNumerics($seat),
				'color'=>$db->clearText($color),
				'price'=>formatNumberToInt($price),
				'sale'=>formatNumberToInt($sale),
				'upload_id'=>$upload_img_id+0,
				'comment'=>$db->clearText($comment),
				'content'=>$db->clearText($content),
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
				$imgUp->file_new_name_body    = 'full-' . $name_image;
				$imgUp->Process($dir_dest);

				$imgUp->file_new_name_body    = $name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_y               = 256;
				$imgUp->image_x               = 490;
				$imgUp->Process($dir_dest);

				if($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "car";
					$data = array(
						'img'=>$db->clearText($name_img)
					);
					$db->condition = "car_id = ".$id_query;
					$db->update($data);
				}
                else {
                    loadPageAdmin("Lỗi tải hình: ".$imgUp->error,"?".TTH_PATH."=car_list&id=".$car_menu_id);
                }

				$imgUp->file_new_name_body    = 'car-' . $name_image;
				$imgUp->image_resize          = true;
				$imgUp->image_ratio_crop      = true;
				$imgUp->image_y               = 258;
				$imgUp->image_x               = 380;
				$imgUp->Process($dir_dest);

				$imgUp-> Clean();
			}

			$db->table = "uploads_tmp";
			$data = array(
					'status'=>1
			);
			$db->condition = "upload_id = ".($upload_img_id+0);
			$db->update($data);

			loadPageSucces("Đã thêm xe thành công.","?".TTH_PATH."=car_list&id=".$car_menu_id);
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
	$title			= "";
	$description	= "";
	$keywords		= "";
	$img            = "";
	$img_note       = "";
	$model          = "";
	$year           = "";
	$seat           = "";
	$color          = "";
	$price          = "";
	$sale           = 0;
	$comment        = "";
	$content        = "";
	$is_active		= 1;
	$hot			= 0;
	$created_time   = $date->vnDateTime(time());
}
if(!$OK) car("?".TTH_PATH."=car_add", "add", 0, $car_menu_id, $name, $title, $description, $keywords, $img, $img_note, $model, $year, $seat, $color, $price, $sale, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error);
?>