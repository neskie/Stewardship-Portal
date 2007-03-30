<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_list_forms_code.php

desc:	contains the php code behind the tng_list_forms.php page
---------------------------------------------------------------*/

include_once('classes/class_login.php');
include_once('classes/class_fist_conf_file_generator.php');

session_start();
// globals in this page
$login;
$form_list = array();
$form_list_size = 0;

///
/// main method
/// only proceed if login object
/// is valid
///
if(!isset($_SESSION['obj_login'])){
	echo "login session variable not set";
	header("Location: tng_login.php");
}else if(!isset($_POST['form_action'])){ // first time form is being loaded
	// retrieve login object
	$login = $_SESSION['obj_login'];
	// call function to fill array with
	// names and ids of forms in the db
	fetch_form_list($login->uid);
}else{ // post back 
	if($_POST['form_action'] == "launch_fist"){
		$mapfile = "/home/karima/public_html/fist/sites/example_world/mapfiles/example_lin.map";
		$output_dir = "/tmp/";
		$login = $_SESSION['obj_login'];
		$fist_file_gen =& new Fist_Conf_File_Generator($login->uid, $mapfile, $output_dir);
		$fist_file_gen->get_viewable_layers();
		$fist_file_gen->generate_map_file();
	}else if($_POST['form_action'] == "fill_form"){
		// set session variable and redirect
		// to display form page.
		$_SESSION['form_id'] = $_POST['form_id'];
		$_SESSION['readonly'] = 'false';
		header("Location: tng_display_form.php");
	}
}

///
/// fetch_form_list()
/// function to fetch list of forms in the db
/// that the current user is allowed to access
///
function fetch_form_list($uid){
	global $form_list;
	global $form_list_size;
	// permissions should be incorporated here
	$sql_str = "SELECT "
				. "form_id, "
				. "form_name "
			. "FROM "
				. "tng_form ";
	
	$dbconn = new DBConn();
	
	$dbconn->connect();
	
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
			echo "An error occurred while executing the query - tng_list_forms.php:25 " . pg_last_error($dbconn->conn);
			$dbconn->disconnect();
	}else{ // successfuly ran the query
		$form_list_size = pg_num_rows($result);
		// fill the array with the form ids and form names.
		// each element in the form_list array is an array
		// of two elements.
		for($i = 0; $i < $form_list_size; $i++){
			$form_list[$i] = array(pg_fetch_result($result, $i, 0),
									pg_fetch_result($result, $i, 1));
		}
	}

	$dbconn->disconnect();
}
?>