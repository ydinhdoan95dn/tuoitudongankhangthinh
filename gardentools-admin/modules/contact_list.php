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
			<i class="fa fa-edit"></i> Quản lý nội dung
		</li>
		<li>
			<i class="fa fa-send-o"></i> Liên hệ
		</li>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
if(isset($_POST['idDel'])){

	$idDel = implode(',',$_POST['idDel']);

	$db->table = "contact";
	$db->condition = "contact_id IN (".$idDel.")";
	$db->delete();
	loadPageSucces("Đã thực hiện thao tác Xóa thành công.","?".TTH_PATH."=contact_list");
}
?>
<style>
/* Contact List Optimized Styles */
.contact-source-link {
	color: #337ab7;
	text-decoration: none;
	font-size: 12px;
	max-width: 180px;
	display: inline-block;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	vertical-align: middle;
}
.contact-source-link:hover {
	color: #23527c;
	text-decoration: underline;
}
.contact-source-text {
	color: #aaa;
	font-size: 12px;
	font-style: italic;
}
.contact-name {
	font-weight: 600;
	color: #333;
}
.contact-phone {
	font-family: monospace;
	font-size: 13px;
}
.contact-phone a {
	color: #337ab7;
	text-decoration: none;
}
.contact-phone a:hover {
	text-decoration: underline;
}
.btn-sm-sm {
	padding: 3px 8px;
	font-size: 11px;
}
.status-new {
	background: #f0ad4e !important;
	border-color: #eea236 !important;
	color: #fff !important;
}
.status-viewed {
	background: #5cb85c !important;
	border-color: #4cae4c !important;
	color: #fff !important;
}
</style>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=contact_list" method="post" enctype="multipart/form-data" id="delete">
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th width="40px">STT</th>
							<th>Tên khách hàng</th>
							<th width="120px">Điện thoại</th>
							<th width="200px">Nguồn</th>
							<th width="80px">Trạng thái</th>
							<th width="130px">Ngày gửi</th>
							<th width="100px">Thao tác</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$date = new DateClass();

						$db->table = "contact";
						$db->condition = "";
						$db->order = "created_time DESC";
						$rows = $db->select();

						$totalpages = 0;
						$perpage = 80;
						$total = $db->RowCount;
						if($total%$perpage==0) $totalpages=$total/$perpage;
						else $totalpages = floor($total/$perpage)+1;
						if(isset($_GET["page"])) $page=$_GET["page"]+0;
						else $page=1;
						$start=($page-1)*$perpage;
						$i=0+($page-1)*$perpage;

						$db->table = "contact";
						$db->condition = "";
						$db->order = "created_time DESC";
						$db->limit = $start.','.$perpage;
						$rows = $db->select();

						foreach($rows as $row) {
							$i++;

							// Xử lý page_slug để hiển thị nguồn
							$pageSlug = isset($row['page_slug']) ? trim($row['page_slug']) : '';
							$sourceDisplay = '';

							if(!empty($pageSlug)) {
								// Lấy phần cuối của slug (sau dấu / cuối cùng)
								$slugParts = explode('/', $pageSlug);
								$shortSlug = end($slugParts);
								// Nếu slug dài, hiển thị tối đa 30 ký tự
								if(strlen($shortSlug) > 30) {
									$shortSlug = substr($shortSlug, 0, 27) . '...';
								}
								$sourceDisplay = '<a href="' . HOME_URL . '/' . htmlspecialchars($pageSlug) . '" target="_blank" class="contact-source-link" title="' . htmlspecialchars($pageSlug) . '"><i class="fa fa-external-link"></i> ' . htmlspecialchars($shortSlug) . '</a>';
							} else {
								$sourceDisplay = '<span class="contact-source-text">--</span>';
							}
							?>
							<tr>
								<td align="center"><?=$i?></td>
								<td class="contact-name"><?=stripslashes($row['name'])?></td>
								<td class="contact-phone">
									<a href="tel:<?=preg_replace('/[^0-9]/', '', $row['phone'])?>"><?=stripslashes($row['phone'])?></a>
								</td>
								<td><?=$sourceDisplay?></td>
								<td align="center">
									<?=($row["is_active"]+0==0)?
											'<button type="button" id="_v_'.$row["contact_id"].'" class="btn btn-sm-sm status-viewed" data-toggle="tooltip" data-placement="top" title="Chuyển sang: Chưa xem" onclick="status_view($(this), '.$row["contact_id"].', \'is_active\', \'contact\');" rel="1"><i class="fa fa-check"></i> Đã xem</button>'
											:
											'<button type="button" id="_v_'.$row["contact_id"].'" class="btn btn-sm-sm status-new" data-toggle="tooltip" data-placement="top" title="Chuyển sang: Đã xem" onclick="status_view($(this), '.$row["contact_id"].', \'is_active\', \'contact\');" rel="0"><i class="fa fa-bell"></i> Mới</button>'
									?>
								</td>
								<td align="center"><?=$date->vnDateTime($row['created_time'])?></td>
								<td class="details-control" align="center">
									<span class="btn btn-primary btn-sm-sm" data-toggle="modal" data-target="#_contact" onclick="return open_modal_contact(<?=$row['contact_id']?>);"><i class="fa fa-eye"></i> Xem</span>
									<div class="checkbox" style="display:inline-block; margin-left: 5px;">
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkbox" name="idDel[]" value="<?=$row['contact_id']?>">
										</label>
									</div>
								</td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
					<!-- Modal -->
					<div class="modal fade" id="_contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
					<!-- /.modal -->
					<div class="row">
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages,'?'.TTH_PATH.'=contact_list&page=')?></div>
						<div class="col-sm-6" align="right" style="padding: 7px 0;">
							<label class="radio-inline"><input type="checkbox" id="selecctall"  data-toggle="tooltip" data-placement="top" title="Chọn xóa tất cả" ></label>
							<input type="button" class="btn btn-primary btn-xs confirmManager" value="Xóa" name="delete">
						</div>
					</div>
				</form>
			</div>
			<!-- /.table-responsive -->
		</div>
		<!-- /.panel -->
	</div>
	<!-- /.col-lg-6 -->
</div>
<script>
	$(document).ready(function() {
		$('#dataTablesList').dataTable( {
			"language": {
				"url": "<?=ADMIN_DIR?>/js/plugins/dataTables/de_DE.txt"
			},
			"aoColumnDefs" : [ {
				"bSortable" : false,
				"aTargets" : [ 6, "no-sort" ]
			} ],
			"paging":   false,
			"info":     false,
			"order": [ 0, "asc" ]
		} );

		$('#selecctall').click(function(event) {
			if(this.checked) {
				$('.checkbox').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkbox').each(function() {
					this.checked = false;
				});
			}
		});
	});
	$(".confirmManager").click(function() {
		var element = $(this);
		var action = element.attr("id");
		confirm("Tất cả các dữ liệu liên quan sẽ được xóa và không thể phục hồi.\nBạn có muốn thực hiện không?", function() {
			if(this.data == true) document.getElementById("delete").submit();
		});
	});
</script>