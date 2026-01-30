<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$car_id = isset($_GET['id']) ? $_GET['id']+0 : $car_id+0;
$db->table = "car";
$db->condition = "car_id = ".$car_id;
$db->order = "";
$rows = $db->select();
foreach($rows as $row) {
	$menu_id    = $row['car_menu_id'];
}
if($db->RowCount==0) loadPageAdmin("Dữ liệu không tồn tại.","?".TTH_PATH."=car_manager");
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
			<a href="?<?=TTH_PATH?>=car_list&id=<?=$menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($menu_id, 'car')?></a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa xe
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
if($typeFunc=='edit'){
	$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';
	$content = (isset($_POST['content'])) ? $_POST['content'] : '';
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập Tiêu đề</span>';
	elseif(empty($comment)) $error = '<span class="show-error">Vui lòng nhập Mô tả</span>';
	elseif(empty($content)) $error = '<span class="show-error">Vui lòng nhập Nội dung chi tiết</span>';
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
		if(isset($del_img)) {
			$handleUploadImg = false;

			if(glob($dir_dest . DS .'*'.$img)) array_map("unlink", glob($dir_dest . DS . '*'.$img));

			$db->table = "car";
			$data = array(
				'img'=>'no'
			);
			$db->condition = "car_id = ".$car_id;
			$db->update($data);
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
				'comment'=>$db->clearText($comment),
				'content'=>$db->clearText($content),
				'is_active'=>$is_active+0,
				'hot'=>$hot+0,
				'created_time'=>strtotime($date->dmYtoYmd($created_time)),
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->condition = "car_id = ".$car_id;
			$db->update($data);
			$id_query = $car_id;

			if($handleUploadImg) {
				$stringObj = new StringHelper();

				if(glob($dir_dest . DS .'*'.$img)) array_map("unlink", glob($dir_dest . DS .'*'.$img));

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

			loadPageSucces("Đã chỉnh sửa xe thành công.","?".TTH_PATH."=car_list&id=".$car_menu_id);
			$OK = true;
		}
	}
}
else {
	$db->table = "car";
	$db->condition = "car_id = ".$car_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$car_menu_id    = $row['car_menu_id']+0;
		$name			    = $row['name'];
		$title			    = $row['title'];
		$description	    = $row['description'];
		$keywords		    = $row['keywords'];
		$img                = $row['img'];
		$img_note           = $row['img_note'];
		$model              = $row['model'];
		$year               = $row['year'];
		$seat               = $row['seat'];
		$color              = $row['color'];
		$price              = $row['price'];
		$sale               = $row['sale']+0;
		$upload_img_id      = $row['upload_id']+0;
		$comment            = $row['comment'];
		$content            = $row['content'];
		$is_active		    = $row['is_active']+0;
		$hot			    = $row['hot']+0;
		$created_time       = $date->vnDateTime($row['created_time']);
	}
}
if(!$OK) car("?".TTH_PATH."=car_edit", "edit", $car_id, $car_menu_id, $name, $title, $description, $keywords, $img, $img_note, $model, $year, $seat, $color, $price, $sale, $comment, $content, $is_active, $hot, $created_time, $upload_img_id, $error);
?>