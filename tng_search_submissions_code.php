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

$list_sub_types =& get_sub_types();
$list_sub_statuses =& get_sub_statuses();
$list_sub_asignees =& get_sub_asignees();
$list_sub_users =& get_sub_users();

// form is being loaded first time or
// it is being loaded through ajax
if(isset($_SESSION['obj_login'])){
	global $list_sub_types;
	global $list_sub_statuses;
	global $list_sub_asignees;
	
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// clear lists held in session 
			// variable and 
			// re read all lists from the db
			case "refresh_lists":
				unset($_SESSION['list_sub_types']);
				unset($_SESSION['list_sub_statuses']);
				unset($_SESSION['list_sub_asignees']);
				unset($_SESSION['list_sub_users']);
				$list_sub_types =& get_sub_types();
				$list_sub_statuses =& get_sub_statuses();
				$list_sub_asignees =& get_sub_asignees();
				$list_sub_users =& get_sub_users();
			break;
			// request for all submission types
			case "get_sub_types":
				$xml = generate_object_list_xml($list_sub_types);
				//$html = generate_html($xml, $xslt_user);
				echo $xml;
			break;
			// request for a list of submission statuses
			case "get_sub_statuses":
				$xml = generate_object_list_xml($list_sub_statuses);
				//$html = generate_html($xml, $xslt_user);
				echo $xml;
			break;
			// request for a list of asignees
			case "get_sub_asignees":
				$xml = generate_object_list_xml($list_sub_asignees);
				//$html = generate_html($xml, $xslt_user);
				echo $xml;
			break;
			// request for a list of users who've made a submission
			case "get_sub_users":
				$xml = generate_object_list_xml($list_sub_users);
				//$html = generate_html($xml, $xslt_user);
				echo $xml;
			break;
			// request for list of searchable fields for a
			// specific form.
			case "get_spec_field":
				$form_id = $_POST['form_id'];
				$searchable_fields =& get_searchable_fields($form_id);
				$xml = generate_object_list_xml($searchable_fields);
				//$html = generate_html($xml, $xslt_user);
				echo $xml;
			break;
			// perform a search
			// use strip slashes to escape backslashes added
			// by http to escape single quotes.
			case "perform_search":
				$where_clause = stripslashes($_POST['where_clause']);
				//$where_clause = str_replace($tmp, "\\", "");
				$xml = perform_search($where_clause);
				echo $xml;
			break;
		}
	}
}

///
/// get_sub_types()
/// get the names of the different
/// forms from the db.
///
function &get_sub_types(){
	if(!isset($_SESSION['list_sub_types'])){
		$sub_types = array();
		$sql_str = "SELECT "
						. "form_id, "
						. "form_name "
					. "FROM "
						. "tng_form ";
						
		$dbconn =& new DBConn();
		$dbconn->connect();
		$result = pg_query($dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query " 
					. pg_last_error($dbconn->conn);
			$dbconn->disconnect();
			return NULL;
		}
		// create one entry for all types
		// of submissions
		$sub_types['All'] = -1;
		
		$n_types = pg_num_rows($result);
		// store result as name-value pairs
		// where the form name is the name and
		// the form id is the value
		for($i = 0; $i < $n_types; $i++){
				$sub_types[pg_fetch_result($result, $i, 'form_name')] = 
								pg_fetch_result($result, $i, 'form_id');
		}
		$dbconn->disconnect();
		// set the session variable
		$_SESSION['list_sub_types'] = $sub_types;				
	}
	return $_SESSION['list_sub_types'];
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
		$sub_statuses['All Statuses'] = -1;
		
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
/// get_sub_asignees()
/// get the list of users
/// that have a submission
/// assigned to them
///
function &get_sub_asignees(){
	if(!isset($_SESSION['list_sub_asignees'])){
		$sub_asignees = array();
		$sql_str = "SELECT "
						. "tng_user.uid, "
						. "tng_user.uname "
					. "FROM "
						. "tng_form_submission "
						. "INNER JOIN tng_user " 
							."ON tng_form_submission.uid_assigned = tng_user.uid";
						
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
		$sub_asignees['unassigned'] = -1;
		
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
/// get_sub_users
/// get the list of users
/// that have made a submission
///
function &get_sub_users(){
	if(!isset($_SESSION['list_sub_users'])){
		$sub_users = array();
		$sql_str = "SELECT "
						. "tng_user.uid, "
						. "tng_user.uname "
					. "FROM "
						. "tng_form_submission "
						. "INNER JOIN tng_user " 
							."ON tng_form_submission.uid = tng_user.uid";
						
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
		$sub_users['All Users'] = -1;
		
		$n_users = pg_num_rows($result);
		// store result as name-value pairs
		// where the uname is the name and
		// the uid is the value
		for($i = 0; $i < $n_users; $i++){
				$sub_users[pg_fetch_result($result, $i, 'uname')] = 
								pg_fetch_result($result, $i, 'uid');
		}
		$dbconn->disconnect();
		// set the session variable
		$_SESSION['list_sub_users'] = $sub_users;				
	}
	return $_SESSION['list_sub_users'];
}

///
/// get_searchable_fields()
/// get a list of searchable fields for a 
/// specific form.
///
function &get_searchable_fields($form_id){
	$fields = array();
	$sql_str = "SELECT "
					. "field_id, "
					. "field_name "
				. "FROM "
					. "tng_form_field "
				. "WHERE "
					. "form_id = " . $form_id . " "
				. "AND "
					. "field_searchable = true";
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " 
				. $sql_str . "\n". pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}

	$n_fields = pg_num_rows($result);
	// store result as name-value pairs
	// where the field name is the name and
	// the field id is the value
	for($i = 0; $i < $n_fields; $i++){
			$fields[pg_fetch_result($result, $i, 'field_name')] = 
							pg_fetch_result($result, $i, 'field_id');
	}
	return $fields;				
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
				. "vi_submission_search.sub_id, "
				. "sub_type, "
				. "sub_title, "
				. "sub_name, "
				. "sub_status, "
				. "submitted_by, "
				. "assigned_to, "
				. "sub_date "
			. "FROM "
				. "vi_submission_search ";
	// if the user wishes to include a specific field
	// search, include the view that accomplishes
	// this.
	if(substr_count($where_clause, "field_id") == 1)
		$sql_str .= "INNER JOIN vi_field_search "
						. "ON vi_submission_search.sub_id = vi_field_search.sub_id ";

	// if the user is not in the tng
	// group, then only show submissions made
	// by users in the same group(s) as the
	// logged in user.
	if(!$_SESSION['obj_login']->is_tng_user()){
		$where_clause .= "AND "
						. "uid_submitted IN ("
							. "SELECT "
						    	. "DISTINCT tng_group_users.uid "
							. "FROM " 
								. "tng_group_users "
							. "WHERE "  
								. "gid IN (" 
									. "SELECT " 
										. "gid " 
									. "FROM " 
										. "tng_group_users " 
									. "WHERE " 
										. "uid = " . $_SESSION['obj_login']->uid . " "
									. ")"
							. ")";
	}
						
	$sql_str .= $where_clause;
	
	//echo $sql_str;
	//return;
		
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
?>