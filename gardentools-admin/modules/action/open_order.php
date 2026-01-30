<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
if(isset($_POST['id'])) {
	$id = $_POST['id'];
	$content = '';
	$date = new DateClass();
	$title = '';

	$db->table = "order";
	$db->condition = "order_id = $id";
	$db->order = "";
	$db->limit = 1;
	$rows = $db->select();
	if($db->RowCount > 0) {
		foreach($rows as $row) {
			if($row['icon']=='fa-car') $title = '[ĐẶT XE]: ' . stripslashes($row['name']) . ' (' . $date->vnDateTime($row['created_time']) . ')';
			elseif($row['icon']=='fa-building')  $title = '[ĐẶT PHÒNG]: ' . stripslashes($row['name']) . ' (' . $date->vnDateTime($row['created_time']) . ')';
			elseif($row['icon']=='fa-briefcase')  $title = '[ĐẶT TOUR]: ' . stripslashes($row['name']) . ' (' . $date->vnDateTime($row['created_time']) . ')';
			else $title = '[ĐẶT HÀNG]: ' . stripslashes($row['name']) . ' (' . $date->vnDateTime($row['created_time']) . ')';
			$content = '<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title" id="myModalLabel">' . $title .'</h4>
								</div>
								'. stripslashes($row['content']) . '</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-form-primary btn-form" data-dismiss="modal">Đóng</button>
								</div>
							</div>
						</div>';
		}
	}
	echo $content;
}