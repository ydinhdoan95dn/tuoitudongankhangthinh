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
			<i class="fa fa-database"></i> Cơ sở dữ liệu (CSDL)
		</li>
		<li>
			<i class="fa fa-crosshairs"></i> Cấu hình sao lưu
		</li>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
if(isset($_POST['update'])) {

	function updateConstant ($constant, $value) {
		global $db;
		$db->table = "constant";
		$data =array(
			'value'=>$db->clearText($value)
		);
		$db->condition = "constant = '".$constant."'";
		$db->update($data);
	}

	//updateConstant("backup_auto",$_POST['backup_auto']);
	updateConstant("backup_filetype",$_POST['backup_filetype']);
	updateConstant("backup_filecount",$_POST['backup_filecount']);
	updateConstant("backup_email",$_POST['backup_email']);

	loadPageSucces("Đã cập nhật cấu hình thành công.","?".TTH_PATH."=backup_config");
}
?>
<script type="text/javascript" language="javascript">
	function IsNumberInt(str) {
		for(var i = 0; i < str.length; i++) {
			var temp = str.substring(i, i + 1);
			if(!(temp == "," || temp == "." || (temp >= 0 && temp <=9))) {
				alert("Chỉ được nhập kiểu số nguyên dương vào ô này.");
				return str.substring(0, i);
			}
			if(temp == " " || temp == "," || temp == ".")
				return str.substring(0, i);
		}
		return str;
	}
</script>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-crosshairs"></i> Cấu hình sao lưu
			</div>
			<div class="panel-body">
				<div class="table-respon">
					<form action="?<?=TTH_PATH?>=backup_config" method="post">
						<table class="table table-hover" style="width: 70%;">
							<tr>
								<td width="250px"><label>Tự động sao lưu:</label></td>
								<td>
									<select disabled name="backup_auto" class="form-control">
										<option value="none" <?=(getConstant("backup_auto")=='none')? "selected" : "" ?> >Không</option>
										<option value="day" <?=(getConstant("backup_auto")=='day')? "selected" : "" ?> >Hằng ngày</option>
										<option value="week" <?=(getConstant("backup_auto")=='week')? "selected" : "" ?> >Hằng tuần</option>
										<option value="month" <?=(getConstant("backup_auto")=='month')? "selected" : "" ?> >Hằng tháng</option>
									</select>
								</td>
							</tr>
							<tr>
								<td><label>Định dạng lưu file CSDL:</label></td>
								<td>
									<select name="backup_filetype" class="form-control">
										<option value="sql" <?=(getConstant("backup_filetype")=='sql')? "selected" : "" ?> >.sql</option>
										<option value="sql.gz" <?=(getConstant("backup_filetype")=='sql.gz')? "selected" : "" ?> >.gz</option>
									</select>
								</td>
							</tr>
							<tr>
								<td><label>Số file CSDL lưu lại:</label></td>
								<td><input class="form-control" type="text" maxlength="3" onBlur="if(this.value=='' || this.value==1) {this.value=1;}" onkeyup="this.value = IsNumberInt(this.value);" name="backup_filecount" value="<?=(getConstant("backup_filecount")+0==0) ? 1 : getConstant("backup_filecount") ?>"></td>
							</tr>
							<tr>
								<td><label>Email nhận thông báo và file:</label><br>(để trống khi không muốn gửi)</td>
								<td><input class="form-control" type="email" maxlength="200" name="backup_email" value="<?=getConstant("backup_email")?>"></td>
							</tr>
							<tr>
								<td colspan="2" align="center">
									<button type="submit" name="update" class="btn btn-form-primary btn-form">Đồng ý</button> &nbsp;
									<button type="reset" class="btn btn-form-success btn-form">Làm lại</button> &nbsp;
									<button type="button" class="btn btn-form-info btn-form" onclick="location.href='<?=ADMIN_DIR?>'">Thoát</button>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>