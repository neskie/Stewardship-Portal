<?php
/*---------------------------------------------------------------
author:	alim karim
date:	nov 23, 2007
file:	cstc_csgts.php

desc:	server side script for the carrier sekanni 
		geospatial toolset interface.
		the client makes requests to this script through
		ajax
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_submission_layer.php');
session_start();

if (isset($_GET['ajax_req'])){
	switch ($_GET['ajax_req']){
		// user wishes to obtain available
		// layers for operations
		case "get_layers":
			$layers = get_layers($_SESSION['obj_login']);
			echo  json_encode($layers); //print_r($layers); //
		break;
	}
}else if(isset($_GET['download_file'])){
	// only permit the download if the 
	// file being requested was the zip
	// file that was saved 
	if(isset($_SESSION['output_file']) && substr_count($_GET['download_file'], $_SESSION['output_file']) == 1)
		download_file($_GET['download_file']);
}else if (isset($_POST['ajax_req'])){
	switch ($_POST['ajax_req']){
		// user wishes to perform an operation
		case "perform_op":
			$operation = strtolower($_POST['operation']);
			$buffer_dist = $_POST['buffer_dist'];
			$layer2 = null;
			
			$layer1 = new Submission_Layer($_POST['layer1']);
			if($_POST['layer2'] != "")
				$layer2 = new Submission_Layer($_POST['layer2']);
				
			// make sure all backslashes inserted
			// while transporting the json are
			// replaced. otherwise, json_decode 
			// will not work.
			$layer1_attrs = json_decode(str_replace("\\", "", $_POST['l1_attrs']), true);
			$layer2_attrs = json_decode(str_replace("\\", "", $_POST['l2_attrs']), true);
			
			// call method to perform the operation
			$output = perform_operation($layer1, $layer2, $layer1_attrs, $layer2_attrs, $buffer_dist, $operation);
			// extract the results as a shapefile
			$zipfile = "";
			if($output != null)
				$zipfile = export_to_shapefile($output, $output);
			// note that the output name is saved
			// as a session variable so that when 
			// the user wishes to download the
			// resultant file, the name of the file
			// is checked. this prevents the user
			// from supplying any file path and
			// arbitrarily downloading a file
			$_SESSION['output_file'] = $zipfile;
	
			// drop the table that was created
			drop_table($output);
			echo $zipfile;
			break;
	}
}

///
/// get_layers()
/// make a connection to the db
/// and discover what layers are
/// available to this user
///
function get_layers($login){
	$dbconn =& new DBConn();
	$layers = array();
	
	$sql_str =	"SELECT "
						. "DISTINCT " 
						. "tng_spatial_layer.layer_id, "
						. "tng_spatial_layer.layer_name, "
						. "tng_spatial_data.geometry_type "
					. "FROM "
						. "tng_spatial_layer "
						. "INNER JOIN tng_submission_permission ON " 
									. "tng_spatial_layer.form_submission_id = tng_submission_permission.sub_id "
						. "INNER JOIN tng_spatial_attribute_table ON " 
									. "tng_spatial_layer.attr_table_id = tng_spatial_attribute_table.attr_table_id "
						. "INNER JOIN tng_spatial_data ON " 
									. "tng_spatial_attribute_table.spatial_table_id = tng_spatial_data.spatial_table_id ";
		if(!$login->is_tng_user())
			$sql_str .= "WHERE "
							. "tng_submission_permission.uid = " . $login->uid;
	
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
				. $sql_str ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_layers = pg_num_rows($result);
	for($i = 0; $i < $n_layers; $i++){
		$layers[$i] = array(
							"id" => pg_fetch_result($result, $i, 'layer_id'),
							"lname" => pg_fetch_result($result, $i, 'layer_name'),
							"geom_type" => pg_fetch_result($result, $i, 'geometry_type'),
							"attrs" => get_attributes(pg_fetch_result($result, $i, 'layer_id'))
							);
		
	}
	//$dbconn->disconnect();
	return $layers;
}

///
/// get_attributes()
/// get the attributes of a 
function get_attributes($layer_id){
	$dbconn =& new DBConn();
	$attrs = array();
	$sql_str = "SELECT "
				. "tng_spatial_attribute.attr_id, "
				. "tng_spatial_attribute.attr_name, "
				. "tng_spatial_attribute.attr_type "
			. "FROM "
				. "tng_spatial_layer "
				. "INNER JOIN tng_spatial_attribute "
					. "ON tng_spatial_layer.attr_table_id = tng_spatial_attribute.attr_table_id "
			. "WHERE "
				. "tng_spatial_layer.layer_id = " . $layer_id;
	
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$n_attrs = pg_num_rows($result);
	for($i = 0; $i < $n_attrs; $i++){
		$attrs[$i] = array(
							"id" => pg_fetch_result($result, $i, 'attr_id'),
							"attr_name" => pg_fetch_result($result, $i, 'attr_name'),
							"attr_type" => pg_fetch_result($result, $i, 'attr_type')
							);
		
	}
	$dbconn->disconnect();
	return $attrs;
}

///
///
///
function perform_operation($layer1, $layer2, $layer1_attrs, $layer2_attrs, $buffer_dist, $operation){
	// generate a random name for 
	// the resultant table
	$output = "op_" . uniqid(true);
	$dbconn =& new DBConn();
	$dbconn->conn_str = $dbconn->schema_creator_conn_str; 
	$sql_str = "";
	switch($operation){
		case "intersect":
			$sql_str = intersect_sql($layer1, $layer2, $layer1_attrs, $layer2_attrs, $output);
		break;
		
		case "clip":
			$sql_str = clip_sql($layer1, $layer2, $layer2_attrs, $output);
		break ;
		
		case "buffer":
			$sql_str = buffer_sql($layer1, $layer1_attrs, $buffer_dist, $output);
		break;
		
		default:
			$sql_str = "no op";
		break;
	}
	
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
				. $sql_str ." - " 
				. pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return null;
	}
	$dbconn->disconnect();
	return $output;
}

///
/// intersect_sql()
/// generate sql to intersect layer1 with layer2,
/// preserving the given attributes
///
function intersect_sql($layer1, $layer2, $layer1_attrs, $layer2_attrs, $output){
	// creating unique aliases for
	// the layer names takes care of
	// trying to operate on layers with
	// the same schema
	$layer1_alias = $layer1->view_name . "_l1";
	$layer2_alias = $layer1->view_name . "_l2";
	$sql_str = "BEGIN TRANSACTION; " 
				. "CREATE TABLE " .$output . " AS "
					. "SELECT "
					. "intersection(" . $layer1_alias . ".the_geom, " .  $layer2_alias . ".the_geom) AS the_geom ";
	
	if(count($layer1_attrs) > 0 )
		$sql_str .= ", ";
	// append selected layer 1 attributes 
	// to string
	$sql_str = util_append_attributes($sql_str, $layer1_alias, $layer1_attrs);
	if(count($layer2_attrs) > 0)
		$sql_str .= ", ";
	$sql_str = util_append_attributes($sql_str, $layer2_alias, $layer2_attrs);
	
	$sql_str .= "FROM "
				. $layer1->view_name . " AS " . $layer1_alias . ", "
				. $layer2->view_name . " AS " . $layer2_alias . " "
			. "WHERE "
				. $layer1_alias . ".layer_id = " . $layer1->layer_id . " "
				. "AND "
				. $layer2_alias . ".layer_id = " . $layer2->layer_id . " "
				. "AND intersects(" . $layer1_alias . ".the_geom, " .  $layer2_alias . ".the_geom); "
			. "COMMIT TRANSACTION;";
	return $sql_str;
}

///
/// clip_sql()
/// generate sql statement for clipping
/// layer2 with layer1 i.e. layer1 is 
/// the overlay. note that we do not
/// concern ourselves with the overlay
/// layer attributes.
///
function clip_sql($layer1, $layer2, $layer2_attrs, $output){
	$sql_str = "BEGIN TRANSACTION; " 
				. "CREATE TABLE " .$output . " AS "
					. "SELECT "
					. "intersection(vlyr.the_geom, " .  $layer2->view_name . ".the_geom) AS the_geom ";
	
	if(count($layer2_attrs) > 0)
		$sql_str .= ", ";
	// append selected layer 2 attributes 
	// to string
	$sql_str = util_append_attributes($sql_str, $layer2->view_name, $layer2_attrs);
	// note the use of the snaptogrid function.
	// it is used here because geos throws exceptions
	// when performing operations on certain
	// geometries due to srid transformations.
	// see http://postgis.refractions.net/pipermail/postgis-users/2006-October/013529.html
	$sql_str .= "FROM "
				. "(SELECT GeomUnion(snaptogrid(the_geom, 0.0001)) AS the_geom " 
				. "FROM " . $layer1->view_name . " WHERE " . $layer1->view_name . ".layer_id = " . $layer1->layer_id . ") as vlyr, "
				. $layer2->view_name . " "
			. "WHERE "
				. $layer2->view_name . ".layer_id = " . $layer2->layer_id . " "
				. "AND intersects(vlyr.the_geom, ".  $layer2->view_name . ".the_geom); "
			. "COMMIT TRANSACTION; ";
	return $sql_str;
}

///
/// buffer()
/// generate sql statement to buffer
/// the given layer by the given
/// distance.
///
function buffer_sql($layer, $attributes, $buffer_dist, $output){
	$sql_str = "BEGIN TRANSACTION; " 
				. "CREATE TABLE " .$output . " AS "
					. "SELECT "
						. "buffer(" . $layer->view_name . ".the_geom, " . $buffer_dist . ") AS the_geom ";
	if(count($attributes) > 0)
		$sql_str .= ", ";
	// append selected attributes 
	// to string
	$sql_str = util_append_attributes($sql_str, $layer->view_name, $attributes);
	$sql_str .= "FROM "
					. $layer->view_name . " "
				. "WHERE "
					. $layer->view_name . ".layer_id = " . $layer->layer_id . "; "
			. "COMMIT TRANSACTION; ";
					
	return $sql_str;
}

///
/// util_append_attributes
/// append items to a select list in an
/// sql statement
///
function util_append_attributes($sql_string, $layer_name, $attributes){
	$new_str = $sql_string;
	$n_attrs = count($attributes);
	for($i = 0; $i < $n_attrs; $i++){
		$new_str .= $layer_name . "." . $attributes[$i]['name']. " ";
		if($i < $n_attrs - 1)
			$new_str .= ", ";
	}
	return $new_str;
}

///
/// export_to_shapefile()
/// call ogr2ogr to dump the table into 
/// a shapefile
///
function export_to_shapefile($tablename, $output_file_name){
	$dbconn =& new DBConn();
	$dbconn->schema_creator_conn_str;
	$ogr_bin = "/usr/bin/ogr2ogr";
	$ogr_bin = "/usr/local/bin/ogr2ogr";
	$output_dir = "/tmp/";
	// build a call out string to OGR
	// that looks something like:
	// ogr2ogr 
	// 		-f "ESRI Shapefile" 
	// 		-sql "select * $tablename 
	//		/tmp/tng_port.shp 
	//		PG:'host=ninkasi dbname=tng_dev user=tng_readwrite password=tng_readwrite'
	// note that we cant simply supply the table
	// name because the table is not recorded in the
	// geometry columns table and so 
	// ogr will not do anything
	$exec_str = "cd " .$output_dir . "; "
				. "rm " . $output_file_name . ".*; "
				. $ogr_bin . " "
				. "-f \"ESRI Shapefile\" "
				. "-sql 'SELECT * FROM " . $tablename . "' " 
				. "/tmp/". $output_file_name . ".shp" . " "
				. "PG:'" . $dbconn->schema_creator_conn_str . "' "
				. $tablename;
		
		// execute the command
		exec($exec_str, $output);
		// check if shapefile was created
		if(!file_exists($output_dir . $output_file_name . ".shp")){
			echo "could not create output shapefile using: " . $exec_str . "error: " . $output;
			return null;
		}
		// ogr2ogr successfully created the output file
		// now zip the shp, dbf and shx
		$exec_str = "cd /tmp/; "
					. "rm " . $output_file_name . ".zip; "
					. "/usr/bin/zip -j  " . $output_file_name . ".zip " . $output_file_name . ".*;";

		// execute the command
		exec($exec_str);
		// check if zip file was created
		if(!file_exists($output_dir . $output_file_name . ".zip")){
			echo "could not create zip file using: " . $exec_str;
			return null;
		}
		// zip file successfully created.
		// return path to zip file
		return $output_dir . $output_file_name . ".zip";				
}

///
/// drop_table()
/// drop the given table from the db
///
function drop_table($tablename){
	$sql_str = "DROP TABLE " . $tablename;
	$dbconn =& new DBConn();
	$dbconn->conn_str = $dbconn->schema_creator_conn_str;
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query - " 
				. $sql_str ." - " 
				. pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	} 
	$dbconn->disconnect();
	return true;
}

///
///
///
function download_file($path){
	$file_name = basename($path);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=" . $file_name);
	header("Content-Transfer-Encoding: binary");
	readfile($path);
}
?>
