<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_login_code.php

desc:	contains the php code behind the tng_login.php page
---------------------------------------------------------------*/
// includes
include('classes/class_login.php');
include('classes/class_app_config.php');
session_start();
///
/// main method
/// process login only if page is postback
///
if(isset($_POST['uname']) && isset($_POST['passwd'])){
	$login = new Login($_POST['uname']);
	if(!$login->validate_login($_POST['passwd'])){
			echo "Login failed. Please check your username and password";
	}else{
			//echo "login succeeded for userid: " . $login->uid;
			// set $this->permission here (func call)
			// set session variable
			unset($_SESSION['obj_login']);
			unset($_SESSION['app_config']);
			$_SESSION['obj_login'] = $login;
			$_SESSION['app_config'] = new App_Config();
			// go to list forms page
			//header("Location: tng_list_forms.php");
			echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login_successful.php'>";  
	}
}
?>
