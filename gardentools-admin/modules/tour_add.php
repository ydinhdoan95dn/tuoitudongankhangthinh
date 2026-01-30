<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$tour_menu_id = isset($_GET['id']) ? $_GET['id']+0 : $tour_menu_id+0;
$db->table = "tour_menu";
$db->condition = "tour_menu_id = ".$tour_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=tour_manager");
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
			<a href="?<?=TTH_PATH?>=tour_list&id=<?=$tour_menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($tour_menu_id, 'tour')?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm tour
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
if($typeFunc=='add'){
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
				'user_id'=>$_SESSION["user_id"]
			);
			$db->insert($data);
			$id_query = $db->LastInsertID;

			if($handleUploadImg) {
				$stringObj = new StringHelper();
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

			$db->table = "uploads_tmp";
			$data = array(
				'status'=>1
			);
			$db->condition = "upload_id = ".($upload_img_id+0);
			$db->update($data);

			loadPageSucces("Đã thêm dữ liệu thành công.","?".TTH_PATH."=tour_list&id=".$tour_menu_id);
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

	$name			    = "";
	$img                = "";
	$img_note           = "";
	$comment            = "";
	$tour_keys          = "";
	$price              = "";
	$date_schedule      = "";
	$date_departure     = "";
	$means              = "";
	$tour_type          = "";
	$destination        = "";
	$sale               = 0;
	$schedule           = "";
	$price_list_service = "";
	$maps               = "";
	$video              = "";
	$is_active		    = 1;
	$hot			    = 0;
	$pin                = 0;
	$title			    = "";
	$description	    = "";
	$keywords		    = "";
	$created_time       = $date->vnDateTime(time());
}
if(!$OK) tour("?".TTH_PATH."=tour_add", "add", 0, $tour_menu_id, $name, $img, $img_note, $comment, $tour_keys, $price, $date_schedule, $date_departure, $means, $tour_type, $destination, $sale, $schedule, $price_list_service, $upload_img_id, $maps, $video, $is_active, $hot, $pin, $created_time, $title, $description, $keywords, $error);
?>