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
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// request for a list of available
			// forms
			case "get_form_list":
				$form_list = get_form_list();
				$xml = generate_object_list_xml($form_list, "");
				echo $xml;
			break;
			// get the schemas for a particular
			// form
			case "get_schemas":
				$obj_set = $_POST['ajax_object_set'];
				$form_id = $_POST['form_id'];
				$schema_list = array();
				// now check if the request was
				// for the set of linked schemas
				// or unlinked schemas. 
				// call appropriate method accordingly
				switch ($obj_set){
					case "linked":
						get_schemas($schema_list, $form_id, true);
					break;
					case "unlinked":
						get_schemas($schema_list, $form_id, false);
					break;
				}
				
				$xml = generate_object_list_xml($schema_list, "");
				echo $xml;	
			break;
			// the user wishes to link a schema
			// to a form 
			case "link":
				$attr_table_id = $_POST['schema_id'];
				$form_id = $_POST['form_id'];
				toggle_schema_linkage($attr_table_id, $form_id, "link");
				// nothing echoed back
			break;
			// request to unlink a schema from
			// a form
			case "unlink":
				$attr_table_id = $_POST['schema_id'];
				$form_id = $_POST['form_id'];
				toggle_schema_linkage($attr_table_id, $form_id, "unlink");
				// nothing echoed back
			break;
		}
	}
}

///
/// get_form_list()
/// get all forms in db
///
function &get_form_list(){
	$form_list = array();
	
	$sql_str = "SELECT "
					. "form_id, " 
					. "form_name "
				. "FROM " 
					. "tng_form ";
					
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	// create a blank entry
	$form_list['Select a Form'] = -1;
	
	$n_forms = pg_num_rows($result);
	// populate user_list array
	// as name-value pairs
	// i.e. the user name is the name, and the
	// user id is the value
	for($i = 0; $i < $n_forms; $i++)
		$form_list[pg_fetch_result($result, $i, 'form_name')]  = 
												pg_fetch_result($result, $i, 'form_id');
	$dbconn->disconnect();
	return $form_list;
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
/// get_schemas()
/// get schemas associated with a form.
/// the $linked param decides whether to
/// query for linked or unlinked schemas
///
function get_schemas(&$array, $form_id, $linked){
	$sql_str = "SELECT "
				. "tng_spatial_attribute_table.attr_table_id, "
				. "tng_spatial_attribute_table.attr_table_name "
			. "FROM "
				. "tng_spatial_attribute_table "
				. "INNER JOIN tng_form_spatial_data " 
					. "ON tng_spatial_attribute_table.attr_table_id = tng_form_spatial_data.attr_table_id "
			. "WHERE "
				. "tng_form_spatial_data.form_id = " . $form_id;
	// note:
	// the EXCEPT operator returns all
	// records in the first result that are
	// not present in the second result
	if(!$linked)
		$sql_str =  "SELECT "
				      	. "tng_spatial_attribute_table.attr_table_id, "
						. "tng_spatial_attribute_table.attr_table_name "
					. "FROM "
		    			. "tng_spatial_attribute_table "
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
	$n_schemas = pg_num_rows($result);
	// populate array
	// as name-value pairs
	// i.e. the schema name is the key, and the
	// schema id is the value
	for($i = 0; $i < $n_schemas; $i++)
		$array[pg_fetch_result($result, $i, 'attr_table_name')] = 
												pg_fetch_result($result, $i, 'attr_table_id');

	$dbconn->disconnect();
}


///
/// toggle_schema_linkage()
/// link/unlink a schema and a form
/// 
function toggle_schema_linkage($attr_table_id, $form_id, $action){
	$sql_str = "";
	if($action == "link")
		$sql_str = "INSERT INTO tng_form_spatial_data "
					. "( "
						. "form_id, "
						. "attr_table_id"
					. ") "
					. "VALUES "
					. "("
						. $form_id . ", "
						. $attr_table_id 
					. ")";
	else
		$sql_str = "DELETE "
					. "FROM "
						. "tng_form_spatial_data "
					. "WHERE "
						. "form_id = " . $form_id . " "
					. "AND "
						. "attr_table_id = " . $attr_table_id;
						
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