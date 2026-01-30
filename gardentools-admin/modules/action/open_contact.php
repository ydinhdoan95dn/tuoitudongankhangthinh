<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
if(isset($_POST['id'])) {
	$id = $_POST['id'];
	$content = '';
	$date = new DateClass();

	$db->table = "contact";
	$db->condition = "contact_id = $id";
	$db->order = "";
	$db->limit = 1;
	$rows = $db->select();
	if($db->RowCount > 0) {
		foreach($rows as $row) {
			$name = stripslashes($row['name']);
			$phone = stripslashes($row['phone']);
			$email = stripslashes($row['email']);
			$address = stripslashes($row['address']);
			$msgContent = stripslashes($row['content']);
			$pageSlug = isset($row['page_slug']) ? trim($row['page_slug']) : '';
			$createdTime = $date->vnDateTime($row['created_time']);
			$ip = $row['ip'];

			// Tạo link nguồn
			$sourceLink = '';
			if(!empty($pageSlug)) {
				$sourceLink = '<a href="' . HOME_URL . '/' . htmlspecialchars($pageSlug) . '" target="_blank" style="color:#337ab7;">' . htmlspecialchars($pageSlug) . ' <i class="fa fa-external-link"></i></a>';
			} else {
				$sourceLink = '<span style="color:#999;">--</span>';
			}

			$content = '<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header" style="background:#f5f5f5; border-bottom:2px solid #337ab7;">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title" id="myModalLabel" style="color:#337ab7;"><i class="fa fa-envelope"></i> Chi tiết liên hệ</h4>
								</div>
								<div class="modal-body" style="padding:20px;">
									<table class="table table-bordered" style="margin-bottom:0;">
										<tr>
											<td width="150" style="background:#f9f9f9; font-weight:600;"><i class="fa fa-user"></i> Họ và tên:</td>
											<td style="font-size:16px; font-weight:600; color:#333;">' . $name . '</td>
										</tr>
										<tr>
											<td style="background:#f9f9f9; font-weight:600;"><i class="fa fa-phone"></i> Số điện thoại:</td>
											<td><a href="tel:' . preg_replace('/[^0-9]/', '', $phone) . '" style="color:#337ab7; font-size:15px; font-weight:600;">' . $phone . '</a></td>
										</tr>
										' . (!empty($email) ? '<tr>
											<td style="background:#f9f9f9; font-weight:600;"><i class="fa fa-envelope-o"></i> Email:</td>
											<td><a href="mailto:' . $email . '" style="color:#337ab7;">' . $email . '</a></td>
										</tr>' : '') . '
										' . (!empty($address) ? '<tr>
											<td style="background:#f9f9f9; font-weight:600;"><i class="fa fa-map-marker"></i> Địa chỉ:</td>
											<td>' . $address . '</td>
										</tr>' : '') . '
										<tr>
											<td style="background:#f9f9f9; font-weight:600;"><i class="fa fa-link"></i> Nguồn:</td>
											<td>' . $sourceLink . '</td>
										</tr>
										<tr>
											<td style="background:#f9f9f9; font-weight:600;"><i class="fa fa-clock-o"></i> Thời gian:</td>
											<td>' . $createdTime . '</td>
										</tr>
										' . (!empty($ip) ? '<tr>
											<td style="background:#f9f9f9; font-weight:600;"><i class="fa fa-globe"></i> IP:</td>
											<td style="color:#999; font-size:12px;">' . $ip . '</td>
										</tr>' : '') . '
									</table>
									' . (!empty($msgContent) ? '<div style="margin-top:15px; padding:15px; background:#f9f9f9; border-radius:5px; border-left:3px solid #337ab7;">
										<strong style="color:#666;"><i class="fa fa-comment"></i> Nội dung:</strong>
										<div style="margin-top:10px; color:#333; line-height:1.6;">' . nl2br($msgContent) . '</div>
									</div>' : '') . '
								</div>
								<div class="modal-footer" style="background:#f5f5f5;">
									<a href="tel:' . preg_replace('/[^0-9]/', '', $phone) . '" class="btn btn-success"><i class="fa fa-phone"></i> Gọi ngay</a>
									<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Đóng</button>
								</div>
							</div>
						</div>';
		}
	}
	echo $content;
}