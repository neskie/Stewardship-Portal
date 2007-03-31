<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Mar 22, 2007
file:	class_submission_layer.php

desc:	abstraction of a spatial layer
		uploaded with a submission

notes:	
---------------------------------------------------------------*/

include_once('class_dbconn.php');
include_once('class_submission_layer_class.php');

class Submission_Layer{
	var $layer_id;
	var $layer_name;
	var $view_name;
	var $geom_pk_col_name;
	var $geom_type;
	var $layer_proj;
	var $layer_classes;
	var $dbconn;
	var $ogr_bin; /* path to ogr2ogr executable */
	//
	/// constructor
	/// instantiate a submission file object
	///
	function Submission_Layer($layer_id){
		$this->ogr_bin = "/usr/local/bin/ogr2ogr";
		$this->layer_id = $layer_id;
		$this->layer_classes = array();
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
		if(!$this->get_layer_attributes())
			return NULL;
	}
	
	///
	/// get_layer_attributes
	/// get the attributes of a layer
	///
	function get_layer_attributes(){
		$sql_str = "SELECT "
					. "tng_spatial_layer.layer_name, "
					. "tng_spatial_attribute_table.view_name, "
					. "tng_spatial_data.pk_col_name, "
					. "tng_spatial_data.geometry_type, "
					. "tng_spatial_layer.proj_string "
				. "FROM "
					. "tng_spatial_layer "
					. "INNER JOIN tng_spatial_attribute_table " 
									. "ON tng_spatial_layer.attr_table_id = tng_spatial_attribute_table.attr_table_id "
					. "INNER JOIN tng_spatial_data " 
									."ON tng_spatial_attribute_table.spatial_table_id =  tng_spatial_data.spatial_table_id "
				. "WHERE "
					. "tng_spatial_layer.layer_id = " . $this->layer_id;
		
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		// successfuly ran the query
		// store layer attributes
		$this->layer_name = pg_fetch_result($result, 0, 0);
		$this->view_name = pg_fetch_result($result, 0, 1);
		$this->geom_pk_col_name = pg_fetch_result($result, 0, 2);
		$this->geom_type = pg_fetch_result($result, 0, 3);
		$this->layer_proj= pg_fetch_result($result, 0, 4);
		
		$this->dbconn->disconnect();
		
		return true;
	}
	
	///
	/// convert_to_shapefile()
	/// convert the layer into a shapefile
	/// by calling out to OGR.
	/// if the shapefile is created successfully,
	/// then the .shp, dbf and shx files are
	/// zipped and the path is returned.
	///
	function convert_to_shapefile(){
		$output_dir = "/tmp/";
		$basename = $this->layer_name;
		$shapefile =  $basename . ".shp" ;
		$zipfile = $basename . ".zip" ;
		
		// build a call out string to OGR
		// that looks something like:
		// ogr2ogr 
		// 		-f "ESRI Shapefile" 
		// 		-sql "select * from vi_process1_poly1 where layer_id = 66" 
		//		/tmp/tng_port.shp 
		//		PG:'host=ninkasi dbname=tng_dev user=tng_readwrite password=tng_readwrite'
		$exec_str = "cd /tmp/; "
					. "rm " . $basename . ".*; "
					. $this->ogr_bin . " "
					. "-f \"ESRI Shapefile\" "
					. "-sql \"SELECT * FROM " . $this->view_name . " WHERE layer_id = " . $this->layer_id . "\" "
					. $output_dir . $shapefile . " "
					. "PG:'" . $this->dbconn->conn_str . "'";
		
		// execute the command
		exec($exec_str, $output);
		// check if shapefile was created
		if(!file_exists($output_dir . $shapefile)){
			echo "could not create output shapefile using: " . $exec_str . "error: " . $output;
			return null;
		}
		// ogr2ogr successfully created the output file
		// now zip the shp, dbf and shx
		$exec_str = "cd /tmp/; "
					. "rm " . $zipfile . "; "
					. "/usr/bin/zip -j  " . $zipfile . " " . $basename . ".*;";

		// execute the command
		exec($exec_str);
		// check if zip file was created
		if(!file_exists($output_dir . $zipfile)){
			echo "could not create zip file using: " . $exec_str;
			return null;
		}
		// zip file successfully created.
		// return path to zip file
		return $output_dir . $zipfile;				
	}
	
	///
	/// get_layer_classes()
	/// get all child mapserver
	/// classes associated with this
	/// layer
	///
	function get_layer_classes(){
		$sql_str = "SELECT "
						. "class_id "
					. "FROM "
						. "tng_layer_ms_class "
					. "WHERE "
						. "layer_id = " . $this->layer_id;
						
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}

		// successfuly ran the query
		// get classes and store them in the
		// layer_classes array
		$n_classes = pg_num_rows($result);
		for($i = 0; $i < $n_classes; $i++){
			$this->layer_classes[$i] =& new Submission_Layer_Class(pg_fetch_result($result, $i, 0));
			if($this->layer_classes[$i] == NULL){
				$this->dbconn->disconnect();
				return false;
			}
		}
		
		$this->dbconn->disconnect();

		return true;				
	}
}