<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
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
			<i class="fa fa-plus-square-o"></i> Thêm mục
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "others_menu.php");
if(empty($typeFunc)) $typeFunc = "no";
$category_id =  isset($_GET['id_cat']) ? $_GET['id_cat']+0 : $category_id+0;
$db->table = "category";
$db->condition = "category_id = ".$category_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TH_PATH."=others_manager");
$others_menu_id = isset($_GET['id_art']) ? $_GET['id_art']+0 : 0;
$db->table = "others_menu";
$db->condition = "others_menu_id = ".$others_menu_id;
$rows = $db->select();
if($db->RowCount==0 && $others_menu_id!=0) loadPageAdmin("Mục không tồn tại.","?".TH_PATH."=others_manager");

$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên mục.</span>';
	else {
		$db->table = "others_menu";
		$data = array(
			'category_id'=>$category_id+0,
			'name'=>$db->clearText($name),
			'parent'=>$parent+0,
			'sort'=>sortAcs($category_id,$parent)+1,
			'is_active'=>$is_active+0,
			'hot'=>$hot+0,
			'created_time'=>time(),
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->insert($data);
		loadPageSucces("Đã thêm Mục thành công.","?".TTH_PATH."=others_manager");
		$OK = true;
	}
}
else {
	$name			= "";
	$parent			= isset($_GET['id_art']) ? $_GET['id_art']+0 : 0;
	$is_active		= 1;
	$hot			= 0;
	$img            = "";
}
if(!$OK) othersMenu("?".TTH_PATH."=others_menu_add", "add", 0, $category_id, $name, $parent, $is_active, $hot, $error);
?>
