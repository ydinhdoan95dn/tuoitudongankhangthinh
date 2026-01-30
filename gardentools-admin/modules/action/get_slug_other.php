<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
if(isset($_POST['name'])) {
	$name = $_POST['name'];
	$stringObj = new StringHelper();
	$slug = $stringObj->getSlug($name);
	echo $slug;
}