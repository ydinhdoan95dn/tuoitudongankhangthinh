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
			<i class="fa fa-send-o"></i> Đánh giá
		</li>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
if(isset($_POST['idDel'])){

	$idDel = implode(',',$_POST['idDel']);

	$db->table = "comment";
	$db->condition = "comment_id IN (".$idDel.")";
	$db->delete();
	loadPageSucces("Đã thực hiện thao tác Xóa thành công.","?".TTH_PATH."=comment_list");
}
?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=comment_list" method="post" enctype="multipart/form-data" id="delete">
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th width="50px">STT</th>
							<th>Tên khách hàng</th>
							<th>Nhận xét</th>
							<th>Bình chọn</th>
							<th>Sản phẩm</th>
							<th>Menu</th>
							<th>Trạng thái</th>
							<th>Ngày gửi</th>
							<th>Chọn</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$date = new DateClass();

						$db->table = "comment";
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

						$db->table = "comment";
						$db->condition = "";
						$db->order = "created_time DESC";
						$db->limit = $start.','.$perpage;
						$rows = $db->select();

						foreach($rows as $row) {
							$i++;
							?>
							<tr>
								<td align="center"><?=$i?></td>
								<td><?=stripslashes($row['name'])?></td>
								<td><?=stripslashes($row['content'])?></td>
								<td align="center"><?=stripslashes($row['ratting'])?> Sao</td>
								<td align="center"><a target="_blank"><?=getNameProduct($row['product_id'])?></a></td>
								<td align="center"><?=getNameMenuPro(getMenuProduct($row['product_id']))?></td>
								<?php
								if(in_array("comment_edit",$corePrivilegeSlug)) {
									?>
									<td align="center">
										<?php echo ($row["is_active"]+0==0)?
											'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["comment_id"].', \'is_active\', \'comment\');" rel="1">0</div>'
											:
											'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["comment_id"].', \'is_active\', \'comment\');" rel="0">1</div>'
										?>
									</td>
									<?php
								}
								else {
									?>
									<td align="center">
										<?php echo ($row["is_active"]+0==0)?
											'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở">0</div>'
											:
											'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng">1</div>'
										?>
									</td>
									<?php
								}
								?>
								<td align="center"><?=$date->vnDateTime($row['created_time'])?></td>
								<td class="details-control" align="center">
									<div class="checkbox">
										<a href="?<?=TTH_PATH?>=comment_edit&id=<?=$row['comment_id']?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a>&nbsp;&nbsp;
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkbox" name="idDel[]" value="<?=$row['comment_id']?>">
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
					<div class="modal fade" id="_comment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
					<!-- /.modal -->
					<div class="row">
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages,'?'.TTH_PATH.'=comment_list&page=')?></div>
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
				"aTargets" : [ 8, "no-sort" ]
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