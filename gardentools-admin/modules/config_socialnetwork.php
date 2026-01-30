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
			<i class="fa fa-cogs"></i> Cấu hình
		</li>
		<li>
			<i class="fa fa-recycle"></i> Mạng xã hội
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

	$nameConstant = $_POST["name_constant"];
	$countConstant = count($nameConstant);
	$valueConstant = $_POST["value_constant"];
	for($i = 0; $i < $countConstant; $i++) {
		updateConstant($nameConstant[$i],$valueConstant[$i]);
	}

	loadPageSucces("Đã cập nhật thông tin cấu hình thành công.","?".TTH_PATH."=config_socialnetwork");
}
?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-recycle"></i> Mạng xã hội
			</div>
			<div class="panel-body">
				<div class="table-respon">
					<form action="?<?=TTH_PATH?>=config_socialnetwork" method="post">
						<table class="table table-hover" style="width: 70%;">
							<?php
							$db->table = "constant";
							$db->condition = "type = 5";
							$db->order = "sort ASC";
							$db->limit = "";
							$rows = $db->select();

							foreach($rows as $row) {
							?>
							<tr>
								<td width="200px"><label><?=$row['name']?>:</label></td>
								<td>
									<input type="hidden" name="name_constant[]" value="<?=$row['constant']?>" >
									<input class="form-control" type="text" name="value_constant[]" value="<?=stripslashes($row['value'])?>" >
								</td>
							</tr>
							<?php
							}
							?>
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