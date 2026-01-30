<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//

if(isset($_POST['idDel'])){
	$idDel = implode(',',$_POST['idDel']);
	$db->table = "others";
	$db->condition = "others_id IN (".$idDel.")";
	$db->delete();
	loadPageSucces("Đã xóa dữ liệu thành công.","?".TTH_PATH."=others_list&id=".$_POST['list_id']);
}

$others_menu_id =  isset($_GET['id']) ? $_GET['id']+0 : 0;
$category_id_core = 0;
$db->table = "others_menu";
$db->condition = "others_menu_id = ". $others_menu_id;
$rows = $db->select();
foreach($rows as $row){
	$category_id_core = $row['category_id'];
}
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=others_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=others_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=others_manager"><i class="fa fa-puzzle-piece"></i> Dữ liệu khác</a>
		</li>
		<li>
			<i class="fa fa-list"></i> <?=getNameMenu($others_menu_id, 'others')?>
		</li>
		<a class="btn-add-new" href="?<?=TTH_PATH?>=others_add&id=<?=$others_menu_id?>">Thêm mục</a>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdminTwo("others_list;".$category_id_core)?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=others_list" method="post" id="deleteArt">
					<input type="hidden" name="list_id" value="<?=$others_menu_id?>" />
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th>STT</th>
							<th>Tiêu đề</th>
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

						$db->table = "others";
						$db->condition = "others_menu_id = ".$others_menu_id;
						$db->order = "sort ASC";
						$rows = $db->select();
						$countList = 0;
						$countList = $db->RowCount;

						$totalpages = 0;
						$perpage = 60;
						$total = $db->RowCount;
						if($total%$perpage==0) $totalpages=$total/$perpage;
						else $totalpages = floor($total/$perpage)+1;
						if(isset($_GET["page"])) $page=$_GET["page"]+0;
						else $page=1;
						$start=($page-1)*$perpage;
						$i=0+($page-1)*$perpage;

						$db->table = "others";
						$db->condition = "others_menu_id = ".$others_menu_id;
						$db->order = "sort ASC";
						$db->limit = $start.','.$perpage;
						$rows = $db->select();

						foreach($rows as $row) {
							$i++;
							?>
							<tr>
								<td align="center"><?=$i?></td>
								<td><?=stripslashes($row['name'])?></td>
								<?php
								//-----------Kiểm tra phân quyền ---------------------------------------
								if(in_array("others_edit;".$category_id_core,$corePrivilegeSlug)) {
								?>
								<td align="center">
									<?=showSort("sort_".$row["others_id"]."", $countList,$row["sort"], "90%", 0, $row["others_id"] ,'others', 1);?>
								</td>		
								<td align="center">
									<?=($row["is_active"]+0==0)?
										'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["others_id"].', \'is_active\', \'others\');" rel="1">0</div>'
										:
										'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["others_id"].', \'is_active\', \'others\');" rel="0">1</div>'
									?>
								</td>
								<td align="center">
									<?=($row["hot"]+0==0)?
										'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="edit_status($(this), '.$row["others_id"].', \'hot\', \'others\');" rel="1">0</div>'
										:
										'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="edit_status($(this), '.$row["others_id"].', \'hot\', \'others\');" rel="0">1</div>'
									?>
								</td>
								<?php
								} else {
								?>
								<td align="center">
									<?=showSort("sort_".$row["others_id"]."", $countList,$row["sort"], "90%", 0, $row["others_id"] ,'others', 0);?>
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
								<?php }
								//----------- end if ------------
								?>
								<td align="center"><?=$date->vnDateTime($row['modified_time'])?></td>
								<td align="center"><?=getUserName($row['user_id']);?></td>
								<td class="details-control" align="center">
									<div class="checkbox">
										<?php
										$href = ($category_id_core==66) ? '?'.TTH_PATH.'=video_edit&id='.$row['others_id'] : '?'.TTH_PATH.'=others_edit&id='.$row['others_id'];
										?>
										<a href="<?=$href?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a> &nbsp;
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkboxArt" name="idDel[]" value="<?=$row['others_id']?>">
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
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages, '?'.TTH_PATH.'=others_list&id='.$others_menu_id.'&page=')?></div>
						<div class="col-sm-6" align="right" style="padding: 7px 0;">
							<label class="radio-inline"><input type="checkbox" id="selecctall"  data-toggle="tooltip" data-placement="top" title="Chọn xóa tất cả" ></label>
							<input type="button" class="btn btn-primary btn-xs <?=in_array("others_del;".$category_id_core,$corePrivilegeSlug)? "confirmManager" : "alertManager"?> " value="Xóa" name="deleteArt">
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
			"order": [ 0, "asc" ],
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