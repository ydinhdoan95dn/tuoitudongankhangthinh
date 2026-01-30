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
			<i class="fa fa-globe"></i> Cấu hình chung
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

	loadPageSucces("Đã cập nhật thông tin cấu hình thành công.","?".TTH_PATH."=config_general");
}
?>
<script type="text/javascript">
	var editedField;
	function BrowseFile(field) {
		editedField = field ;
		var finder = new CKFinder();
		finder.selectActionFunction = SetFileField;
		finder.popup();
	}
	function SetFileField(fileUrl) {
		document.getElementById(editedField).value = fileUrl;
	}
</script>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-globe"></i> Cấu hình chung
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<form action="?<?=TTH_PATH?>=config_general" method="post">
						<table class="table table-hover">
							<?php
							$db->table = "constant";
							$db->condition = "type = 0";
							$db->order = "sort ASC";
							$db->limit = "";
							$rows = $db->select();

							foreach($rows as $row) {
							?>
							<tr>
								<td width="200px" class="ver-top"><label><?=$row['name']?>:</label></td>
								<td>
									<input type="hidden" name="name_constant[]" value="<?=$row['constant']?>" >
									<?php
									if($row['constant']=='file_logo' || $row['constant']=='image_thumbnailUrl') {
									?>
										<div class="input-group ">
											<input class="form-control" type="text" name="value_constant[]" id="_<?php echo $row['constant'];?>" value="<?=stripslashes($row['value'])?>">
											<div class="input-group-btn">
												<button  class="btn btn-primary" type="button" name="<?php echo $row['constant'];?>" onclick="BrowseFile('_<?php echo $row['constant'];?>');"><i class="glyphicon glyphicon-folder-open"></i> &nbsp;Chọn tệp...</button>
											</div>
										</div>
									<?php
									}
									else if($row['constant']=='error_page') {
									?>
										<textarea class="form-control" rows="4" style="resize: none;" name="value_constant[]" id="<?=$row['constant']?>" ><?=stripslashes($row['value'])?></textarea>
									<?php
									}
									else if($row['constant']=='help_address' || $row['constant']=='help_icon' || $row['constant']=='keywords') {
									?>
										<input class="form-control" type="text" name="value_constant[]" data-role="tagsinput" value="<?=stripslashes($row['value'])?>" >
									<?php
										if($row['constant']=='help_icon') echo '<p><a href="http://fontawesome.io/icons/" target="_blank">Font Awesome</a></p>';
									}
									else {
									?>
										<input class="form-control" type="text" name="value_constant[]" value="<?=stripslashes($row['value'])?>" >
									<?php
									}
									?>
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
<script>
	CKEDITOR.replace( 'error_page', {
		height: 70,
		toolbar: [
			[ 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],
			[ 'FontSize', 'TextColor', 'BGColor' ]
		]
	});
</script>