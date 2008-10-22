<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Dec 27, 2007
file:	tng_assign_sub_perm_code.php

desc:	backend script to allow users to select
		which additional users can see a submission
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');
// since php4 does not have built in json support,
// make sure the json extension is loaded
if(phpversion() < 5.0)
	dl('json.so');
	
session_start();

// do not process any requests if the 
// session variable is not set
if(!isset($_SESSION['obj_login']))
	return;

// globals


if(isset($_GET['ajax_req'])){
	switch($_GET['ajax_req']){
		case "get_groups_and_users":
			$groups = get_groups($_GET['submission_id']);
			echo  json_encode($groups);
		break;
	}
}else if(isset($_POST['ajax_req'])){
	switch($_POST['ajax_req']){
		case "submit_allowed_users":
			$sub_id = $_POST['submission_id'];
			$user_list =  json_decode(str_replace("\\", "", $_POST['user_list']), true);
			create_permissions($sub_id, $user_list);
		break;
	}
}


/*---------------------------------------------------------------*/
// functions
/*---------------------------------------------------------------*/

///
/// get_groups()
/// get groups/users
///
function get_groups($sub_id){
	$dbconn =& new DBConn();
	$groups = array();
	$sql_str = "SELECT "
					. "gid, "
					. "gname "
				. "FROM "
					. "tng_group ";
					
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_groups = pg_num_rows($result);
	for($i = 0; $i < $n_groups; $i++){
		$groups[$i] = array(
							"gid" => pg_fetch_result($result, $i, 'gid'),
							"gname" => pg_fetch_result($result, $i, 'gname'),
							//"users" => get_group_users(pg_fetch_result($result, $i, 'gid'), $sub_id),
							"children" => get_group_users(pg_fetch_result($result, $i, 'gid'), $sub_id)

							);
	}
	
	return $groups;
}

///
/// get_group_users()
/// get all users in the specified group, along with
/// an attribute to specify if they can view the
/// specified submission
///
function get_group_users($gid, $sub_id){
	$dbconn =& new DBConn();
	$users = array();
	// note that the sql statement joins on the
	// submission permission table. this way, if
	// the request is being sent for an existing
	// submission, we will know which users within
	// the given group have permission to view
	$sql_str = "SELECT "
					. "tng_group_users.uid, "
					. "tng_user.uname, "
					. "not nullvalue(tng_submission_permission.perm_id) as selected "
				. "FROM "
					. "tng_group_users "
					. "INNER JOIN tng_user ON tng_group_users.uid = tng_user.uid "
					. "LEFT JOIN tng_submission_permission ON tng_user.uid = tng_submission_permission.uid "
							. "AND tng_submission_permission.sub_id = " . $sub_id . " "
				. "WHERE "
					. "tng_group_users.gid = " . $gid;
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sql_str ." - " 
			. pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	$n_users = pg_num_rows($result);
	for($i = 0; $i < $n_users; $i++){
		$users[$i] = array(
							"uid" => pg_fetch_result($result, $i, 'uid'),
							"uname" => pg_fetch_result($result, $i, 'uname'),
							"checked" => (pg_fetch_result($result, $i, 'selected') == "t") ? true:false,
							"leaf" => true
							);
	}
	$dbconn->disconnect();
	
	return $users;
}

///
/// create_permissions()
/// delete existing entries.
/// insert entries into 
/// tng_submission_permission for the
/// given submission id and user ids.
///
function create_permissions($sub_id, $user_list){
	$dbconn =& new DBConn();
	// delete old permissions
	$sql_str = "DELETE FROM "
					. "tng_submission_permission "
				. "WHERE "
					. "sub_id = " . $sub_id;
					
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sql_str ." - " 
			. pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	// insert new permissions
	$n_users = count($user_list);	
	for($i = 0; $i < $n_users; $i++){
		$sql_str = "INSERT INTO " 
						. "tng_submission_permission "
							. "("
								. "sub_id, "
								. "uid "
							. ") "
							. "VALUES "
							. "("
								. $sub_id . ", "
								. $user_list[$i]
							. ")";
		$result = pg_query($dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query - " 
				. $sql_str ." - " 
				. pg_last_error($dbconn->conn);
			$dbconn->disconnect();
			return NULL;
		}
	}
	$dbconn->disconnect();
}
?>
