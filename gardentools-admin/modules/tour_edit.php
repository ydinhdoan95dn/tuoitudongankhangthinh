<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$tour_id = isset($_GET['id']) ? $_GET['id']+0 : $tour_id+0;
$db->table = "tour";
$db->condition = "tour_id = ".$tour_id;
$db->order = "";
$rows = $db->select();
foreach($rows as $row) {
	$menu_id    = $row['tour_menu_id'];
}
if($db->RowCount==0) loadPageAdmin("Dữ liệu không tồn tại.","?".TTH_PATH."=tour_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=tour_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=tour_manager"><i class="fa fa-globe"></i> Tour du lịch</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=tour_list&id=<?=$menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($menu_id, 'tour')?></a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa tour
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "tour.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();
$OK = false;
$error = '';
if($typeFunc=='edit'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tiêu đề.</span>';
	elseif(empty($comment)) $error = '<span class="show-error">Vui lòng nhập mô tả.</span>';
	elseif(empty($schedule)) $error = '<span class="show-error">Vui lòng nhập lịch trình.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'tour';
		$file_size = $_FILES['img']['size'];

		if($file_size>0) {
			$imgUp = new Upload($_FILES['img']);

			$imgUp->file_max_size = $file_max_size;
			if ($imgUp->uploaded) {
				$handleUploadImg = true;
				$OK = true;
			}
			else {
				$error = '<span class="show-error">Hình ảnh: '.$imgUp->error.'</span>';
			}
		}
		else {
			$handleUploadImg = false;
			$OK = true;
		}
		if(isset($del_img)) {
			$handleUploadImg = false;

			if(glob($dir_dest.'*'.$img)) array_map("unlink", glob($dir_dest.'*'.$img));

			$db->table = "tour";
			$data = array(
				'img'=>'no'
			);
			$db->condition = "tour_id = ".$tour_id;
			$db->update($data);
		}

		if($OK) {
			$id_query = 0;
			$db->table = "tour";
			$data = array(
				'tour_menu_id'=>$tour_menu_id+0,
				'name'=>$db->clearText($name),
				'img_note'=>$db->clearText($img_note),
				'comment'=>$db->clearText($comment),
				'tour_keys'=>$db->clearText($tour_keys),
				'price'=>formatNumberToInt($price),
				'date_schedule'=>$db->clearText(mb_convert_case($date_schedule, MB_CASE_LOWER, "UTF-8")),
				'date_departure'=>strtotime($date->dmYtoYmd($date_departure)),
				'means'=>$db->clearText($means),
				'tour_type'=>$db->clearText($tour_type),
				'destination'=>$db->clearText($destination),
				'sale'=>formatNumberToInt($sale),
				'schedule'=>$db->clearText($schedule),
				'price_list_service'=>$db->clearText($price_list_service),
				'upload_id'=>$upload_img_id+0,
				'maps'=>$db->clearText($maps),
				'video'=>$db->clearText($video),
				'is_active'=>$is_active+0,
				'hot'=>$hot+0,
				'pin'=>$pin+0,
				'title'=>$db->clearText($title),
				'description'=>$db->clearText($description),
				'keywords'=>$db->clearText($keywords),
				'created_time'=>strtotime($date->dmYtoYmd($created_time)),
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->condition = "tour_id = ".$tour_id;
			$db->update($data);
			$id_query = $tour_id;

			if($handleUploadImg) {
				$stringObj = new StringHelper();

				if(glob($dir_dest . DS .'*'.$img)) array_map("unlink", glob($dir_dest . DS .'*'.$img));

				$name_image = getRandomString() . '-' . $id_query . '-' . substr($stringObj->getSlug($name),0,120);
				$imgUp->file_new_name_body      = $name_image;
				$imgUp->image_resize            = true;
				$imgUp->image_ratio_crop        = true;
				$imgUp->image_y                 = 256;
				$imgUp->image_x                 = 490;
				$imgUp->Process($dir_dest);

				if($imgUp->processed) {
					$name_img = $imgUp->file_dst_name;
					$db->table = "tour";
					$data = array(
						'img'=>$db->clearText($name_img)
					);
					$db->condition = "tour_id = ".$id_query;
					$db->update($data);
				}

				$imgUp->file_new_name_body      = 'tour-' . $name_image;
				$imgUp->image_resize            = true;
				$imgUp->image_ratio_crop        = true;
				$imgUp->image_y                 = 292;
				$imgUp->image_x                 = 380;
				$imgUp->Process($dir_dest);

				$imgUp-> Clean();
			}

			loadPageSucces("Đã chỉnh sửa dữ liệu thành công.","?".TTH_PATH."=tour_list&id=".$tour_menu_id);
			$OK = true;
		}
	}
}
else {
	$db->table = "tour";
	$db->condition = "tour_id = ".$tour_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$tour_menu_id       = $row['tour_menu_id']+0;
		$name			    = $row['name'];
		$img                = $row['img'];
		$img_note           = $row['img_note'];
		$comment            = $row['comment'];
		$tour_keys          = $row['tour_keys'];
		$price              = ($row['price']+0==0) ? "" : $row['price']+0;
		$date_schedule      = $row['date_schedule'];
		$date_departure     = ($row['date_departure']+0==0) ? "" : $date->vnDate($row['date_departure']);
		$means              = $row['means'];
		$tour_type          = $row['tour_type'];
		$destination        = $row['destination'];
		$sale               = $row['sale']+0;
		$schedule           = $row['schedule'];
		$price_list_service = $row['price_list_service'];
		$upload_img_id      = $row['upload_id']+0;
		$maps               = $row['maps'];
		$video              = $row['video'];
		$is_active		    = $row['is_active']+0;
		$hot			    = $row['hot']+0;
		$pin                = $row['pin']+0;
		$title			    = $row['title'];
		$description	    = $row['description'];
		$keywords		    = $row['keywords'];
		$created_time       = $date->vnDateTime($row['created_time']);
	}
}
if(!$OK) tour("?".TTH_PATH."=tour_edit", "edit", $tour_id, $tour_menu_id, $name, $img, $img_note, $comment, $tour_keys, $price, $date_schedule, $date_departure, $means, $tour_type, $destination, $sale, $schedule, $price_list_service, $upload_img_id, $maps, $video, $is_active, $hot, $pin, $created_time, $title, $description, $keywords, $error);
?>