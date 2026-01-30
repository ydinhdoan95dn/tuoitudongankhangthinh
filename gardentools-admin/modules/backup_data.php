<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
@set_time_limit(0);
$dir_dest = "cronjobs";
$dir_dest = str_replace("../","",$dir_dest);
//--------------------------------------------
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<i class="fa fa-database"></i> Cơ sở dữ liệu (CSDL)
		</li>
		<li>
			<i class="fa fa-paste"></i> Sao lưu dữ liệu
		</li>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
if(isset($_GET['filename'])) {
	@unlink($dir_dest . DS . $_GET['filename']);
	loadPageSucces("Đã xóa file thành công.","?".TTH_PATH."=backup_data");
}

//---------------------------------------------------------------
if(isset($_POST['backupdatabase'])) {

	loadPageSucces("Đã tiến hành sao lưu thành công.","?".TTH_PATH."=backup_data");
}

$currentdir = getCurrentDir($dir_dest);
rsort($currentdir);
?>
<div class="row">
	<div class="col-lg-12">
		<div style="margin-bottom: 10px; text-align: right;">
				<input class="btn btn-primary" type="button" onclick="return backupDatabase('list_file_backup');" name="backupdatabase" value="Tiến hành sao lưu" >
		</div>
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive" id="list_file_backup">
				<?=showFileBackupData($currentdir, $dir_dest)?>
			</div>
		</div>
		<!-- /.panel -->
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<table class="table table-manager table-striped table-bordered table-hover">
					<caption><i class="fa fa-database fa-fw"></i> Thông tin chung về CSDL</caption>
					<tbody>
						<tr>
							<td>Máy chủ MySQL</td>
							<td><?=$db->hostInfo();?></td>
						</tr>
						<tr>
							<td>Phiên bản máy chủ MySQL</td>
							<td><?=$db->serverInfo();?></td>
						</tr>
						<tr>
							<td>Phiên bản giao thức MySQL</td>
							<td><?=$db->protoInfo();?></td>
						</tr>
						<tr>
							<td>Tên máy chủ MySQL</td>
							<td><?=TTH_DB_HOST?></td>
						</tr>
						<tr>
							<td>Tên CSDL</td>
							<td><?=TTH_DB_NAME?></td>
						</tr>
						<tr>
							<td>Tài khoản truy cập CSDL</td>
							<td><?=TTH_DB_USER?></td>
						</tr>
						<tr>
							<td>Số Table trong CSDL</td>
							<td>
								<?php
								$db->table = "%";
								$db->showtablestatus();
								echo $db->RowCount;
								?>
							</td>
						</tr>
						<tr>
							<td>Bảng mã CSDL</td>
							<td>
								<?php
								$info = array();
								$rows = $db->showDbInfo();
								foreach($rows as $row) {
									$info['db_info']['db_charset'] = $row['db_charset'];
									$info['db_info']['db_collation'] = $row['db_collation'];
									$info['db_info']['db_time_zone'] = $row['db_time_zone'];
								}
								echo $info['db_info']['db_charset'];
								?>
							</td>
						</tr>
						<tr>
							<td>Mã so sánh CSDL</td>
							<td><?=$info['db_info']['db_collation']?></td>
						</tr>
						<tr>
							<td>Múi giờ của CSDL</td>
							<td><?=$info['db_info']['db_time_zone']?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<!-- /.panel -->
		<div class="panel panel-default panel-no-border">
			<div class="table-responsive">
				<table class="table table-manager table-striped table-bordered table-hover">
					<caption><i class="fa fa-table fa-fw"></i> Các Table thuộc CSDL &ldquo;<?=TTH_DB_NAME?>&rdquo;</caption>
					<thead>
					<tr>
						<th>No.</th>
						<th>Name</th>
						<th>Data length</th>
						<th>Max data length</th>
						<th>Rows</th>
						<th>Collation</th>
						<th>Engine</th>
						<th>Auto increment</th>
						<th>Create time</th>
						<th>Update time</th>
					</tr>
					</thead>
					<tbody>
					<?php
					$tablestatus = "";
					$db->table = "%";
					$rows = $db->showtablestatus();
					$stt = 0;
					foreach($rows as $row) {
						$stt++;
					?>
					<tr>
						<td align="center"><?=$stt?></td>
						<td><?=$row['Name']?></td>
						<td align="right"><?=size_format($row['Data_length'])?></td>
						<td align="right"><?=size_format($row['Max_data_length'])?></td>
						<td align="right"><?=$row['Rows']?></td>
						<td align="center"><?=$row['Collation']?></td>
						<td align="center"><?=$row['Engine']?></td>
						<td align="right"><?=$row['Auto_increment']?></td>
						<td align="center"><?=$row['Create_time']?></td>
						<td align="center"><?=$row['Update_time']?></td>
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<!-- /.panel -->
	</div>
</div>