<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
$bds_business_id = isset($_GET['id']) ? $_GET['id']+0 : $bds_business_id+0;
$db->table = "bds_business";
$db->condition = "bds_business_id = ".$bds_business_id;
$db->order = "";
$rows = $db->select();
foreach($rows as $row) {
	$menu_id    = $row['bds_business_menu_id']+0;
}
if($db->RowCount==0) loadPageAdmin("Tin rao không tồn tại.","?".TTH_PATH."=bds_business_manager");
// ---------------
$db->table = "bds_business_menu";
$db->condition = "bds_business_menu_id = ".$menu_id;
$rows = $db->select();
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
			<a href="?<?=TTH_PATH?>=bds_business_list&id=<?=$menu_id?>"><i class="fa fa-list"></i> <?=getNameMenu($menu_id, 'bds_business')?></a>
		</li>
		<li>
			<i class="fa fa-cog"></i> Chỉnh sửa tin
		</li>
	</ol>
</div>
<!-- /.row -->
<?php
include_once (_A_TEMPLATES . DS . "bds_business.php");
if(empty($typeFunc)) $typeFunc = "no";

$date = new DateClass();
$stringObj = new StringHelper();

$OK = false;
$error = '';
if($typeFunc=='edit'){
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
			'is_active'=>$is_active+0,
			'modified_time'=>time(),
			'user_id'=>$_SESSION["user_id"]
		);
		$db->condition = "bds_business_id = ".$bds_business_id;
		$db->update($data);

		loadPageSucces("Đã chỉnh sửa Tin rao thành công.","?".TTH_PATH."=bds_business_list&id=".$land_type);
		$OK = true;
	}
}
else {
	$db->table = "bds_business";
	$db->condition = "bds_business_id = ".$bds_business_id;
	$rows = $db->select();
	foreach($rows as $row) {
		$bds_business_menu_id            = $row['bds_business_menu_id']+0;
		$project                    = $row['project']+0;
		$street                     = $row['street'];
		$road                       = $row['road']+0;
		$floors                     = $row['floors']+0;
		$view_direction             = $row['view_direction']+0;
		$view_scene                 = $row['view_scene'];
		$direction                  = $row['direction']+0;
		$location                   = $row['location']+0;
		$geo_radius                 = $row['geo_radius']+0;
		$area_land                  = $row['area_land']+0;
		$area_use                   = $row['area_use']+0;
		$price_total_land           = $row['price_total_land']+0;
		$price_unit_land            = $row['price_unit_land']+0;
		$price_house                = $row['price_house']+0;
		$price_house_m2             = $row['price_house_m2']+0;
		$price_total_house_land     = $row['price_total_house_land']+0;
		$transactions_deposit       = $row['transactions_deposit']+0;
		$transactions_duration      = $row['transactions_duration'];
		$transactions_contract      = $row['transactions_contract']+0;
		$transactions_type          = $row['transactions_type']+0;
		$purpose_use_land           = explode(',', $row['purpose_use_land']);
		$law_land                   = $row['law_land']+0;
		$parallel_price             = $row['parallel_price'];
		$infrastructure_lights      = $row['infrastructure_lights'];
		$infrastructure_water       = $row['infrastructure_water'];
		$infrastructure_view        = $row['infrastructure_view'];
		$infrastructure_land        = $row['infrastructure_land']+0;
		$infrastructure_floors      = $row['infrastructure_floors']+0;
		$type_house                 = $row['type_house']+0;
		$social_05km                = $row['social_05km'];
		$social_1km                 = $row['social_1km'];
		$social_3km                 = $row['social_3km'];
		$social_10km                = $row['social_10km'];
		$social_street              = $row['social_street'];
		$social_educate             = $row['social_educate'];
		$type_show                  = $row['type_show']+0;
		$created_time               = $date->vnOther($row['created_time'],TTH_DATETIME_FORMAT);
		$expiration_time            = $date->vnOther($row['expiration_time'],TTH_DATETIME_FORMAT);
		if($row['status']+0 == 1) {
			$status = 1;
		} else {
			if(($row['expiration_time']+0) < time()) {
				$status = 3;
			} else if((($row['expiration_time']+0) - time()) < (7*24*3600)) {
				$status = 2;
			} else {
				$status = 0;
			}
		}
		$name                       = $row['name'];
		$transactors                = $row['transactors']+0;
		$contact_name               = $row['contact_name'];
		$contact_tell               = $row['contact_tell'];
		$contact_email              = $row['contact_email'];
		$upload_img_id              = $row['upload_id']+0;
		$upload_img_idd             = $row['upload_idd']+0;
		$is_active                  = $row['is_active']+0;
	}
}
if(!$OK) bdsBusiness("?".TTH_PATH."=bds_business_edit", "edit", $bds_business_id, $bds_business_menu_id, $project, $street, $road, $area_land, $area_use, $floors, $view_direction, $view_scene, $direction, $location, $geo_radius, $price_total_land, $price_unit_land, $price_house, $price_house_m2, $price_total_house_land, $transactions_deposit, $transactions_duration, $transactions_contract, $transactions_type, $purpose_use_land, $law_land, $parallel_price, $infrastructure_lights, $infrastructure_water, $infrastructure_view, $infrastructure_land, $infrastructure_floors, $type_house, $social_05km, $social_1km, $social_3km, $social_10km, $social_street, $social_educate, $type_show, $created_time, $expiration_time, $status, $name, $transactors, $contact_name, $contact_tell, $contact_email, $is_active, $upload_img_id, $upload_img_idd, $error);
?>