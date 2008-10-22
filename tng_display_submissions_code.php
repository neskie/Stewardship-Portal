<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_display_form.php

desc:	code behind submission rendering page.
		it generates an array of submission
		objects. for each submission, it gets the
		form object, the files and the spatial
		layers associated with the submission

notes:
		2008.10.22
		added ogr2ogr argument to submission layer constructor
		call. the value is read from app_config session variable
---------------------------------------------------------------*/
include_once('classes/class_form.php');
include_once('classes/class_login.php');
include_once('classes/class_app_config.php');
include_once('classes/class_submission.php');

session_start();
error_reporting(E_ALL);

$xml_data = "";
$xslt_file = "tng_submission_transform.xslt";
$generated_submission_html = "";

///
/// main method
///
if(!isset($_POST['elt_selected'])){ // first time form is being loaded
	global $xml_data;
	global $xslt_file;
	global $generated_submission_html;
	$submissions = array();
	// begin by querying for all 
	// submissions having no parent submissions
	// and order them by the date of the
	// submission.
	$sql_str = "SELECT "
				. "form_submission_id "
			."FROM "
				. "tng_form_submission "
			. "WHERE "
				. "pid = -1 "
			. "ORDER BY "
				. "form_submission_time DESC "
			. "LIMIT 50";
	
	$dbconn = new DBConn();
	if($dbconn == NULL)
		die('Could not create connection object');			
	
	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);

	if(!$result){
		echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}

	// successfuly ran the query
	// get the list of submissions and
	// create an object for each. each
	// submission object in turn creates
	// its own file and layer objects
	$n_submissions = pg_num_rows($result);
	for($i = 0; $i < $n_submissions; $i++){
		$submissions[$i] = new Submission(pg_fetch_result($result, $i, 0), 
										$_SESSION['app_config']->ogr2ogr_path);
		if($submissions[$i] == NULL){
			echo "could not create submission object";
			return false;
		}
		$submissions[$i]->load_sub_details();
	}

	//$dbconn->disconnect();
	
	// now that all the submission objects are
	// in a local array, loop through and produce
	// an xml representation of the submissions
	
	$xml_data = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
			. "<submissions>\n";
	
	for($i = 0; $i < $n_submissions; $i++)
		$xml_data .= $submissions[$i]->generate_xml() . "\n";
	
	$xml_data .= "</submissions>";
	
	// read in xslt file	
	$file = fopen($xslt_file, "r");
	$xslt_data = fread($file, filesize($xslt_file));
	fclose($file);
	
	// for debugging only - write out xml to file
	//$file = fopen("/tmp/xml.txt", "w");
	//fwrite($file, $xml_data);
	//fclose($file);
	
	// create new xlst processor
	$xslt_processor = xslt_create();
	// store array arguments to be passed
	// to the xslt processor
	// see http://ca3.php.net/manual/en/function.xslt-process.php
	// for further explanation
	$xslt_args = array('/_xml' => $xml_data, '/_xsl' => $xslt_data);
	$generated_submission_html = xslt_process($xslt_processor, 'arg:/_xml', 'arg:/_xsl', NULL, $xslt_args);
	xslt_free($xslt_processor);

}else{ // the page has been posted back
	// check what type of link was clicked by the user;
	$selection_type = $_POST['elt_selected'];
	$id = $_POST['elt_id'];
	// the user wishes to download a regular file
	if($selection_type == "file"){
		$sub_file =& new Submission_File($id);
		if($sub_file == NULL){
			echo "could not create submission file object";
			return;
		}
		download_file($sub_file->file_path, $sub_file->file_name);
	}else if($selection_type == "layer"){
		// the user wishes to download a layer
		$sub_layer =& new Submission_Layer($id);
		if($sub_layer == NULL){
			echo "could not create submission file object";
			return;
		}
		
		$zip_file = $sub_layer->convert_to_shapefile();
		if($zip_file == NULL){
			echo "could not extract layer to shapefile";
			return;
		}
		download_file($zip_file, basename($zip_file));
	}else if($selection_type == "form"){
		// user wishes to view the data (fields)
		// in the form.
		// set session variables required by the
		// display_form form
		// note that this has to be done by session
		// variables because when we use the
		// header function, POST variables are
		// NOT transmitted to the php page
		// that we are redirecting to.
		$_SESSION['form_id'] = get_form_id($id);
		$_SESSION['submission_id'] = $id;
		$_SESSION['readonly'] = 'true';
		header("Location: tng_display_form.php");
	}
}

///
/// download_file()
/// given the path to a file, download that 
/// file for the user
///
function download_file($file_path, $file_name){
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=" . $file_name);
	header("Content-Transfer-Encoding: binary");
	readfile($file_path);
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
	
	$dbconn = new DBConn();
	if($dbconn == NULL)
		die('Could not create connection object');			

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);

	if(!$result){
		echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
		
	$form_id = pg_fetch_result($result, 0, 0);
	
	$dbconn->disconnect();
	
	return $form_id;
}

?>
