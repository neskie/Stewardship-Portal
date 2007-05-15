<?php
/*---------------------------------------------------------------
author:	alim karim
date:	May 14, 2007
file:	tng_add_edit_user.php

desc:	webpage to add users or reset passwords for
		existing users
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');

session_start();
//unset($_SESSION['user_list']);
//return;


// form is being loaded first time or
// it is being loaded through ajax
if(isset($_SESSION['obj_login'])){
	global $user_list;
	global $obj_list;
	global $xslt_user;
	
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// query for all users in
			// the db
			case "get_users":
				get_user_list($user_list);
				$xml = generate_object_list_xml($user_list, "");
				echo $xml;
			break;
			// the caller wishes to reset the
			// password for a user
			case "reset_password":
				$uid = $_POST['ajax_uid'];
				$new_passwd = $_POST['ajax_newpasswd'];
				reset_password($uid, $new_passwd);
				//$html = generate_html($xml, $xslt_user);
			break;
			// the caller wishes to add a new
			// user to the db
			case "add_user":
				$uname = $_POST['ajax_uname'];
				$passwd = $_POST['ajax_passwd'];
				add_user($uname, $passwd);
				// regenerate the user list
				// and send back the new list
				// as xml
				get_user_list($user_list);
				$xml = generate_object_list_xml($user_list, "");
				echo $xml;
			break;
		}
	}
}

///
/// get_user_list()
/// get the user list from the 
/// database
///
function get_user_list(&$user_list){
	$user_list = array();
	
	$sql_str = "SELECT "
					. "uid, " 
					. "uname "
				. "FROM " 
					. "tng_user ";
	

	$dbconn =& new DBConn();

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	$n_users = pg_num_rows($result);
	// populate user_list array
	// as name-value pairs
	// i.e. the user name is the name, and the
	// user id is the value
	for($i = 0; $i < $n_users; $i++)
		$user_list[pg_fetch_result($result, $i, 1)] = pg_fetch_result($result, $i, 0);
	
	$dbconn->disconnect();
}

///
/// generate_object_list_xml()
/// produce xml representing a list
/// of objects(users, forms, layers, etc) 
/// limited by $prefix (if any)
/// provided by the user.
/// note that $obj_list is a name-value pair
/// array, where the name of the object is the key
/// and the id of the object is the value
/// 
function generate_object_list_xml($obj_list, $prefix){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
		 		. "<objects>";
	$n_objects = count($obj_list);
	$obj_names = array_keys($obj_list);
	
	for($i = 0; $i < $n_objects; $i++){
		if($prefix == ""
			|| substr($obj_names[$i], 0, strlen($prefix)) == $prefix
			){
				$xml .= "<object>"
						. "<id>"
						. $obj_list[$obj_names[$i]]
						. "</id>"
						. "<name>"
						. $obj_names[$i]
						. "</name>"
						. "</object>\n";
			}
		}
		
		$xml .= "</objects>";
		return $xml;
}

///
/// reset_password()
/// reset the password for
/// uid to new_passwd
///
function reset_password($uid, $new_passwd){
	$md5_pass = md5($new_passwd);
	$sql_str = "UPDATE "
					. "tng_user "
				. "SET "
					. "passwd = '" . $md5_pass . "' "
				. "WHERE "
					. "uid = " . $uid;

	$dbconn =& new DBConn();

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}

	$dbconn->disconnect();
}

///
/// add_user()
/// create a user record in the
/// tng_user table
///
function add_user($uname, $passwd){
	$md5_passwd = md5($passwd);
	
	$sql_str = "INSERT INTO tng_user "
					. "("
						. "uname, "
						. "passwd "
					. ") "
					. "VALUES "
					. "("
						. "'" . $uname . "', "
						. "'" . $md5_passwd . "' "
					. ")";
					
	$dbconn =& new DBConn();

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}

	$dbconn->disconnect();
}

?>