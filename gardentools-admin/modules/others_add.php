<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$others_menu_id = isset($_GET['id']) ? $_GET['id']+0 : $others_menu_id+0;
$db->table = "others_menu";
$db->condition = "others_menu_id = ".$others_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=others_manager");
$category_id = 0;
foreach($rows as $row) {
	$category_id =	$row["category_id"]+0;
} 
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=others_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=others_manager"><i class="fa fa-puzzle-piece"></i> Dữ liệu khác</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=others_list&id=<?=$others_menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($others_menu_id, 'others')?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm mục
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "others.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();
$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tiêu đề.</span>';
	else {
		$id_query = 0;
		$db->table = "others";
		$data = array(
			'others_menu_id'=>$others_menu_id+0,
			'name'=>$db->clearText($name),
			'detail'=>$db->clearText($detail),
			'sort'=>sortAcs($others_menu_id)+1,
			'is_active'=>$is_active+0,
			'hot'=>$hot+0,
			'created_time'=>time(),
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->insert($data);
		loadPageSucces("Đã thêm Mục thành công.","?".TTH_PATH."=others_list&id=".$others_menu_id);
	}
}
else {
	$name			= "";
	$detail			= "";
	$is_active		= 1;
	$hot			= 0;
}
if(!$OK) others("?".TTH_PATH."=others_add", "add", 0, $others_menu_id, $name, $is_active, $hot, $error, $detail);
?>