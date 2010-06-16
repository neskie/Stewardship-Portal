<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 18, 2007
file:	tng_manage_permissions_code.php

desc:	code behind permission manager.
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');

session_start();

// form is being loaded first time or
// it is being loaded through ajax
if(isset($_SESSION['obj_login'])){
	global $user_list;
	global $obj_list;
	global $xslt_user;
	
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// check for duplicate group name
			case "check_gname":
				if(check_duplicate_gname(str_replace("'", "''", $_POST['gname'])))
					echo "true";
				else
					echo "false";
			break;
			// request to create a group
			// with the given name
			case "create_group":
				if(create_group(str_replace("'", "''", $_POST['gname'])))
					echo "true";
				else
					echo "false";
			break;			
		}
	}
}

///
/// check_duplicate_gname()
/// check if a group with the given name
/// exists
///
function check_duplicate_gname($gname){
	$result = true;
	$sql_str = "SELECT "
					. "gid "
				. "FROM "
					. "tng_group "
				. "WHERE "
					. "gname = '" .$gname . "'";
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	if(pg_num_rows($result) == 0)
		$result = false;
	
	$dbconn->disconnect();
	return $result;
}

///
/// create_group()
/// create a group with the given name
/// 
function create_group($gname){
	$sql_str = "INSERT INTO "
					. "tng_group "
					. "( "
						. "gname "
					. ") "
					. "VALUES "
					. "( "
						. "'" . $gname . "'"
					. ") ";
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	return true;
}

?>