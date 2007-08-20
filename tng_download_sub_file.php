<?php
/*---------------------------------------------------------------
author:	alim karim
date:	July 3, 2007
file:	tng_download_sub_file.php

desc:	code to download a file or a layer associated
		with a submission.
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');
include_once('classes/class_submission_file.php');
include_once('classes/class_submission_layer.php');
// check session before anything
session_start();
if(!isset($_SESSION['obj_login']))
	return;

if(isset($_GET['type'])){
	$obj_id = $_GET['id'];
	switch($_GET['type']){
		case "file":
			$sub_file =& new Submission_File($obj_id);
			if($sub_file == NULL){
				echo "could not create submission file object";
				return;
			}
			download_file($sub_file->file_path, $sub_file->file_name);
		break;
		
		case "layer":
			$sub_layer =& new Submission_Layer($obj_id);
			if($sub_layer == NULL){
				echo "could not create layer file object";
				return;
			}
			$zip_file = $sub_layer->convert_to_shapefile();
			if($zip_file == NULL){
				echo "could not extract layer to shapefile";
				return;
			}
			download_file($zip_file, basename($zip_file));
		break;
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