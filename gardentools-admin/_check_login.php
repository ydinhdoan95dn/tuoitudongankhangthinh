<?php
// DEBUG MODE - Bật hiển thị lỗi PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// error_reporting(0); // Comment dòng này để bật debug
set_time_limit(0);

	if(!defined('TTH_SYSTEM')) { die('Please stop!'); }
	$logout =  isset($_GET['logout']) ? $_GET['logout'] : 'NOT';
	$login_true = false;
	$login_failed = "";
	if ($logout == "OK") {
		$_SESSION["admin_user"] = "";
		$_SESSION["admin_pass"] = "";
		$_SESSION["user_id"] = "";
		$_SESSION['upload_id'] = 0;
		header("location:.." . ADMIN_DIR);
	} else {
		$_SESSION["user_id"] = 0;
		if (empty($_SESSION["admin_user"]))
			$admin_user = "";
		else
			$admin_user = $_SESSION["admin_user"];

		if (empty($_SESSION["admin_pass"]))
			$admin_pass = "";
		else
			$admin_pass = $_SESSION["admin_pass"];
		if(isset($_POST['login_admin'])) {
			if (isset($_POST["login_user_admin"])) {
				$admin_user	= $_POST["login_user_admin"];
			}

			if (isset($_POST["login_password_admin"])) {
				$admin_pass	= $_POST["login_password_admin"];
			}
		}
		$login_true = true;
		if ($admin_user == "" || $admin_pass == "") {
			$login_true = false;
			$login_failed = "";
		}
		else {
			if(!check_login_admin($admin_user, $admin_pass) )
			{
				$login_true = false;
				$login_failed = '<div class="alert alert-danger alert-dismissable">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								   &#272;&#259;ng nh&#7853;p th&#7845;t b&#7841;i, h&#7879; th&#7889;ng kh&#244;ng t&#236;m th&#7845;y t&#224;i kho&#7843;n n&#224;o ph&#249; h&#7907;p v&#7899;i th&#244;ng tin m&#224; b&#7841;n khai b&#225;o. B&#7841;n vui l&#242;ng th&#7917; l&#7841;i (nh&#7899; ki&#7875;m tra ph&#237;m Caps Lock).
								 </div>';
			}
		}
	}

function check_login_admin($user, $pass) {
    global $db;
    $db->table = "core_user";
    $db->condition = "`user_name` = '".$db->clearText($user)."' and `password` = '".md5($db->clearText($user.$pass))."' and `role_id`>0 and `is_active`=1";
	$db->order = "";
	$db->limit = 1;
    $rows = $db->select();
    if($db->RowCount>0) {
	    foreach($rows as $row) {
		    $role_id = $row['role_id']+0;
	    }
	    $db->table = "core_role";
	    $db->condition = "`role_id` = " . ($role_id);
	    $db->order = "";
	    $db->limit = 1;
	    $rows_role = $db->select();
	    if($db->RowCount>0) {
		    if($rows_role[0]['is_active']+0==1) {
			    foreach($rows as $row){
				    $_SESSION["admin_user"] = $row["user_name"];
				    $_SESSION["admin_pass"] = $pass;
				    $_SESSION["user_id"] = $row["user_id"]+0;
			    }
			    return true;
		    } else return false;
	    } else return false;
    } else return false;
}