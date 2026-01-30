<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$bds_business_menu_id = isset($_GET['id']) ? $_GET['id']+0 : $land_type+0;
$db->table = "bds_business_menu";
$db->condition = "bds_business_menu_id = ".$bds_business_menu_id;
$rows = $db->select();
if($db->RowCount==0) loadPageAdmin("Mục không tồn tại.","?".TTH_PATH."=bds_business_manager");
$category_id = 0;
foreach($rows as $row) {
	$category_id =	$row["category_id"]+0;
}
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
			<a href="?<?=TTH_PATH?>=bds_business_list&id=<?=$bds_business_menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($bds_business_menu_id, 'bds_business')?></a>
		</li>
		<li>
			<i class="fa fa-plus-square-o"></i> Thêm tin
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "bds_business.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();
$stringObj = new StringHelper();
$file_max_size = FILE_MAX_SIZE;
$dir_dest = ROOT_DIR . DS . 'uploads';


$OK = false;
$error = '';
if($typeFunc=='add'){
	if(empty($name)) $error = '<span class="show-error">Vui lòng nhập tên sản phẩm.</span>';
	else {
		$transactions_contract = (isset($transactions_contract)) ? $transactions_contract : '';
		$transactions_type = (isset($transactions_type)) ? $transactions_type : '';
		$purpose_use_land = (isset($purpose_use_land)) ? implode(',',$purpose_use_land) : '';
		$law_land  = (isset($law_land)) ? $law_land : '';
		$infrastructure_land  = (isset($infrastructure_land)) ? $infrastructure_land : '';
		$type_house  = (isset($type_house)) ? $type_house : '';
		$status  = (isset($status)) ? $status : 0;

		$price_total_tth = (formatNumberToInt($price_total_house_land) > 0) ? formatNumberToInt($price_total_house_land) : formatNumberToInt($price_total_land);

		//------------ Xử lý tên đường mới :) ---
		$slug_street_tth = $stringObj->getSlug($street);
		$db->table = "street";
		$db->condition = "slug LIKE '$slug_street_tth'";
		$db->order = "sort ASC";
		$db->limit = 1;
		$db->select();
		if($db->RowCount==0) {
			$db->table = "street";
			$data = array(
				'name'=>$db->clearText($street),
				'slug'=>$db->clearText($slug_street_tth),
				'sort'=>sortAcsStreet()+1,
				'is_active'=>0,
				'hot'=>0,
				'created_time'=>time(),
				'modified_time'=>time(),
				'user_id'=>$_SESSION["user_id"]
			);
			$db->insert($data);
		}

		$db->table = "bds_business";
		$data = array(
			'bds_business_menu_id'=>$land_type+0,
			'project'=>$project+0,
			'street'=>$db->clearText($street),
			'street_slug'=>$db->clearText($slug_street_tth),
			'road'=>$road+0,
			'floors'=>formatNumberToInt($floors),
			'view_direction'=>$view_direction+0,
			'view_scene'=>$db->clearText($view_scene),
			'direction'=>$direction+0,
			'location'=>$location_id+0,
			'geo_radius'=>$geo_radius+0,
			'area_land'=>formatNumberToFloat($area_land),
			'area_use'=>formatNumberToFloat($area_use),
			'price_total_land'=>formatNumberToInt($price_total_land),
			'price_unit_land'=>formatNumberToInt($price_unit_land),
			'price_house'=>formatNumberToInt($price_house),
			'price_house_m2'=>formatNumberToInt($price_house_m2),
			'price_total_house_land'=>$price_total_tth,
			'transactions_deposit'=>formatNumberToInt($transactions_deposit),
			'transactions_duration'=>$db->clearText($transactions_duration),
			'transactions_contract'=>$transactions_contract+0,
			'transactions_type'=>$transactions_type+0,
			'purpose_use_land'=>$db->clearText($purpose_use_land),
			'law_land'=>$law_land+0,
			'parallel_price'=>$db->clearText($parallel_price),
			'infrastructure_lights'=>$db->clearText($infrastructure_lights),
			'infrastructure_water'=>$db->clearText($infrastructure_water),
			'infrastructure_view'=>$db->clearText($infrastructure_view),
			'infrastructure_land'=>$infrastructure_land+0,
			'infrastructure_floors'=>formatNumberToInt($infrastructure_floors),
			'type_house'=>$type_house+0,
			'social_05km'=>$db->clearText($social_05km),
			'social_1km'=>$db->clearText($social_1km),
			'social_3km'=>$db->clearText($social_3km),
			'social_10km'=>$db->clearText($social_10km),
			'social_street'=>$db->clearText($social_street),
			'social_educate'=>$db->clearText($social_educate),
			'type_show'=>$type_show+0,
			'created_time'=>strtotime($date->dmYtoYmd($created_time)),
			'expiration_time'=>strtotime($date->dmYtoYmd($expiration_time)),
			'status'=>$status+0,
			'name'=>$db->clearText($name),
			'transactors'=>$transactors+0,
			'contact_name'=>$db->clearText($contact_name),
			'contact_tell'=>$db->clearText($contact_tell),
			'contact_email'=>$db->clearText($contact_email),
			'upload_id'=>$upload_img_id+0,
			'is_active'=>$is_active+0,
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->insert($data);

		$db->table = "uploads_tmp";
		$data = array(
			'status'=>1
		);
		$db->condition = "upload_id IN (" . ($upload_img_id+0) . ", " . ($upload_img_idd+0) . ")";
		$db->update($data);
		$_SESSION['upload_id'] = 0;
		$_SESSION['upload_idd'] = 0;

		loadPageSucces("Đã thêm Tin rao mới thành công.","?".TTH_PATH."=bds_business_list&id=".$bds_business_menu_id);
		$OK = true;
	}
}
else {
	$upload_img_id              = 0;
	$project                    = 0;
	$street                     = '';
	$road                       = 0;
	$floors                     = '';
	$view_direction             = 0;
	$view_scene                 = '';
	$direction                  = 0;
	$location                   = 0;
	$geo_radius                 = 0;
	$area_land                  = '';
	$area_use                   = '';
	$price_total_land           = '';
	$price_unit_land            = '';
	$price_house                = '';
	$price_house_m2             = '';
	$price_total_house_land     = '';
	$transactions_deposit       = '';
	$transactions_duration      = '';
	$transactions_contract      = 0;
	$transactions_type          = 0;
	$purpose_use_land           = array();
	$law_land                   = 0;
	$parallel_price             = '';
	$infrastructure_lights      = '';
	$infrastructure_water       = '';
	$infrastructure_view        = '';
	$infrastructure_land        = 0;
	$infrastructure_floors      = '';
	$type_house                 = 0;
	$social_05km                = '';
	$social_1km                 = '';
	$social_3km                 = '';
	$social_10km                = '';
	$social_street              = '';
	$social_educate             = '';
	$type_show                  = 1;
	$created_time               = $date->vnOther(time(),TTH_DATETIME_FORMAT);
	$expiration_time            = $date->vnOther(time()+90*24*3600,TTH_DATETIME_FORMAT);
	$status                     = 0;
	$name                       = '';
	$transactors                = 0;
	$contact_name               = '';
	$contact_tell               = '';
	$contact_email              = '';
	$is_active                  = 1;

}
if(!$OK) bdsBusiness("?".TTH_PATH."=bds_business_add", "add", 0, $bds_business_menu_id, $project, $street, $road, $area_land, $area_use, $floors, $view_direction, $view_scene, $direction, $location, $geo_radius, $price_total_land, $price_unit_land, $price_house, $price_house_m2, $price_total_house_land, $transactions_deposit, $transactions_duration, $transactions_contract, $transactions_type, $purpose_use_land, $law_land, $parallel_price, $infrastructure_lights, $infrastructure_water, $infrastructure_view, $infrastructure_land, $infrastructure_floors, $type_house, $social_05km, $social_1km, $social_3km, $social_10km, $social_street, $social_educate, $type_show, $created_time, $expiration_time, $status, $name, $transactors, $contact_name, $contact_tell, $contact_email, $is_active, $upload_img_id, $upload_img_idd, $error);
?>