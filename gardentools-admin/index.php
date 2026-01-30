<?php
// Debug mode - hiển thị tất cả lỗi PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@session_start();

// System
define('TTH_SYSTEM', true);
$_SESSION["language"] = (!empty($_SESSION["lang_admin"]) && isset($_SESSION["lang_admin"])) ? $_SESSION["lang_admin"] : 'vi';

require_once('..' . DIRECTORY_SEPARATOR . 'define.php');
include_once(_A_FUNCTIONS . DS . "Function.php");

// Tutorial Builder Config
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tutorial_config.php');
try {
	$db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (DatabaseConnException $e) {
	echo $e->getMessage();
}
// include_once(_F_INCLUDES . DS . "_tth_constants.php");
require_once(ROOT_DIR . DS . ADMIN_DIR . DS . '_check_login.php');
if ($login_true) {
	$tth = isset($_GET[TTH_PATH]) ? $_GET[TTH_PATH] : 'home';
	include_once(_A_FUNCTIONS . DS . "ContentManager.php");
	include_once(_A_FUNCTIONS . DS . "FuncLand.php");

	$corePrivilegeSlug = array();
	$corePrivilegeSlug = corePrivilegeSlug();
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<?php
		include(_A_INCLUDES . DS . "tth_head.php");
		?>
	</head>

	<body>
		<div id="wrapper" style=" background: #1b2631; ">
			<!-- Navigation -->
			<nav class="navbar navbar-default navbar-static-top" role="navigation">
				<?php
				include(_A_INCLUDES . DS . "tth_header.php");
				?>
				<div class="navbar-default sidebar" role="navigation">
					<?php
					include(_A_INCLUDES . DS . "tth_menu.php");
					?>
				</div>
			</nav>
			<div id="page-wrapper">
				<?php
				if (is_file(_A_MODULES . DS . $tth . ".php"))
					include(_A_MODULES . DS . $tth . ".php");
				else
					loadPageAdmin("Hiện tại chưa hỗ trợ chức năng này.", ADMIN_DIR);
				?>
				<!-- Modal -->
				<div class="modal fade" id="_notification" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
					aria-hidden="true"></div>
				<!-- /.modal -->
			</div>
			<!-- /#page-wrapper -->
			<div id="footer-admin">
				<?php
				// Footer content + Mobile Menu (mobile wrapper dat ngoai #footer-admin)
				include(_A_INCLUDES . DS . "tth_footer.php");
				?>
				<!-- #footer-admin duoc dong trong tth_footer.php -->

			</div>

			<!-- <a href="javascript:void(0)" title="Lên đầu trang" id="btnGoTop">
		<span id="toTopHover"></span>
	</a> -->

			<div id="loadingPopup" style="z-index: 999999999;"></div>

			<?php // Admin Tutorial Builder JS
				if (defined('TUTORIAL_ENABLED') && TUTORIAL_ENABLED): ?>
				<script type="text/javascript" src="./js/admin-tutorial.js?v=<?= time() ?>"></script>
				<script type="text/javascript" src="./js/tutorial-test.js?v=<?= time() ?>"></script>
			<?php endif; ?>
	</body>

	</html>
	<!-- Tooltip -->
	<script>
		$('#wrapper').tooltip({
			selector: "[data-toggle=tooltip]",
			container: "body"
		});
		$('#dataTablesList').find('input[type="checkbox"]').shiftSelectable();

		// ============================================
		// Placeholder Enhancement - Phân biệt input đã nhập vs chưa nhập
		// ============================================
		$(document).ready(function () {
			// Function kiểm tra và update class has-value
			function updateInputHighlight($el) {
				var val = $el.val();
				if (val && val.trim() !== '' && val !== '0') {
					$el.addClass('has-value');
				} else {
					$el.removeClass('has-value');
				}
			}

			// Áp dụng cho tất cả input, textarea, select trong form
			var $inputs = $('input[type="text"], input[type="email"], input[type="number"], input[type="tel"], input[type="url"], textarea, select').not('[readonly]').not('.no-highlight');

			// Check initial state
			$inputs.each(function () {
				updateInputHighlight($(this));
			});

			// Update on change/input
			$inputs.on('input change blur', function () {
				updateInputHighlight($(this));
			});

			// Select đặc biệt - check giá trị
			$('select').not('.no-highlight').each(function () {
				var $sel = $(this);
				var val = $sel.val();
				if (val && val !== '' && val !== '0' && val !== null) {
					$sel.addClass('has-value');
				}
			}).on('change', function () {
				var $sel = $(this);
				var val = $sel.val();
				if (val && val !== '' && val !== '0' && val !== null) {
					$sel.addClass('has-value');
				} else {
					$sel.removeClass('has-value');
				}
			});
		});
	</script>
	<?php
} else
	include("login.php");
