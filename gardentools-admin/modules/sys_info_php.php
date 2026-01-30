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
			<i class="fa fa-sitemap"></i> Thông tin hệ thống
		</li>
		<li>
			<i class="fa fa-file-code-o"></i> Cấu hình PHP
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
dashboardCoreAdmin();
require_once(_A_INCLUDES . DS . 'core' . DS . 'phpinfo.php');
$xtpl = new XTemplate('configuration_php.tpl', ROOT_DIR . '/themes/admin/modules/siteinfo');
$array = phpinfo_array(4, 1);
$caption = 'Cấu hình PHP';
$thead = array('Chỉ thị', 'Giá trị khu vực', 'Giá trị mặc định');

if( ! empty( $array['PHP Core'] ) ) {
	$xtpl->assign( 'CAPTION', $caption );
	$xtpl->assign( 'THEAD0', $thead[0] );
	$xtpl->assign( 'THEAD1', $thead[1] );
	$xtpl->assign( 'THEAD2', $thead[2] );
	$a = 0;
	foreach( $array['PHP Core'] as $key => $value ) {
		$xtpl->assign( 'KEY', $key );
		if( ! is_array( $value ) ) {
			$xtpl->assign( 'VALUE', str_replace( ',', ', ', $value) );
			$xtpl->parse( 'main.loop.if' );
		}
		else {
			$xtpl->assign( 'VALUE0', str_replace( ',', ', ', $value[0]) );
			$xtpl->assign( 'VALUE1', str_replace( ',', ', ', $value[1]) );
			$xtpl->parse( 'main.loop.else' );
		}
		$xtpl->parse( 'main.loop' );
		++$a;
	}
	$xtpl->parse( 'main' );
	$contents = $xtpl->text( 'main' );
}

echo $contents;