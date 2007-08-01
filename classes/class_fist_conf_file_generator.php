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
	var $default_mapservconf_file;
	var $mapservice_name;
	var $viewable_layers; // array containing Submission_Layer objects
	var $output_mapfile;
	var $output_layerconf;
	var $output_mapservconf; 
	
	///
	/// constructor
	/// instantiate dbconn object
	/// set member variables
	///
	function Fist_Conf_File_Generator($uid, 
									$default_map_file, 
									$default_layerconf_file, 
									$default_mapservconf_file,
									$mapservice_name, 
									$dest_dir){
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object - class_login.php:23');
		// connection successful
		$this->viewable_layers = array();
		$this->uid = $uid;
		$this->dest_dir = $dest_dir;
		$this->default_map_file = $default_map_file;
		$this->default_layerconf_file = $default_layerconf_file;
		$this->default_mapservconf_file = $default_mapservconf_file;
		$this->mapservice_name = $mapservice_name;
		$rnd_prefix = uniqid(true) ;
		// set output file names & paths
		$this->output_mapfile = $dest_dir . $rnd_prefix . "_" . basename($default_map_file);
		$this->output_layerconf = $dest_dir . $rnd_prefix . "_layer-config.xml";
		$this->output_mapservconf = $dest_dir . $rnd_prefix . "_map-service-config.xml";
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
					echo "could get layer classes";
					$this->dbconn->disconnect();
					return false;
			}
		}
		
		//$this->dbconn->disconnect();
		
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
		// layer objects for those layers
		// that have their display property set
		// to true, adding them to the
		// map object
		for($i = 0; $i < $n_layers; $i++){
			if($this->viewable_layers[$i]->display == "true"){
				$layer = ms_newLayerObj($map);
				$layer->set("name", $this->viewable_layers[$i]->layer_name);
				$layer->set("status", MS_OFF);
				$layer->set("template", "nepas.html");
				$this->set_ms_layer_type($layer, $this->viewable_layers[$i]);
				$layer->set("connectiontype", MS_POSTGIS);
				$layer->set("connection", $this->dbconn->mapserver_conn_str);
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
							. "SELECT  " 
								. "* "
							. " FROM " 
								. $db_layer->view_name . " "
							. "WHERE " 
								. "layer_id = " .$db_layer->layer_id . " "
						. ") "
						. "AS foo USING UNIQUE " .$db_layer->geom_pk_col_name . " "
						. "USING SRID=-1";
						
		/* - query used for testing - no layer id specified.
		$data_str = "the_geom FROM " . $db_layer->view_name . " "
						. "AS foo USING UNIQUE " .$db_layer->geom_pk_col_name . " "
						. "USING SRID=-1";
		*/
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
		// a <folder-layer> for each layer that has its
		// display property set to true.
		$n_layers = count($this->viewable_layers);
		for($i = 0; $i < $n_layers; $i++){
			if($this->viewable_layers[$i]->display == "true"){
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
		}
		
		$dom->dump_file($this->output_layerconf, false, false);
		return $this->output_layerconf;
	}
	
	///
	/// generate_mapservice_conf_file()
	/// generate the xml needed by the
	/// fist for the mapservice config
	/// file.
	///
	function generate_mapservice_conf_file(){
		$dom;
		if(!$dom = domxml_open_file($this->default_mapservconf_file)){
			echo "Could not open xml file:  " . $this->default_mapservconf_file;
			return NULL; 
		}
		$mapservice = $this->find_mapservice($dom);
		// map service not found
		if($mapservice == NULL){
			echo "could not find map-service named: " . $mapservice_name ;
			return NULL;
		}
		
		$children = $mapservice->child_nodes();
		$n_children = count($children);
		
		// loop through to find <map-file>
		// and <layer-config> element
		for($i = 0; $i < $n_children; $i++){
			switch ($children[$i]->tagname){
				case "map-file":
					$this->change_node_content($dom, $children[$i], $this->output_mapfile);
					break;
				
				case "layer-config-file":
					$this->change_node_content($dom, $children[$i], $this->output_layerconf);
					break;
			}
		}
		
		$dom->dump_file($this->output_mapservconf, false, false);
		return $this->output_mapservconf;
	}
	
	///
	/// find_mapservice()
	/// locate the mapservice element
	/// that we are interested in
	/// within the dom
	///
	function find_mapservice($dom){
		$mapservice = NULL;
		// get all <map-service> elements
		$mapservices = $dom->get_elements_by_tagname("map-service");
		$n_services = count($mapservices);
		// loop through all the services and find
		// the one whose name matches the mapservice
		// name that was passed into the constructor
		for($i = 0; $i < $n_services; $i++){
			// get all the child nodes of
			// a <map-service> node
			$children = $mapservices[$i]->child_nodes();
			$n_children = count($children);
			$child_found = false;
			// go through the children and
			// find a <name> node
			for($j = 0; $j < $n_children; $j++){
				if($children[$j]->tagname == "name"){
					// check if the <name> value 
					// matches the name that was passed
					// in to the constructor. to get the
					// <name> value, we get the child node
					// of <name>, which should be a text node.
					$name_value = $children[$j]->child_nodes();
					$name_value = $name_value[0];
					if($name_value->get_content() == $this->mapservice_name){
						$child_found = true;
						break;
					}
				}
			}
			// if the <map-service> we are looking for 
			// is found, terminate the loop
			if($child_found){
				$mapservice = $mapservices[$i];
				break;
			}
		}		
		return $mapservice;
	}
	
	///
	/// create_dom_node
	/// create a node in the dom object
	/// that is passed. the parent of the 
	/// new node is set to the $parent_node
	/// argument. if a node value is passed
	/// in, a text node is created as a child
	/// of the new node and the value of the
	/// text node is set to the $node_value 
	/// argument. 
	///
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
	/// change_elt_content()
	/// change the text content of a node
	/// within the dom to the new value that
	/// is passed in
	///
	function change_node_content(&$dom, &$node, $new_txt_content){
		// to accomplish changing the content
		// of a node, we delete the child
		// text node of the node passed in and
		// add a new text node with the given value
		$txt_node = $node->child_nodes();
		$txt_node = $txt_node[0];
		$node->remove_child($txt_node);
		// now create the new node
		$new_txt = $dom->create_text_node($new_txt_content);
		$node->append_child($new_txt);
	}
}
?>
