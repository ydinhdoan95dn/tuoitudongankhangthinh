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
			<i class="fa fa-plus-square-o"></i> Thêm mục
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "prjname.php");
if(empty($typeFunc)) $typeFunc = "no";

$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên mục.</span>';
	else {
		$stringObj = new StringHelper();

		$slug = (empty($slug)) ? $name : $slug;
		$slug = $stringObj->getSlug($slug);

		$db->table = "prjname";
		$data = array(
			'name'=>$db->clearText($name),
			'slug'=>$db->clearText($slug),
			'sort'=>sortAcs()+1,
			'is_active'=>$is_active+0,
			'hot'=>$hot+0,
			'created_time'=>time(),
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->insert($data);

		loadPageSucces("Đã thêm Mục thành công.","?".TTH_PATH."=prjname_manager");
		$OK = true;
	}
}
else {
	$name			= "";
	$slug           = "";
	$is_active		= 1;
	$hot			= 0;
	$img            = "";
}
if(!$OK) prjname("?".TTH_PATH."=prjname_add", "add", 0, $name, $slug, $is_active, $hot, $error);
?>
