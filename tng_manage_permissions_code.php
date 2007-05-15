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
//unset($_SESSION['user_list']);
//return;



$user_list =& get_user_list();
$obj_list = array();
$obj_disallowed_list = array();

// form is being loaded first time or
// it is being loaded through ajax
if(isset($_SESSION['obj_login'])){
	global $user_list;
	global $obj_list;
	global $xslt_user;
	
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// the user wishes
			// to limit the list of layers that they see.
			case "search_uname":
				$xml = generate_object_list_xml($user_list, $_POST['ajax_uname']);
				//$html = generate_html($xml, $xslt_user);
				echo $xml;
			break;
			// the user has selected/changed selection
			// on the type of object that they wish to
			// manage permissions for
			case "get_objects":
				$obj_type = $_POST['ajax_obj_type'];
				$obj_set = $_POST['ajax_object_set'];
				$uid = $_POST['uid'];
				switch ($obj_type){
					// the user wishes to manage permissions
					// on layers
					case "layer":
						// now check if ajax requested the
						// set of allowed layers or the set of
						// disallowed layers
						switch ($obj_set){
							case "allowed":
								populate_layers($obj_list, $uid, true);
							break;
							case "disallowed":
								populate_layers($obj_list, $uid, false);
							break;
						}
						
					
					break;
					case "form":
						switch ($obj_set){
							case "allowed":
								populate_forms($obj_list, $uid, true);
							break;
							case "disallowed":
								populate_forms($obj_list, $uid, false);
							break;
						}
					break;
					case "group":
						switch ($obj_set){
							case "allowed":
								populate_groups($obj_list, $uid, true);
							break;
							case "disallowed":
								populate_groups($obj_list, $uid, false);
							break;
						}
					break;
				}
				
				$xml = generate_object_list_xml($obj_list, "");
				//$html = generate_html($xml, $xslt_user);
				echo $xml;	
			break;
			// the user wishes to grant permissions
			// to a certain object to a certain user
			case "grant_perm":
				$obj_type = $_POST['ajax_obj_type'];
				$obj_id = $_POST['ajax_obj_id'];
				$uid = $_POST['uid'];
				$action = "grant";
				switch ($obj_type){
					case "layer":
						toggle_layer_permission($obj_id, $uid, $action);
						// nothing echoed back
					break;
					case "form":
						toggle_form_permission($obj_id, $uid, $action);
						// nothing echoed back
					break;
					case "group":
						toggle_group_permission($obj_id, $uid, $action);
						// nothing echoed back
					break;
				}
			break;
			// the user wishes to revoke permissions
			// on a certain object from a certain user
			case "revoke_perm":
				$obj_type = $_POST['ajax_obj_type'];
				$obj_id = $_POST['ajax_obj_id'];
				$uid = $_POST['uid'];
				$action = "revoke";
				switch ($obj_type){
					case "layer":
						toggle_layer_permission($obj_id, $uid, $action);
						// nothing echoed back
					break;
					case "form":
						toggle_form_permission($obj_id, $uid, $action);
						// nothing echoed back
					break;
					case "group":
						toggle_group_permission($obj_id, $uid, $action);
						// nothing echoed back
					break;
				}
			break;
		}
	}
}

