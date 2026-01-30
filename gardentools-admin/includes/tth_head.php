<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-language" content="vi">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="DXMT - Administration Control Panel">
<meta name="author" content="DXMT Interior Design">

<title>DXMT Admin - Control Panel</title>

<!-- Bootstrap Core CSS -->
<link rel="stylesheet" type="text/css" href="./css/bootstrap.css" charset="utf-8" media="all" >
<!-- File input CSS -->
<link rel="stylesheet" type="text/css" href="./css/fileinput.css" media="all" >
<!-- MetisMenu CSS -->
<link rel="stylesheet" type="text/css" href="./css/plugins/metisMenu/metisMenu.css" media="all">
<!-- Custom CSS -->
<link rel="stylesheet" type="text/css" href="./css/style-admin.css" media="all">
<!-- DXMT Admin Theme -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="./css/luxe-admin.css" media="all">
<!-- Animate CSS -->
<link rel="stylesheet" type="text/css" href="./css/animate.css" media="all">
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="./css/plugins/dataTables.bootstrap.css">
<link rel="stylesheet" type="text/css" href="./css/plugins/jquery.dataTables.css">
<!-- Datetimepicker CSS -->
<link rel="stylesheet" type="text/css" href="./js/jquery.calendar/jquery.datetimepicker.css"/>
<!-- Popup Alert CSS -->
<link rel="stylesheet" type="text/css" href="./css/popup/jquery.boxes.css">
<!-- blueimp Gallery CSS -->
<link rel="stylesheet" type="text/css" href="./css/gallery/blueimp-gallery.min.css">
<!-- Fancybox CSS -->
<link rel="stylesheet" type="text/css" href="./js/fancybox/jquery.fancybox.css?v=2.1.5" charset="utf-8" media="screen" />
<link rel="stylesheet" type="text/css" href="./js/fancybox/helpers/jquery.fancybox-buttons.css?v=1.0.5" media="screen" />
<link rel="stylesheet" type="text/css" href="./js/fancybox/helpers/jquery.fancybox-thumbs.css?v=1.0.7" media="screen" />

<!-- jQuery Version 1.11.0 -->
<script type="text/javascript" src="./js/jquery/jquery-1.11.0.js"></script>
<!-- Bootstrap Core JavaScript -->
<script type="text/javascript" src="./js/bootstrap/bootstrap.js"></script>
<!-- Modernizr JavaScript -->
<script type="text/javascript" src="./js/modernizr.min.js"></script>
<!-- File input JavaScript -->
<script type="text/javascript" src="./js/bootstrap/fileinput.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script type="text/javascript" src="./js/plugins/metisMenu/metisMenu.js"></script>
<!-- Custom Theme JavaScript -->
<script type="text/javascript" src="./js/tth-admin.js"></script>
<!-- DataTables JavaScript -->
<script type="text/javascript" src="./js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./js/plugins/dataTables/dataTables.bootstrap.js"></script>
<!-- Datetimepicker JavaScript -->
<script type="text/javascript" src="./js/jquery.calendar/jquery.datetimepicker.js"></script>
<!-- Bootstrap-wizard JavaScript -->
<script type="text/javascript" src="./js/bootstrap/bootstrap-wizard.min.js"></script>
<!-- Bootstrap-tagsinput JavaScript -->
<script type="text/javascript" src="./js/bootstrap/bootstrap-tagsinput.js"></script>
<!-- autoNumeric JavaScript -->
<script type="text/javascript" src="./js/autoNumeric.js"></script>
<!-- validate JavaScript -->
<script type="text/javascript" src="./js/jquery.validation/jquery.validate.min.js"></script>
<!-- Fancybox JavaScript -->
<script type="text/javascript" src="./js/fancybox/jquery.fancybox.js?v=2.1.5"></script>
<script type="text/javascript" src="./js/fancybox/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
<script type="text/javascript" src="./js/fancybox/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
<!-- Popup Alert JavaScript -->
<script type="text/javascript" src="./js/jquery.slimscroll.js"></script>
<!-- Admin Base URL for AJAX calls -->
<script type="text/javascript">
var ADMIN_BASE = '<?=rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/\\")?>';
</script>
<!-- Validate JavaScript -->
<script type="text/javascript" src="./js/script.js"></script>
<!-- Popup Alert JavaScript -->
<script type="text/javascript" src="./js/jquery.popup/jquery.boxes.js"></script>
<script type="text/javascript" src="./js/jquery.popup/jquery.boxes.repopup.js"></script>
<!-- CKEditor -->
<script type="text/javascript" src="../editor/ckeditor/ckeditor.js"></script>
<!-- CKFinder -->
<script type="text/javascript" src="../editor/ckfinder/ckfinder.js"></script>
<!-- blueimp Gallery JavaScript -->
<script type="text/javascript" src="./js/gallery/jquery.blueimp-gallery.min.js"></script>
<!-- Admin Tutorial Builder CSS -->
<?php if(defined('TUTORIAL_ENABLED') && TUTORIAL_ENABLED): ?>
<link rel="stylesheet" type="text/css" href="./css/admin-tutorial.css?v=<?=time()?>" media="all">
<?php endif; ?>
<!-- Admin Responsive CSS -->
<link rel="stylesheet" type="text/css" href="./css/admin-responsive.css?v=<?=time()?>" media="all">
<!-- Admin Mobile Menu CSS (prefix: adm-) -->
<link rel="stylesheet" type="text/css" href="./css/admin-mobile-menu.css?v=<?=time()?>" media="all">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
