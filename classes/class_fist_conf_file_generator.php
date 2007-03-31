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
	var $default_layerconf_file;
	var $viewable_layers; // array containing Submission_Layer objects
	var $output_mapfile;
	var $output_layerconf;
	var $output_mapservconf; 
	
	///
	/// constructor
	/// instantiate dbconn object
	/// set member variables
	///
	function Fist_Conf_File_Generator($uid, $default_map_file, $default_layerconf_file, $dest_dir){
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object - class_login.php:23');
		// connection successful
		$this->viewable_layers = array();
		$this->uid = $uid;
		$this->dest_dir = $dest_dir;
		$this->default_map_file = $default_map_file;
		$this->default_layerconf_file = $default_layerconf_file;
		$rnd_prefix = uniqid(true) ;
		// set output file names & paths
		$this->output_mapfile = $dest_dir . $rnd_prefix . "_" . basename($default_map_file);
		$this->output_layerconf = $dest_dir . $rnd_prefix . "_layer-config.xml";
		$this->output_layerconf = $dest_dir . $rnd_prefix . "_map-service-config.xml";
	}
	
	///
	/// get_viewable_layers()
	/// get a list of viewable
	/// layers
	///
	function get_viewable_layers(){
		$sql_str = "SELECT "
						. "tng_spatial_layer.layer_id "
					. "FROM "
						. "tng_spatial_layer "
						. "INNER JOIN tng_layer_permission ON tng_spatial_layer.layer_id = tng_layer_permission.layer_id "
					. "WHERE "
						. "tng_layer_permission.uid = " . $this->uid;
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
			$this->set_ms_layer_type($layer, $this->viewable_layers[$i]);
			$layer->set("connectiontype", MS_POSTGIS);
			$layer->set("connection", $this->dbconn->conn_str);
			$this->set_ms_data_string($layer, $this->viewable_layers[$i]);
			$layer->setProjection($this->viewable_layers[$i]->layer_proj);
			// generate classes that this
			// layer contains
			$n_classes = count($this->viewable_layers[$i]->layer_classes);
			for($j = 0; $j < $n_classes; $j++){
				$class = ms_newClassObj($layer);
				$class->set("name", $this->viewable_layers[$i]->layer_classes[$j]->class_name);
				$class->setExpression($this->viewable_layers[$i]->layer_classes[$j]->class_expr);
				$style = ms_newStyleObj($class);
				$style->color->setRGB($this->viewable_layers[$i]->layer_classes[$j]->class_color_r,
										$this->viewable_layers[$i]->layer_classes[$j]->class_color_g,
										$this->viewable_layers[$i]->layer_classes[$j]->class_color_b);
				
			}
			
		}
		
		if($map->save($this->output_mapfile) == MS_FAILURE){
			echo "mapfile could not be saved";
			return NULL;
		}
		
		return $this->output_mapfile;	
	}
	
	///
	/// set__ms_layer_type()
	/// set the mapserver layer
	/// type based on the db
	/// layer
	///
	function set_ms_layer_type(&$ms_layer, $db_layer){
		switch ($db_layer->geom_type){
			// point
			case "point":
			case "multipoint":
				$ms_layer->set("type", MS_LAYER_POINT);
				break;
			// line	
			case "line":
			case "linestring":
			case "multilinestring":
				$ms_layer->set("type", MS_LAYER_LINE);
				break;
			// polygon
			case "polygon":
			case "multipolygon":
				$ms_layer->set("type", MS_LAYER_POLYGON);
				break;
			// default
			default:
				$ms_layer->set("type", MS_LAYER_POLYGON);
		}
	}
	
	///
	/// set_ms_data_string()
	/// construct a data string for
	/// the mapserver layer.
	///
	function set_ms_data_string(&$ms_layer, $db_layer){
		$data_str = "the_geom from "
						. "(" 
							. "SELECT " 
								. "the_geom, "
								.  $db_layer->geom_pk_col_name
							. " FROM " 
								. $db_layer->view_name . " "
							. "WHERE " 
								. "layer_id = " .$db_layer->layer_id . " "
						. ") "
						. "AS foo USING UNIQUE " .$db_layer->geom_pk_col_name . " "
						. "USING SRID=-1";
		$ms_layer->set("data", $data_str);
	}
	
	///
	/// generate_layerconf_file()
	/// generate the xml needed by
	/// the fist for the layer
	/// config file.
	/// layers are added as they appear
	/// in the viewable layers array
	/// the schema for a layer is:
	///
    /// <layer>
	///	<visible>true</visible>
	///	<name>tus_line</name>
	///	<alias>tus line</alias>
	///	<max-scale>100000000</max-scale>
	///	<min-scale>1</min-scale>
	///	<context>
	///		<modes>
	///			<mode>select</mode>
	///		</modes>
	///	</context>
	/// </layer>
	///
	/// the schema for a folder is:
	///	<folder-layer>
	///		<name> abcd </name>
	/// </folder-layer>
	///	
	function generate_layerconf_file(){
		$dom;
		if(!$dom = domxml_open_file($this->default_layerconf_file)){
			echo "Could not open xml file:  " . $this->default_layerconf_file;
			return NULL; 
		}
		
		// get all <layers> elements
		$all_layers = $dom->get_elements_by_tagname("layers");
		$all_folders = $dom->get_elements_by_tagname("folders");
		// currently there should only be one 
		// of each of <layers> and <folders> elements
		$layers = $all_layers[0];
		$folders = $all_folders[0];

		// create <folder> to hold the new layers
		$visible_layers_folder = $this->create_dom_node($dom, $folders, "folder");
		// create <name> Visible Layers </name>
		$this->create_dom_node($dom, $visible_layers_folder, "name", "Visible Layers");
		// create <folder-layers>
		$folder_layers = $this->create_dom_node($dom, $visible_layers_folder, "folder-layers");
		
		// now that the folder has been created to hold the
		// new layer, loop through all viewable layers
		// and create a <layer> object for each, along with
		// a <folder-layer> for each.
		$n_layers = count($this->viewable_layers);
		for($i = 0; $i < $n_layers; $i++){
			// create <layer>
			$new_layer = $this->create_dom_node($dom, $layers, "layer");
			// create <visible>true</visible>
			$this->create_dom_node($dom, $new_layer, "visible", "true");
			// create <name>tus_line</name>
			$this->create_dom_node($dom, $new_layer, "name", $this->viewable_layers[$i]->layer_name);
			// create 	<alias>tus line</alias>
			$this->create_dom_node($dom, $new_layer, "alias", $this->viewable_layers[$i]->layer_name);
			// create <max-scale>100000000</max-scale>
			$this->create_dom_node($dom, $new_layer, "max-scale", "100000000");
			// create <min-scale>1 </max-scale>
			$this->create_dom_node($dom, $new_layer, "min-scale", "1");
			// create <context>
			$context = $this->create_dom_node($dom, $new_layer, "context");
			// create <modes>
			$modes = $this->create_dom_node($dom, $context, "modes");
			// create <mode>
			$this->create_dom_node($dom, $modes, "mode", "select");
			// the <layer> element is now complete.
			// generate <folder-layer> elements
			$folder_layer = $this->create_dom_node($dom, $folder_layers, "folder-layer");
			// create <name>tus line</name> 	
			$this->create_dom_node($dom, $folder_layer, "name", $this->viewable_layers[$i]->layer_name);			
		}
		
		$dom->dump_file("/tmp/test.xml", false, false);
		
	}
	
	function create_dom_node(&$dom, &$parent_node, $node_name, $node_value = NULL){
		$node = $dom->create_element($node_name);
		$new_node = $parent_node->append_child($node);
		if($node_value != NULL){
			$txt_node = $dom->create_text_node($node_value);
			$new_node->append_child($txt_node);
		}
		return $new_node;
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