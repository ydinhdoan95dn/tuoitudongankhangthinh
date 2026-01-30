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
			<i class="fa fa-tag"></i> Dữ liệu tên dự án
		</li>
		<a class="btn-add-new" href="?<?=TTH_PATH?>=prjname_add">Thêm mục</a>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
if(isset($_POST['idDel'])){

	$idDel = implode(',',$_POST['idDel']);

	$db->table = "prjname";
	$db->condition = "prjname_id IN (".$idDel.")";
	$db->delete();
	loadPageSucces("Đã xóa Mục thành công.","?".TTH_PATH."=prjname_manager");
}
?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=prjname_manager" method="post" id="deleteArt">
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th>STT</th>
							<th>Tên dự án</th>
							<th>Sắp xếp</th>
							<th>Trạng thái</th>
							<th>Nổi bật</th>
							<th>Ngày cập nhật</th>
							<th>Người đăng</th>
							<th>Chức năng</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$date = new DateClass();

						$db->table = "prjname";
						$db->condition = "";
						$db->order = "is_active ASC, name ASC";
						$db->limit = "";
						$rows = $db->select();
						$countList = 0;
						$countList = $db->RowCount;

						$totalpages = 0;
						$perpage = 50;
						$total = $db->RowCount;
						if($total%$perpage==0) $totalpages=$total/$perpage;
						else $totalpages = floor($total/$perpage)+1;
						if(isset($_GET["page"])) $page=$_GET["page"]+0;
						else $page=1;
						$start=($page-1)*$perpage;
						$i=0+($page-1)*$perpage;

						$db->table = "prjname";
						$db->condition = "";
						$db->order = "is_active ASC, name ASC";
						$db->limit = $start.','.$perpage;
						$rows = $db->select();
						foreach($rows as $row) {
							$i++;
							?>
							<tr>
								<td align="center"><?=$i?></td>
								<td><?=stripslashes($row['name'])?></td>
								<?php
								if(in_array("prjname_edit",$corePrivilegeSlug)) {
									?>
									<td align="center">
										<?=showSort("sort_".$row["prjname_id"]."", $countList,$row["sort"], "80%", 0, $row["prjname_id"] ,'prjname', 1);?>
									</td>
									<td align="center">
										<?=($row["is_active"]+0==0)?
											'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["prjname_id"].', \'is_active\', \'prjname\');" rel="1">0</div>'
											:
											'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["prjname_id"].', \'is_active\', \'prjname\');" rel="0">1</div>'
										?>
									</td>
									<td align="center">
										<?=($row["hot"]+0==0)?
											'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["prjname_id"].', \'hot\', \'prjname\');" rel="1">0</div>'
											:
											'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["prjname_id"].', \'hot\', \'prjname\');" rel="0">1</div>'
										?>
									</td>
								<?php
								}
								else {
									?>
									<td align="center">
										<?=showSort("sort_".$row["prjname_id"]."", $countList,$row["sort"], "80%", 0, $row["prjname_id"] ,'prjname', 0);?>
									</td>
									<td align="center">
										<?=($row["is_active"]+0==0)?
											'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở">0</div>'
											:
											'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng">1</div>'
										?>
									</td>
									<td align="center">
										<?=($row["hot"]+0==0)?
											'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở">0</div>'
											:
											'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng">1</div>'
										?>
									</td>
								<?php
								}
								?>
								<td align="center"><?=$date->vnDateTime($row['modified_time'])?></td>
								<td align="center"><?=getUserName($row['user_id']);?></td>
								<td class="details-control" align="center">
									<div class="checkbox">
										<a href="?<?=TTH_PATH?>=prjname_edit&id=<?=$row['prjname_id']?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a> &nbsp;
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkboxArt" name="idDel[]" value="<?=$row['prjname_id']?>">
										</label>
									</div>
								</td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
					<div class="row">
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages,'?'.TTH_PATH.'=prjname_manager&page=')?></div>
						<div class="col-sm-6" align="right" style="padding: 7px 0;">
							<label class="radio-inline"><input type="checkbox" id="selecctall"  data-toggle="tooltip" data-placement="top" title="Chọn xóa tất cả" ></label>
							<input type="button" class="btn btn-primary btn-xs  <?=in_array("prjname_del",$corePrivilegeSlug)? "confirmManager" : "alertManager"?> " value="Xóa mục" name="deleteArt">
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
				"aTargets" : [7, "no-sort" ]
			} ],
			"paging":   false,
			"info":     false,
			"order": [3, "asc" ],
			"columns": [
				null,
				null,
				{ "orderDataType": "dom-select" },
				null,
				null,
				null,
				null,
				null
			]
		} );

		$('#selecctall').click(function(event) {
			if(this.checked) {
				$('.checkboxArt').each(function() {
					this.checked = true;
				});
			}else{
				$('.checkboxArt').each(function() {
					this.checked = false;
				});
			}
		});
	});
	$(".confirmManager").click(function() {
		var element = $(this);
		var action = element.attr("id");
		confirm("Tất cả các dữ liệu liên quan sẽ được xóa và không thể phục hồi.\nBạn có muốn thực hiện không?", function() {
			if(this.data == true) document.getElementById("deleteArt").submit();
		});
	});
	$(".alertManager").boxes('alert', 'Bạn không được phân quyền với chức năng này.');
</script>