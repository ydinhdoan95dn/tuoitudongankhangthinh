<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$gallery_menu_id = isset($_GET['id']) ? $_GET['id']+0 : $gallery_menu_id+0;
$db->table = "gallery_menu";
$db->condition = "gallery_menu_id = ".$gallery_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=gallery_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=gallery_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=gallery_manager"><i class="fa fa-newspaper-o"></i> Hình ảnh</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=gallery_list&id=<?=$gallery_menu_id?>"><i class="fa fa-list"></i> <?=getNameMenuGal($gallery_menu_id)?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm video
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "video.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();

$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tiêu đề video.</span>';
	else {
		$handleUploadImg = false;
		$file_max_size = FILE_MAX_SIZE;
		$dir_dest = ROOT_DIR . DS . 'uploads' . DS . 'gallery' . DS;

		preg_match_all("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$link_youtube,$url);
		foreach ( $url as $link ) {
			for($i=0;$i<=count($link);$i++) {
				$y_link = $link[$i];
				preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=[0-9]/)[^&\n]+|(?<=v=)[^&\n]+#", $y_link, $valid);
				if ( (strpos($y_link,"youtube.com") != false) && ($valid != NULL) ) {
					$stringObj = new StringHelper();
					$youtube = $valid[0];
					$txt = $stringObj->getSlug($name);
					$filename = time() . '-' . md5(uniqid()) . '-' . $txt;
					$OK = uploadVideo($youtube, $filename, $dir_dest);

					if($OK) {
						$img = $filename . '.jpg';

						$db->table = "gallery";
						$data = array(
							'gallery_menu_id'=>$gallery_menu_id+0,
							'name'=>$db->clearText($name),
							'title'=>$db->clearText($title),
							'description'=>$db->clearText($description),
							'keywords'=>$db->clearText($keywords),
							'img'=>$db->clearText($img),
							'link'=>$db->clearText($link_youtube),
							'comment'=>$db->clearText($comment),
							'is_active'=>$is_active+0,
							'hot'=>$hot+0,
							'created_time'=>strtotime($date->dmYtoYmd($created_time)),
							'user_id'=>$_SESSION["user_id"]
						);
						$db->insert($data);

						loadPageSucces("Đã thêm Video thành công.","?".TTH_PATH."=gallery_list&id=".$gallery_menu_id);
						$OK = true;
					}
				}
			}
		}
	}
}
else {
	$name			= "";
	$title			= "";
	$description	= "";
	$keywords		= "";
	$img            = "";
	$link_youtube   = "";
	$comment        = "";
	$is_active		= 1;
	$hot			= 0;
	$created_time   = $date->vnDateTime(time());
}
if(!$OK) video("?".TTH_PATH."=video_add", "add", 0, $gallery_menu_id, $name, $title, $description, $keywords, $img, $link_youtube, $comment, $is_active, $hot, $created_time, $error);
?>