<?php
/*---------------------------------------------------------------
author:	alim karim
date:	July 26, 2007
file:	tng_view_form_details_code.php

desc:	backend script to respond to requests made
		for details about a form.
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');
include_once('classes/class_form.php');
session_start();

// form is being loaded first time or
// it is being loaded through ajax
if(!isset($_SESSION['obj_login'])){
	echo "Please Login before proceeding";
}else{	
	if(isset($_POST['ajax_action'])){		
		switch($_POST['ajax_action']){
			// caller wants list of all available
			// forms
			case "get_forms":
				$form_list = array();
				$xml = "";
			 	if(get_form_list($form_list))
					$xml = convert_form_list_to_xml($form_list);
				echo $xml;
			break;
			// the caller wishes get details
			// on a particular form
			case "get_form_details":
				$form_id = $_POST['form_id'];
				$xml = get_form_details($form_id);
				echo $xml;
			break;
			case "toggle_searchable":
				toggle_searchable($_POST['field_id'], $_POST['searchable']);
			break;
		}
	}
}


///
/// get_form_list()
/// get list of available form in the db.
/// store results in the array as
/// array[form_name] = form_id
///
function get_form_list(&$form_list){
	$sql_str = "SELECT "
					. "form_id, "
					. "form_name "
				. "FROM "
					. "tng_form "
				. "ORDER BY "
					. "form_name";
					
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$n_forms = pg_num_rows($result);
	
	for($i = 0; $i < $n_forms; $i++)
		$form_list[pg_fetch_result($result, $i, 'form_name')] = 
							pg_fetch_result($result, $i, 'form_id');
							
	$dbconn->disconnect();
	return true;
}

///
/// convert_form_list_to_xml()
/// convert form list to xml representation
///	<form>
///		<id>	1	</id>
///		<name> abc </name>
///	</form>
///
function convert_form_list_to_xml($form_list){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
	$n_forms = count($form_list);
	$form_names = array_keys($form_list);
	$xml .= "<forms>";
	// note: the javascript xml parser is picky
	// about the newline character, so a \n after
	// opening <schema> will actually register as
	// a child of <schema>
	for($i = 0; $i < $n_forms; $i++)
		$xml .= "<form>"
				. "<id>" . $form_list[$form_names[$i]] . "</id>"
				. "<name>" . $form_names[$i] . "</name>"
				. "</form>\n";
	$xml .= "</forms>";
	return $xml;
}

///
/// get_form_details()
/// get xml representation of a particular
/// form using the Form class method
/// generate_xml. see class for details and
/// schema.
///
function get_form_details($form_id){
	$xml = "";
	$form =& new Form($form_id);
	$xml = $form->generate_xml();
	return $xml;
}

///
/// toggle_searchable()
/// toggle the searchable field for a 
/// particular field.
///
function toggle_searchable($field_id, $searchable){
	$sql_str = "UPDATE "
					. "tng_form_field "
				. "SET "
					. "field_searchable = " . $searchable . " "
				. "WHERE "
					. "field_id = " . $field_id;

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