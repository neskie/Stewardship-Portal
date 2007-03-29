<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_login_code.php

desc:	contains the php code behind the tng_login.php page
---------------------------------------------------------------*/
// includes
include('classes/class_login.php');
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
			$_SESSION['obj_login'] = $login;
			// go to list forms page
			header("Location: tng_list_forms.php");
	}
}
?>