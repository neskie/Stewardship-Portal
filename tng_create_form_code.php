<?php
/*---------------------------------------------------------------
author:	alim karim
date:	May 14, 2007
file:	tng_create_schema_code.php

desc:	backend script to create a spatial
		schema
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');

session_start();

// form is being loaded first time or
// it is being loaded through ajax
if(!isset($_SESSION['obj_login'])){
	echo "Please Login before proceeding";
	return;
}else{	
	if(isset($_POST['ajax_action'])){
		switch($_POST['ajax_action']){
			// check if a form exists
			// with the same name
			case "check_form_name":
				$result = check_form_name($_POST['form_name']);
				if($result)
					echo "true";
				else
					echo "false";
			break;
			// the caller wishes to create
			// a form
			case "create_form":
				$form_name = $_POST['form_name'];
				$n_fields = $_POST['n_fields'];
				$result = create_form($form_name, $n_fields, $_POST);
				if($result)
					echo "true";
				else
					echo "false";
			break;
		}
	}
}


///
/// check_form_name()
/// check if schema name already
/// exists
///
function check_form_name($form_name){
	$form_exists = true;
	
	$sql_str = "SELECT "
					. "form_id "
				. "FROM "
					. "tng_form "
				. "WHERE "
					. "form_name = '" . $form_name . "'";
	$dbconn =& new DBConn();

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	if(pg_num_rows($result) == 0)
		$form_exists = false;

	$dbconn->disconnect();
	
	return $form_exists; 
}

///
/// create_form()
/// create a new form
///
function create_form($form_name, $n_fields, $post_vars){
	$result = false;
	$fields = array();
	// populate fields array with 
	// field_name and field type
	// pairs
	collect_fields($fields, $n_fields, $post_vars);
	$view_name = "vi_" . $schema_name;
	$attr_table_id = -1;
	if(($form_id = create_form_record($form_name, $fields)) != -1)
			$result = true;
	return $result;
}

///
/// collect_fields
/// go through the post variables
/// and extract fields that the user
/// created.
/// these are expected to be in the form
/// field_1_name = xxx 
/// field_1_type = yyy
///
function collect_fields(&$fields, $n_fields, $post_vars){
	$prefix = "field_";
	$name_suffix = "_name";
	$type_suffix = "_type";
	$rank_suffix = "_rank";	
	$searchable_suffix = "_searchable";
	for($i = 0; $i < $n_fields; $i++){
		$f_name = $post_vars[$prefix . $i . $name_suffix];
		// each field element is an array 
		// where:
		// [0] -> type and
		// [1] -> searchable/not searchable
		// [2] -> field_label
		// [3] -> field_rank
		$fields[$f_name] = array();
		$fields[$f_name]['type'] = $post_vars[$prefix . $i . $type_suffix];
		$fields[$f_name]['searchable'] = $post_vars[$prefix . $i . $searchable_suffix];
		$fields[$f_name]['rank'] = $post_vars[$prefix . $i . $rank_suffix];
		$fields[$f_name]['field_css_class'] = "";
		$fields[$f_name]['field_label_css_class'] = "lbl_regular";
		// set css style based on type
		if($fields[$f_name]['type'] == "text"){
			$fields[$f_name]['field_css_class'] = "form_text_area";
		}else if($fields[$f_name]['type'] == "checkbox"){
			$fields[$f_name]['field_label_css_class'] = "lbl_checkbox";
		}
	}
}

///
/// create_form_record()
/// create a record in the tng_form table
/// and get the id for the record.
/// once the record is created,
/// we can proceed to create the form field
/// entries in tng_form_field
///
function create_form_record($form_name, $fields){
	$form_id = -1;
	$sql_str = "INSERT INTO " 
					. "tng_form "
					. "("
						. "process_id, "
						. "form_name "
					. ") "
					. "VALUES "
					. "("
						. "1, "
						. "'" . $form_name . "' "
					. "); "
					. "SELECT "
						. "max(form_id) "
					. "FROM "
						. "tng_form ";
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return -1;
	}
	
	$form_id = pg_fetch_result($result, 0, 0);
	$dbconn->disconnect();
	
	// now insert the individual
	// fields into the
	// tng_form_field table
	$field_names = array_keys($fields);
	$n_fields = count($field_names);
	$dbconn->connect();
	for($i = 0; $i < $n_fields; $i++){
		$sql_str = "INSERT INTO "
						. "tng_form_field "
						. "("
							. "form_id, "
							. "field_name, "
							. "field_label, "
							. "field_type, "
							. "field_rank, "
							. "field_searchable, "
							. "field_label_css_class, "
							. "field_css_class "							
						. ") "
						. "VALUES "
						. "("
							. $form_id . ", "
							. "'" . $field_names[$i] . "', "
							. "'" . $field_names[$i] . "', "
							. "'" . $fields[$field_names[$i]]['type'] . "', "
							. $fields[$field_names[$i]]['rank'] . ", "
							. $fields[$field_names[$i]]['searchable'] . ", "
							. "'" . $fields[$field_names[$i]]['field_label_css_class'] . "', "
							. "'" . $fields[$field_names[$i]]['field_css_class'] . "' "
						. ")";
		
		$result = pg_query($dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
			$dbconn->disconnect();
			return -1;
		}
	}
	$dbconn->disconnect();
	
	return $form_id;
}

?>