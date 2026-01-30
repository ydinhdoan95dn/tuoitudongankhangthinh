<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
if(isset($_POST['type'])) {
	$type = $_POST['type'] + 0;
	$parent = $_POST['parent'] + 0;
	if($type == 1) {
		$db->table = "location_menu";
		$db->condition = "is_active = 1 and parent = " . $parent . " and category_id = 39";
		$db->order = "sort ASC";
		$db->limit = "";
		$rows = $db->select();
		echo '<option value="" selected="selected">Chọn quận/huyện</option>';
		if($db->RowCount > 0) {
			foreach ($rows as $row) {
				echo '<option value="' . ($row['location_menu_id'] + 0) . '">' . stripslashes($row["name"]) . '</option>';
			}
		}
	} elseif($type == 2){
		$db->table = "location_menu";
		$db->condition = "is_active = 1 and parent = " . $parent . " and category_id = 39";
		$db->order = "sort ASC";
		$db->limit = "";
		$rows = $db->select();
		echo '<option value="" selected="selected">Chọn khu vực</option>';
		if($db->RowCount > 0) {
			foreach ($rows as $row) {
				echo '<option value="' . ($row['location_menu_id'] + 0) . '">' . stripslashes($row["name"]) . '</option>';
			}
		}
	} else {
		echo '<option value="" selected="selected">Chọn khu vực</option>';
	}
}