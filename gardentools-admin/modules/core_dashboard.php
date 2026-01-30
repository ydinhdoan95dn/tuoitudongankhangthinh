<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//

$role_id = isset($_GET['id']) ? $_GET['id']+0 : $role_id+0;
$db->table = "core_role";
$db->condition = "role_id = ".$role_id;
$db->order = "";
$rows = $db->select();
foreach($rows as $row) {
	$name    = stripslashes($row['name']);
}
if($db->RowCount==0) loadPageAdmin("Nhóm không tồn tại.","?".TTH_PATH."=core_role");

include ("includes" . DS . "function" . DS . "CoreDashboard.php");
?>
<!-- Menu path -->
<div class="row">
	<ol class="breadcrumb">
		<li>
			<a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=core_role"><i class="fa fa-dashboard"></i> Quản trị hệ thống</a>
		</li>
		<li>
			<a href="?<?=TTH_PATH?>=core_role"><i class="fa fa-group"></i> Nhóm quản trị</a>
		</li>
		<li>
			<i class="fa fa-list"></i> <?=$name?>
		</li>
	</ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-dashboard"></i> Phân quyền quản trị
			</div>
			<!-- .panel-heading -->
			<div class="panel-body">
				<div class="panel-group panel-tabs-line">
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><i class="fa fa-edit fa-fw"></i> Quản lý nội dung</a>
							</h4>
						</div>
						<div id="collapseOne" class="panel-collapse collapse">
							<div class="panel-body">
								<div class="col-lg-12">
									<div class="panel panel-default">
										<div class="panel-heading">
											Thể loại
										</div>
										<!-- /.panel-heading -->
										<div class="panel-body">
											<!-- Nav tabs -->
											<ul class="nav nav-tabs">
												<li class="active"><a href="#manager" data-toggle="tab">Chung</a>
												</li>
												<li><a href="#article" data-toggle="tab">Bài viết</a>
												</li>
												<li><a href="#gallery" data-toggle="tab">Hình ảnh</a>
												</li>
												<li><a href="#document" data-toggle="tab">Văn bản / Tàu liệu</a>
												</li>
												<li><a href="#product" data-toggle="tab">Sản phẩm</a>
												</li>
												<li><a href="#project" data-toggle="tab">Dự án</a>
												</li>
												<li><a href="#comment" data-toggle="tab">Đánh giá sản phẩm</a>
												</li>
												<li><a href="#others" data-toggle="tab">Dữ liệu khác</a>
												</li>
												<li><a href="#pages" data-toggle="tab">Phần bổ sung</a>
												</li>
											</ul>
											<!-- Tab panes -->
											<div class="tab-content">
												<div class="tab-pane fade in active" id="manager">
													<form id="core_category" method="post" onsubmit="return coreDashboard('core_category', 'category');">
														<?php
														echo showCoreCategory($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="article">
													<form id="core_article" method="post" onsubmit="return coreDashboard('core_article', 'article');">
														<?php
														echo showCoreArticle($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="gallery">
													<form id="core_gallery" method="post" onsubmit="return coreDashboard('core_gallery', 'gallery');">
														<?php
														echo showCoreGallery($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="document">
													<form id="core_document" method="post" onsubmit="return coreDashboard('core_document', 'document');">
														<?php
														echo showCoreDocument($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="product">
													<form id="core_product" method="post" onsubmit="return coreDashboard('core_product', 'product');">
														<?php
														echo showCoreProduct($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="project">
													<form id="core_project" method="post" onsubmit="return coreDashboard('core_project', 'project');">
														<?php
														echo showCoreProject($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="comment">
													<form id="core_comment" method="post" onsubmit="return coreDashboard('core_comment', 'comment');">
														<?php
														echo showCorecomment($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="tour">
													<form id="core_tour" method="post" onsubmit="return coreDashboard('core_tour', 'tour');">
														<?php
														echo showCoreTour($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="car">
													<form id="core_car" method="post" onsubmit="return coreDashboard('core_car', 'car');">
														<?php
														echo showCoreCar($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="others">
													<form id="core_others" method="post" onsubmit="return coreDashboard('core_others', 'others');">
														<?php
														echo showCoreOthers($role_id);
														?>
													</form>
												</div>
												<div class="tab-pane fade" id="pages">
													<form id="core_pages" method="post" onsubmit="return coreDashboard('core_pages', 'pages');">
														<?php
														echo showCorePages($role_id);
														?>
													</form>
												</div>
											</div>
										</div>
										<!-- /.panel-body -->
									</div>
									<!-- /.panel -->
								</div>
								<!-- /.col-lg-6 -->
							</div>
						</div>
					</div>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"><i class="fa fa-database fa-fw"></i> Cơ sở dữ liệu</a>
							</h4>
						</div>
						<div id="collapseTwo" class="panel-collapse collapse">
							<div class="panel-body">
								<form id="core_backup" method="post" onsubmit="return coreDashboard('core_backup', 'backup');">
									<?php
									echo showCoreBackup($role_id);
									?>
								</form>
							</div>
						</div>
					</div>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"><i class="fa fa-cogs fa-fw"></i> Cấu hình</a>
							</h4>
						</div>
						<div id="collapseThree" class="panel-collapse collapse">
							<div class="panel-body">
								<form id="core_config" method="post" onsubmit="return coreDashboard('core_config', 'config');">
									<?php
									echo showCoreConfig($role_id);
									?>
								</form>
							</div>
						</div>
					</div>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseFour"><i class="fa fa-wrench fa-fw"></i> Công cụ hỗ trợ</a>
							</h4>
						</div>
						<div id="collapseFour" class="panel-collapse collapse">
							<div class="panel-body">
								<form id="core_tool" method="post" onsubmit="return coreDashboard('core_tool', 'tool');">
									<?php
									echo showCoreTool($role_id);
									?>
								</form>
							</div>
						</div>
					</div>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseFive"><i class="fa fa-dashboard fa-fw"></i> Quản trị hệ thống</a>
							</h4>
						</div>
						<div id="collapseFive" class="panel-collapse collapse">
							<div class="panel-body">
								<form id="core_core" method="post" onsubmit="return coreDashboard('core_core', 'core');">
									<?php
									echo showCoreCore($role_id);
									?>
								</form>
							</div>
						</div>
					</div>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseSix"><i class="fa fa-sitemap fa-fw"></i> Thông tin hệ thống</a>
							</h4>
						</div>
						<div id="collapseSix" class="panel-collapse collapse">
							<div class="panel-body">
								<form id="core_info" method="post" onsubmit="return coreDashboard('core_info', 'info');">
									<?php
									echo showCoreInfo($role_id);
									?>
								</form>
							</div>
						</div>
					</div>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseLanding"><i class="fa fa-file-text fa-fw"></i> Landing Page</a>
							</h4>
						</div>
						<div id="collapseLanding" class="panel-collapse collapse">
							<div class="panel-body">
								<form id="core_landing" method="post" onsubmit="return coreDashboard('core_landing', 'landing');">
									<?php
									echo showCoreLanding($role_id);
									?>
								</form>
							</div>
						</div>
					</div>

				</div>
			</div>
			<!-- .panel-body -->
		</div>
		<!-- /.panel -->
	</div>
	<!-- /.col-lg-12 -->
</div>
<!-- /.row -->