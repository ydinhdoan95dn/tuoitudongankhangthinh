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
			<a href="?<?=TTH_PATH?>=prjname_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=prjname_manager"><i class="fa fa-tag"></i> Dữ liệu tên dự án</a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa mục
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "prjname.php");
if(empty($typeFunc)) $typeFunc = "no";
$prjname_id =  isset($_GET['id']) ? $_GET['id']+0 : $prjname_id+0;
$db->table = "prjname";
$db->condition = "prjname_id = ".$prjname_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=prjname_manager");

$OK = false;
$error = '';
if($typeFunc=='edit'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên mục.</span>';
	else {
		$db->table = "prjname";
		$data = array(
			'name'=>$db->clearText($name),
			'slug'=>$db->clearText($slug),
			'is_active'=>$is_active+0,
			'hot'=>$hot+0,
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->condition = "prjname_id = ".$prjname_id;
		$db->update($data);

		loadPageSucces("Đã chỉnh sửa Mục thành công.","?".TTH_PATH."=prjname_manager");
		$OK = true;
	}
}
else {
	$db->table = "prjname";
	$db->condition = "prjname_id = ".$prjname_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$name			= $row['name'];
		$slug			= $row['slug'];
		$is_active		= $row['is_active']+0;
		$hot			= $row['hot']+0;
	}
}
if(!$OK) prjname("?".TTH_PATH."=prjname_edit", "edit", $prjname_id, $name, $slug, $is_active, $hot, $error);
?>
