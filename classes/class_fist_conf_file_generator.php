<?php
/*---------------------------------------------------------------
author:	alim karim
date:	March 28, 2007
file:	class_fist_conf_file_generator.php

desc:	to generate configuration files needed
		by the fist from the information stored
		in the tng database.
		the basic functions are:
			- discover which layers the user can see
			- open a default map file and add these layers
			in
			- generate the layer config xml file
			- generate the mapservice-config xml file
---------------------------------------------------------------*/
include_once('class_dbconn.php');
include_once('class_submission_layer.php');

dl('php_mapscript.so');

class Fist_Conf_File_Generator{
	var $dbconn;
	var $uid;
	var $default_map_file;
	var $dest_dir;
	var $viewable_layers; // array containing Submission_Layer objects
	var $rnd_prefix; // unique string prefix for generated files 
	
	///
	/// constructor
	/// instantiate dbconn object
	/// set member variables
	///
	function Fist_Conf_File_Generator($uid, $default_map_file, $dest_dir){
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object - class_login.php:23');
		// connection successful
		$this->viewable_layers = array();
		$this->uid = $uid;
		$this->dest_dir = $dest_dir;
		$this->default_map_file = $default_map_file;
		$this->rnd_prefix = uniqid(true) ;
	}
	
	///
	/// get_viewable_layers()
	/// get a list of viewable
	/// layers
	///
	function get_viewable_layers(){
		$sql_str = "SELECT "
						. "layer_id "
					. "FROM "
						. "tng_spatial_layer "
						. "INNER JOIN tng_layer_permission ON tng_spatial_layer.layer_id = tng_layer_permission.layer_id "
					. "WHERE "
						. "tng_layer_permission.uid = " . $uid;
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		$n_layers = pg_num_rows($result);
		
		for($i = 0; $i < $n_layers; $i++){
			$this->viewable_layers[$i] =& new Submission_Layer(pg_fetch_result($result, $i, 0));
			if($this->viewable_layers[$i] == NULL){
				echo "could not create layer object";
				$this->dbconn->disconnect();
				return false;
			}
			
			// get the mapserver classes that this
			// layer has
			if(!$this->viewable_layers[$i]->get_layer_classes()){
					echo "could not create layer object";
					$this->dbconn->disconnect();
					return false;
			}
		}
		
		$this->dbconn->disconnect();
		
		return true;				
	}
	
	///
	/// generate_map_file()
	/// read the default map file
	/// and add in layers returned
	/// by get_viewable_layers.
	/// use php mapscript functions
	/// to accomplish this
	///
	function generate_map_file(){
		$map = ms_newMapObj($this->default_map_file);
		$n_layers = count($this->viewable_layers);
		// go through and create new 
		// layer objects, adding them to the
		// map object
		for($i = 0; $i < $n_layers; $i++){
			$layer = ms_newLayerObj($map);
			$layer->set("name", $this->viewable_layers[$i]->layer_name);
			$layer->set("status", MS_OFF);
			$layer->set("connectiontype", MS_POSTGIS);
			$layer->set("connection", $this->dbconn->conn_str);
			// generate classes that this
			// layer contains
			$n_classes = count($this->viewable_layers[$i]->layer_classes);
			for($j = 0; $j < $n_classes; $j++){
				$class = ms_newClassObj($layer);
				$class->set("name", viewable_layers[$i]->layer_classes[$j]->class_name);
				$class->setExpression(viewable_layers[$i]->layer_classes[$j]->class_expr);
				$style = ms_newStyleObj($class);
				
			}
			
		}
			
	}
	
	///
	/// generate_layerconf_file()
	/// generate the xml needed by
	/// the fist for the layer
	/// config file.
	/// layers are added as they appear
	/// in the viewable layers array
	///
	function generate_layerconf_file(){
		
	}
	
	///
	/// generate_mapservice_conf_file()
	/// generate the xml needed by the
	/// fist for the mapservice config
	/// file.
	/// args passed in are the name of the
	/// mapfile and the name of the
	/// layer config file
	///
	function generate_mapservice_conf_file($mapfile_name, $layerconf_name){
		
	}
}
?>