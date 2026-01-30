<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
?>
<?=dashboardCoreAdmin(); ?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=core_role"><i class="fa fa-dashboard"></i> Quản trị hệ thống</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=core_role"><i class="fa fa-group"></i> Nhóm quản trị</a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm nhóm
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "core_role.php");
if(empty($typeFunc)) $typeFunc = "no";

$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập Tên nhóm</span>';
	else {
		$db->table = "core_role";
		$data = array(
			'name'=>$db->clearText($name),
			'comment'=>$db->clearText($comment),
			'is_active'=>$is_active+0,
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->insert($data);
		loadPageSucces("Đã thêm Nhóm thành công.","?".TTH_PATH."=core_role");
		$OK = true;
	}
}
else {
	$name			= "";
	$comment        = "";
	$is_active		= 1;
}
if(!$OK) grAdmin("?".TTH_PATH."=core_role_add", "add", 0, $name, $comment, $is_active, $error);
?>