<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//

if(isset($_POST['idDel'])){
	$dir_dest = ROOT_DIR . DS .'uploads';

	$upload_id = array();
	$idDel = implode(',',$_POST['idDel']);
	$db->table = "bds_business";
	$db->condition = "bds_business_id IN (".$idDel.")";
	$db->order = "";
	$rows = $db->select();
	$i = 0;
	foreach($rows as $row) {
		$upload_id[$i] = $row['upload_id'];
		$list_img = "";
		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->order = "";
		$rows = $db->select();
		foreach ($rows as $row){
			$list_img = $row['list_img'];
		}
		$img = explode(";",$list_img);
		if(count($img)>0) {
			for($j=0;$j<count($img);$j++) {
				if($img[$j] != ""){
					$mask = $dir_dest . DS . "photos" . DS . '*'.$img[$j];
					if (glob($mask))
						array_map("unlink", glob($mask));
				}
			}
		}
		$i++;
	}

	$upload_id = implode(',',$upload_id);
	$db->table = "uploads_tmp";
	$db->condition = "upload_id IN (".$upload_id.")";
	$db->delete();

	$db->table = "bds_business";
	$db->condition = "bds_business_id IN (".$idDel.")";
	$db->delete();
	loadPageSucces("Đã xóa tin rao thành công.","?".TTH_PATH."=bds_business_list&id=".$_POST['list_id']);
}

$bds_business_menu_id =  isset($_GET['id']) ? $_GET['id']+0 : 0;
$category_id_core = 0;
$db->table = "bds_business_menu";
$db->condition = "bds_business_menu_id = ".$bds_business_menu_id;
$rows = $db->select();
foreach($rows as $row){
	$category_id_core = $row['category_id'];
}
if($db->RowCount==0) loadPageAdmin("Nhóm không tồn tại.","?".TTH_PATH."=bds_business_manager");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=bds_business_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=bds_business_manager"><i class="fa fa-money"></i> BĐS Kinh doanh</a>
		</li>
		<li>
			<i class="fa fa-list"></i> <?=getNameMenu($bds_business_menu_id, 'bds_business')?>
		</li>
		<a class="btn-add-new" href="?<?=TTH_PATH?>=bds_business_add&id=<?=$bds_business_menu_id?>">Thêm tin mới</a>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdminTwo("bds_business_list;".$category_id_core)?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<form action="?<?=TTH_PATH?>=bds_business_list" method="post" id="delete">
					<input type="hidden" name="list_id" value="<?=$bds_business_menu_id?>" />
					<table class="table display table-manager" cellspacing="0" cellpadding="0" id="dataTablesList">
						<thead>
						<tr>
							<th>STT</th>
							<th width="38%">Tiêu đề tin rao</th>
							<th>Trạng thái</th>
							<th>Hiển thị</th>
							<th>Ngày đăng</th>
							<th>Người đăng</th>
							<th>Chức năng</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$date = new DateClass();

						$db->table = "bds_business";
						$db->condition = "bds_business_menu_id = ".$bds_business_menu_id;
						$db->order = "created_time DESC";
						$rows = $db->select();

						$totalpages = 0;
						$perpage = 40;
						$total = $db->RowCount;
						if($total%$perpage==0) $totalpages=$total/$perpage;
						else $totalpages = floor($total/$perpage)+1;
						if(isset($_GET["page"])) $page=$_GET["page"]+0;
						else $page=1;
						$start=($page-1)*$perpage;
						$i=0+($page-1)*$perpage;

						$db->table = "bds_business";
						$db->condition = "bds_business_menu_id = ".$bds_business_menu_id;
						$db->order = "created_time DESC";
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
								if(in_array("bds_business_edit;".$category_id_core,$corePrivilegeSlug)) {
								?>
								<td align="center">
									<?php
									if($row['status']+0 == 1) {
										echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Chưa giao dịch" class="btn btn-success btn-sm-sm" onclick="editStatus('.$row["bds_business_id"].',0,\'transaction\',\'bds_business\');">Đã giao dịch</button>';
									} else {
										if(($row['expiration_time']+0) < time()) {
											echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Đã giao dịch" class="btn btn-danger btn-sm-sm" onclick="editStatus('.$row["bds_business_id"].',1,\'transaction\',\'bds_business\');">Hết hiệu lực</button>';
										} else if((($row['expiration_time']+0) - time()) < (7*24*3600)) {
											echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Đã giao dịch" class="btn btn-warning btn-sm-sm" onclick="editStatus('.$row["bds_business_id"].',1,\'transaction\',\'bds_business\');">Sắp hết hiệu lực</button>';
										} else {
											echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Đã giao dịch" class="btn btn-primary btn-sm-sm" onclick="editStatus('.$row["bds_business_id"].',1,\'transaction\',\'bds_business\');">Đang giao dịch</button>';
										}
									}
									?>
								</td>
								<td align="center">
									<?=($row["is_active"]+0==0)?
										'<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="editStatus('.$row["bds_business_id"].',1,\'editstatus\',\'bds_business\')"></div>'
										:
										'<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="editStatus('.$row["bds_business_id"].',0,\'editstatus\',\'bds_business\')"></div>'
									?>
								</td>
								<?php
								} else {
								?>
								<td align="center">
									<?php
									if($row['status']+0 == 1) {
										echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Chưa giao dịch" class="btn btn-success btn-sm-sm alertManager">Đã giao dịch</button>';
									} else {
										if(($row['expiration_time']+0) < time()) {
											echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Đã giao dịch" class="btn btn-danger btn-sm-sm alertManager">Hết hiệu lực</button>';
										} else if((($row['expiration_time']+0) - time()) < (7*24*3600)) {
											echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Đã giao dịch" class="btn btn-warning btn-sm-sm alertManager">Sắp hết hiệu lực</button>';
										} else {
											echo '<button type="button" data-toggle="tooltip" data-placement="top" title="Đã giao dịch" class="btn btn-primary btn-sm-sm alertManager">Đang giao dịch</button>';
										}
									}
									?>
								</td>
								<td align="center">
									<?=($row["is_active"]+0==0)?
										'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở"></div>'
										:
										'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
									?>
								</td>
								<?php }
								//----------- end if ------------
								?>
								<td align="center"><?=$date->vnOther($row['created_time'],TTH_DATETIME_FORMAT)?></td>
								<td align="center"><?=getUserName($row['user_id']);?></td>
								<td class="details-control" align="center">
									<div class="checkbox">
										<a href="?<?=TTH_PATH?>=bds_business_edit&id=<?=$row['bds_business_id']?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a> &nbsp;
										<label class="checkbox-inline">
											<input type="checkbox" data-toggle="tooltip" data-placement="top" title="Xóa" class="checkbox" name="idDel[]" value="<?=$row['bds_business_id']?>">
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
						<div class="col-sm-6"><?=showPageNavigation($page, $totalpages, '?'.TTH_PATH.'=bds_business_list&id='.$bds_business_menu_id.'&page=')?></div>
						<div class="col-sm-6" align="right" style="padding: 7px 0;">
							<label class="radio-inline"><input type="checkbox" id="selecctall"  data-toggle="tooltip" data-placement="top" title="Chọn xóa tất cả" ></label>
							<input type="button" class="btn btn-primary btn-xs <?=in_array("bds_business_del;".$category_id_core,$corePrivilegeSlug)? "confirmManager" : "alertManager"?> " value="Xóa" name="delete">
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
				"aTargets" : [ 2,3,6, "no-sort" ]
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
	$(".alertManager").boxes('alert', 'Bạn không được phân quyền với chức năng này.');
</script>