///
/// get_user_list()
/// get the user list from the session
/// array. if one does not exist, it is created.
///
function &get_user_list(){
	$user_list = array();
	
	// create the user list array
	// if one does not exist as a 
	// session variable
	if(!isset($_SESSION['user_list'])){
		$sql_str = "SELECT "
						. "uid, " 
						. "uname "
					. "FROM tng_user ";
		

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
		
		// set session variable
		$_SESSION['user_list'] = $user_list;
	}
	
	return $_SESSION['user_list'];
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
/// populate_layers()
/// get list of layers this that user with
/// $uid is allowed to see based on the 
/// $allowed parameter
///
function populate_layers(&$array, $uid, $allowed){
	$sql_str = "SELECT "
				. "tng_spatial_layer.layer_id, "
				. "tng_spatial_layer.layer_name "
			. "FROM "
				. "tng_layer_permission "
				. "INNER JOIN tng_spatial_layer ON tng_spatial_layer.layer_id = tng_layer_permission.layer_id "
			. "WHERE "
				. "tng_layer_permission.uid = " . $uid;
	
	if(!$allowed)
		$sql_str =  "SELECT "
		      			. "tng_spatial_layer.layer_id, "
		      			. "tng_spatial_layer.layer_name "
					. "FROM "
		    			. "tng_spatial_layer "
					. "EXCEPT "
					. $sql_str;
	
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_layers = pg_num_rows($result);
	// populate array
	// as name-value pairs
	// i.e. the layer name is the key, and the
	// layer id is the value
	for($i = 0; $i < $n_layers; $i++)
		$array[pg_fetch_result($result, $i, 1)] = pg_fetch_result($result, $i, 0);

	$dbconn->disconnect();
}

///
/// populate_forms()
/// get list of forms that user with
/// $uid is allowed to see based on the 
/// $allowed parameter
///
function populate_forms(&$array, $uid, $allowed){
	$sql_str = "SELECT "
					. "tng_form.form_id, "
					. "tng_form.form_name "
				. "FROM "
					. "tng_form "
					. "INNER JOIN tng_process_form_permissions ON tng_form.form_id = tng_process_form_permissions.form_id "
				. "WHERE "
					. "tng_process_form_permissions.uid = " . $uid;
	if(!$allowed)
		$sql_str = "SELECT "
					. "tng_form.form_id, "
					. "tng_form.form_name "
				. "FROM "
					. "tng_form "
				. "EXCEPT "
					. $sql_str;
	
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_forms = pg_num_rows($result);
	// populate array
	// as name-value pairs
	// i.e. the form name is the key, and the
	// form id is the value
	for($i = 0; $i < $n_forms; $i++)
		$array[pg_fetch_result($result, $i, 1)] = pg_fetch_result($result, $i, 0);

	$dbconn->disconnect();
}

///
/// populate_groups()
/// get list of groups that user with
/// $uid is in based on the 
/// $allowed parameter
///
function populate_groups(&$array, $uid, $allowed){
	$sql_str = "SELECT "
					. "tng_group.gid, "
					. "tng_group.gname "
				. "FROM "
					. "tng_group "
					. "INNER JOIN tng_group_users ON tng_group.gid = tng_group_users.gid "
				. "WHERE "
					. "tng_group_users.uid = " . $uid;
	if(!$allowed)
		$sql_str = "SELECT "
		 			. "tng_group.gid,"
					. "tng_group.gname "
				. "FROM "
					. "tng_group "
				. "EXCEPT "
					. $sql_str;
	
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_forms = pg_num_rows($result);
	// populate array
	// as name-value pairs
	// i.e. the form name is the key, and the
	// form id is the value
	for($i = 0; $i < $n_forms; $i++)
		$array[pg_fetch_result($result, $i, 1)] = pg_fetch_result($result, $i, 0);

	$dbconn->disconnect();
}

///
/// toggle_form_permission()
/// grant permission on $form_id to
/// $uid
/// 
function toggle_form_permission($form_id, $uid, $action){
	$sql_str = "";
	if($action == "grant")
		$sql_str = "INSERT INTO tng_process_form_permissions "
					. "( "
						. "form_id, "
						. "uid"
					. ") "
					. "VALUES "
					. "("
						. $form_id . ", "
						. $uid 
					. ")";
	else
		$sql_str = "DELETE "
					. "FROM "
						. "tng_process_form_permissions "
					. "WHERE "
						. "form_id = " . $form_id . " "
					. "AND "
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
/// toggle_group_permission()
/// grant/revoke permission on $group_id to
/// $uid
/// 
function toggle_group_permission($group_id, $uid, $action){
	$sql_str = "";
	if($action == "grant")
		$sql_str = "INSERT INTO tng_group_users "
					. "( "
						. "gid, "
						. "uid"
					. ") "
					. "VALUES "
					. "("
						. $group_id . ", "
						. $uid 
					. ")";
	else
		$sql_str = "DELETE "
					. "FROM "
						. "tng_group_users "
					. "WHERE "
						. "gid = " . $group_id . " "
					. "AND "
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
/// toggle_layer_permission()
/// grant/revoke permission on $layer_id to
/// $uid
/// 
function toggle_layer_permission($layer_id, $uid, $action){
	$sql_str = "";
	
	if($action == "grant")
		$sql_str = "INSERT INTO tng_layer_permission "
					. "( "
						. "layer_id, "
						. "uid"
					. ") "
					. "VALUES "
					. "("
						. $layer_id . ", "
						. $uid 
					. ")";
	else // revoke
		$sql_str = "DELETE " 
					. "FROM " 
						. "tng_layer_permission "
					. "WHERE "
						. "layer_id = " . $layer_id . " "
					. "AND "
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


?>