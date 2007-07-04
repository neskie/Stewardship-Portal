<?php
/*---------------------------------------------------------------
author:	alim karim
date:	June 21, 2007
file:	tng_search_submissions_code.php

desc:	code behind submission search page.
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');

session_start();

$list_sub_asignees =& get_sub_asignees();
$list_sub_statuses =& get_sub_statuses();

// form is being loaded first time or
// it is being loaded through ajax
if(isset($_SESSION['obj_login'])){
	global $list_sub_asignees;
	global $list_sub_statuses;
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// clear lists held in session 
			// variable and 
			// re read all lists from the db
			case "refresh_lists":
				unset($_SESSION['list_sub_asignees']);
				unset($_SESSION['list_sub_statuses']);
				$list_sub_asignees =& get_sub_asignees();
				$list_sub_statuses =& get_sub_statuses();
			break;
			case "get_sub_asignees":
				$xml = generate_object_list_xml($list_sub_asignees);
				echo $xml;
			break;
			// get list of statuses
			case "get_sub_statuses":
				$xml = generate_object_list_xml($list_sub_statuses);
				echo $xml;
			break;
			// request for a list of users who've made a submission
			case "get_sub_users":
				$xml = generate_object_list_xml($list_sub_users);
				echo $xml;
			break;
			// get details
			case "get_sub_details":
				$where_clause = "WHERE sub_id = " . $_POST['sub_id'];
				$xml = perform_search($where_clause);
				echo $xml;
			break;
			// get children
			case "get_sub_children":
				$where_clause = "WHERE pid = " . $_POST['sub_id'];
				$xml = perform_search($where_clause);
				echo $xml;
			break;
			// get files
			case "get_sub_files":
				$files = get_files($_POST['sub_id']);
				$xml = generate_object_list_xml($files);
				echo $xml;
			break;
			// get layers
			case "get_sub_layers":
				$layers = get_layers($_POST['sub_id']);
				$xml = generate_object_list_xml($layers);
				echo $xml;
			break;
			// display form
			case "display_form":
				display_form($_POST['sub_id']);
				// nothing echoed back. see method.
			break;
			// update name of submission
			case "update_sub_name":
				update_sub_name($_POST['sub_id'], str_replace("'", "''", $_POST['sub_name']));
				// nothing echoed back. see method.
			break;
			// update name of submission
			case "update_sub_status":
				update_sub_status($_POST['sub_id'], $_POST['status_id']);
				// nothing echoed back. see method.
			break;
			// update name of submission
			case "update_sub_asignee":
				update_sub_asignee($_POST['sub_id'], $_POST['uid_assigned']);
				// nothing echoed back. see method.
			break;
		}
	}
}
///
/// get_sub_asignees()
/// get the list of users
/// that have a submission
/// assigned to them
///
function &get_sub_asignees(){
	if(!isset($_SESSION['list_sub_asignees'])){
		$sub_asignees = array();
		// only select users in the TNG group
		$sql_str = "SELECT "
						. "tng_user.uid, "
						. "tng_user.uname "
					. "FROM "
						. "tng_group_users "
						. "INNER JOIN tng_user ON tng_user.uid = tng_group_users.uid "
					. "WHERE "
						. "tng_group_users.gid = 1";
						
		$dbconn =& new DBConn();
		$dbconn->connect();
		$result = pg_query($dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query " 
					. pg_last_error($dbconn->conn);
			$dbconn->disconnect();
			return NULL;
		}
		// create one entry for unassigned
		// submissions
		$sub_asignees['Unassigned'] = -1;
		
		$n_asignees = pg_num_rows($result);
		// store result as name-value pairs
		// where the uname is the name and
		// the uid is the value
		for($i = 0; $i < $n_asignees; $i++){
				$sub_asignees[pg_fetch_result($result, $i, 'uname')] = 
								pg_fetch_result($result, $i, 'uid');
		}
		$dbconn->disconnect();
		// set the session variable
		$_SESSION['list_sub_asignees'] = $sub_asignees;				
	}
	return $_SESSION['list_sub_asignees'];
}

///
/// get_sub_statuses()
/// get the list of valid
/// statuses for a submission from
/// the db.
///
function &get_sub_statuses(){
	if(!isset($_SESSION['list_sub_statuses'])){
		$sub_statuses = array();
		$sql_str = "SELECT "
						. "status_id, "
						. "status_desc "
					. "FROM "
						. "tng_submission_status ";
						
		$dbconn =& new DBConn();
		$dbconn->connect();
		$result = pg_query($dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query " 
					. pg_last_error($dbconn->conn);
			$dbconn->disconnect();
			return NULL;
		}
		
		$n_statuses = pg_num_rows($result);
		// store result as name-value pairs
		// where the status description is the name and
		// the status id is the value
		for($i = 0; $i < $n_statuses; $i++){
				$sub_statuses[pg_fetch_result($result, $i, 'status_desc')] = 
								pg_fetch_result($result, $i, 'status_id');
		}
		$dbconn->disconnect();
		// set the session variable
		$_SESSION['list_sub_statuses'] = $sub_statuses;				
	}
	return $_SESSION['list_sub_statuses'];
}

///
/// generate_object_list_xml()
/// produce xml representing a list
/// of objects(users, forms, layers, etc) 
/// note that $obj_list is a name-value pair
/// array, where the name of the object is the key
/// and the id of the object is the value
/// 
function generate_object_list_xml($obj_list){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
		 		. "<objects>";
	$n_objects = count($obj_list);
	$obj_names = array_keys($obj_list);
	
	for($i = 0; $i < $n_objects; $i++){
		$xml .= "<object>"
				. "<id>"
				. $obj_list[$obj_names[$i]]
				. "</id>"
				. "<name>"
				. $obj_names[$i]
				. "</name>"
				. "</object>\n";
		}
		
		$xml .= "</objects>";
		return $xml;
}

///
/// perform_search()
/// call the view for searching submissions
/// with the given where clause.
/// convert the results to xml and return
/// results.
/// schema:
///	<submissions>
///		<submission>
///			<sub_id> 122 </sub_id>
///			<sub_type> Forestry Referral </sub_type>
///			<sub_titile>  122 - Forestry Referral - John Smith </sub_title>
///			<sub_name> a0093 - Tolko - John Smith - t567 </sub_name>
///			<sub_status> New </sub_status>
///			<submitted_by> John Smith </submitted_by>
///			<assigned_to> Mary Thurow </assigned_to>
///			<sub_date> May 20, 2007 </sub_date>
///		</submission
///		...
/// </submissions>
///
function perform_search($where_clause){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
		 		. "<submissions>";
	$sql_str = "SELECT "
				. "sub_id, "
				. "sub_type, "
				. "sub_title, "
				. "sub_name, "
				. "sub_status, "
				. "submitted_by, "
				. "assigned_to, "
				. "sub_date "
			. "FROM "
				. "vi_submission_search "
			. $where_clause;
		
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " 
				. $sql_str . "\n". pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	$n_submissions = pg_num_rows($result);
	$n_cols = pg_num_fields($result);
	for($i = 0; $i < $n_submissions; $i++){
		$xml .= "<submission>";
		for($j = 0; $j < $n_cols; $j++){
			$xml .= "<" . pg_field_name($result, $j) . ">";
			$xml .= pg_fetch_result($result, $i, $j);
			$xml .= "</" . pg_field_name($result, $j) . ">";
		}
		$xml .= "</submission>\n";
	}
	
	$dbconn->disconnect();
	$xml .= "</submissions>";
	
	return $xml;
}

///
/// display_form()
/// get the form_id, set up session variables
/// and redirect to tng_display_form.php
///
function display_form($sub_id){
	$form_id = get_form_id($sub_id);
	if($form_id != -1){
		// set session variables required by the
		// display_form form
		// note that this has to be done by session
		// variables because when we use the
		// header function, POST variables are
		// NOT transmitted to the php page
		// that we are redirecting to.
		$_SESSION['form_id'] = $form_id;
		$_SESSION['submission_id'] = $sub_id;
		$_SESSION['readonly'] = 'true';
		//header("Location: tng_display_form.php");
		//echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_display_form.php'>";  
	}
}

///
/// get_files()
/// get a list of files belonging to the 
/// given submission
///
function &get_files($sub_id){
	$files = array();
	$sql_str = "SELECT "
				. "file_submission_id, "
				. "file_name "
			. "FROM "
				. "tng_file_submission "
			. "WHERE "
				. "form_submission_id = " . $sub_id;
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " 
				. $sql_str . "\n". pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_files = pg_num_rows($result);
	// store result as name-value pairs
	// where the file name is the name and
	// the file id is the value
	for($i = 0; $i < $n_files; $i++)
		$files[pg_fetch_result($result, $i, 'file_name')] = 
						pg_fetch_result($result, $i, 'file_submission_id');
	$dbconn->disconnect();
	return $files;	
}

///
/// get_layers()
/// get a list of layers belonging to the 
/// given submission
///
function &get_layers($sub_id){
	$layers = array();
	$sql_str = "SELECT "
				. "layer_id, "
				. "layer_name "
			. "FROM "
				. "tng_spatial_layer "
			. "WHERE "
				. "form_submission_id = " . $sub_id;
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " 
				. $sql_str . "\n". pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_layers = pg_num_rows($result);
	// store result as name-value pairs
	// where the layer_name is the name and
	// the layer id is the value
	for($i = 0; $i < $n_layers; $i++)
		$layers[pg_fetch_result($result, $i, 'layer_name')] = 
						pg_fetch_result($result, $i, 'layer_id');
	$dbconn->disconnect();
	return $layers;
}
///
/// get_form_id()
/// get form id based on the
/// submission id passed in
///
function get_form_id($submission_id){
	$sql_str = "SELECT "
				. "form_id "
			. "FROM "
				. "tng_form_submission "
			. "WHERE form_submission_id = " . $submission_id;
	
	$dbconn =& new DBConn();
	if($dbconn == NULL)
		die('Could not create connection object');			

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);

	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return -1;
	}
	$form_id = pg_fetch_result($result, 0, 0);
	$dbconn->disconnect();
	return $form_id;
}

///
/// update_sub_name()
/// update the name of a submission
///
function update_sub_name($sub_id, $sub_name){
	$sql_str = "UPDATE "
					. "tng_form_submission "
				. "SET "
					. "submission_name = '" . $sub_name . "' "
				. "WHERE "
					. "form_submission_id = " . $sub_id;
					
	$dbconn =& new DBConn();
	if($dbconn == NULL)
		die('Could not create connection object');			
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$dbconn->disconnect();
	return true;
}

///
/// update_sub_status()
/// update the status of a submission
///
function update_sub_status($sub_id, $status_id){
	$sql_str = "UPDATE "
					. "tng_form_submission "
				. "SET "
					. "status_id = " . $status_id . " "
				. "WHERE "
					. "form_submission_id = " . $sub_id;
					
	$dbconn =& new DBConn();
	if($dbconn == NULL)
		die('Could not create connection object');			
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$dbconn->disconnect();
	return true;
}

///
/// update_sub_asignee()
/// update who the submission is assigned to
///
function update_sub_asignee($sub_id, $uid_assigned){
	$uid = "NULL";
	if($uid_assigned != -1)
		$uid = $uid_assigned;
		
	$sql_str = "UPDATE "
					. "tng_form_submission "
				. "SET "
					. "uid_assigned = " . $uid . " "
				. "WHERE "
					. "form_submission_id = " . $sub_id;
					
	$dbconn =& new DBConn();
	if($dbconn == NULL)
		die('Could not create connection object');			
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$dbconn->disconnect();
	return true;
}

?>