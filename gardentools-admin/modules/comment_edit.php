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
			<a href="?<?=TTH_PATH?>=comment_list"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=comment_list"><i class="fa fa-file-text-o"></i> Phần Đánh giá</a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa trang
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
//
$comment_id = isset($_GET['id']) ? $_GET['id']+0 : $comment_id+0;
$db->table = "comment";
$db->condition = "comment_id = ".$comment_id;
$db->order = "";
$db->select();
if($db->RowCount==0) loadPageAdmin("Trang bổ sung không tồn tại.","?".TTH_PATH."=comment_list");


include_once (_A_TEMPLATES . DS . "comment.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();

$OK = false;
$error = '';
if($typeFunc=='edit'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên.</span>';
	else {
		$db->table = "comment";
		$data = array(
			'name'=>$db->clearText($name),
			'content'=>$db->clearText($content),
			'ratting'=>$db->clearText($ratting),
			'is_active'=>$is_active+0,
			'modified_time'=>time()
		);
		$db->condition = "comment_id = ".$comment_id;
		$db->update($data);
		loadPageSucces("Đã chỉnh sửa Trang thành công.","?".TTH_PATH."=comment_list");
		$OK = true;

	}
}
else {
	$db->table = "comment";
	$db->condition = "comment_id = ".$comment_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$name			    = $row['name'];
		$content            = $row['content'];
		$ratting        	    = $row['ratting'];
		$is_active		    = $row['is_active']+0;
	}
}
if(!$OK) commentPlugin("?".TTH_PATH."=comment_edit", "edit", $comment_id, $name, $content, $ratting, $is_active, $error);
?>