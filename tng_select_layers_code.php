<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 18, 2007
file:	tng_select_layers_code.php

desc:	.
---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_app_config.php');
include_once('classes/class_fist_conf_file_generator.php');

session_start();
//unset($_SESSION['fist_file_gen']);
//return;

$xslt_file = "tng_layer_transform.xslt";
$fist_file_gen =& get_conf_file_generator();

// form is being loaded first time or
// it is being loaded through ajax
if(isset($_SESSION['obj_login'])){
	global $xslt_file;
	global $fist_file_gen;

	// check to see if the form has sent
	// a partial layer name as a parameter
	// through AJAX. this means the user wishes
	// to limit the list of layers that they see.
	// apply the filter that they have sent to
	// the list of layers. return the html that
	// represents this list of layers.
	$ajax_layer_prefix = "";
	if(isset($_POST['ajax_layer_name'])){
		$ajax_layer_prefix = $_POST['ajax_layer_name'];
		$xml = generate_layer_xml($fist_file_gen->viewable_layers, $ajax_layer_prefix);
		echo $xml;
	}
	else if(isset($_POST['ajax_refresh_layers'])){
		// unset the conf object and re-obtain
		// the list of layers from the db
		unset($_SESSION['fist_file_gen']);
		$fist_file_gen =& get_conf_file_generator();
		$xml = generate_layer_xml($fist_file_gen->viewable_layers, "");
		echo $xml;
	} 
	// otherwise see if the user has sent a
	// specific layer id that they wish to
	// toggle the display attribute for
	else if(isset($_POST['ajax_layer_id']) && isset($_POST['display'])){
		toggle_display_flag($fist_file_gen->viewable_layers, $_POST['ajax_layer_id'], $_POST['display']);
		// nothing should be echoed back.
	}
	// the user has clicked 
	// the submit button to launch the 
	// mapping agent.
	else if(isset($_POST['ajax_launch_fist'])){ 
		global $fist_file_gen;
		$mapserv_name = $_SESSION['app_config']->mapservice_name;
		if(launch_mapper($fist_file_gen)){
			// echo javascript out to open the fist in
			// a new window
			//$js_str = "window.open('http://142.207.69.203/fist/fistMain.php?site=" .$mapserv_name .  "'); ";
			// switching to OL based viewer - see #13
			// reading url from app_config - see #28
			$js_str = "window.open('" . $_SESSION['app_config']->map_agent_launch_url . "'); ";
			echo $js_str;
		}
	}
}

///
/// get_conf_file_generator()
/// get the conf file generator from the session
/// array. if one does not exist, it is created.
/// the list of viewable layers is also obtained.
/// note that this list should be only fetched
/// once.
///
function &get_conf_file_generator(){
	$fist_file_gen;
	
	// create fist_file_gen object if one
	// does not exist
	// note: this object should be destroyed
	// once the mapping agent is launched
	if(!isset($_SESSION['fist_file_gen'])){
		$mapfile = $_SESSION['app_config']->mapfile_path;
		$layerconf_file = $_SESSION['app_config']->layer_config_path;
		$mapservconf_file = $_SESSION['app_config']->mapservice_config_path;
		$mapserv_name =  $_SESSION['app_config']->mapservice_name;
		$output_dir = $_SESSION['app_config']->output_dir;
		$fist_file_gen =& new Fist_Conf_File_Generator($_SESSION['obj_login'], 
								$mapfile, 
								$layerconf_file,
								$mapservconf_file,
								$mapserv_name, 
								$output_dir);
		// get viewable layers
		if(!$fist_file_gen->get_viewable_layers()){
			echo "could not get viewable layers";
			return;
		}
		// set session variable
		$_SESSION['fist_file_gen'] = $fist_file_gen;
	}
	
	return $_SESSION['fist_file_gen'];
}


///
/// toggle_display_flag()
///
function toggle_display_flag(&$viewable_layers, $layer_id, $display){
	$n_layers = count($viewable_layers);
	for($i = 0; $i < $n_layers; $i++){
		if($viewable_layers[$i]->layer_id == $layer_id){
			$viewable_layers[$i]->set_display($display);
			break;
		}
	}
}

///
/// generate_layer_xml()
/// generate xml representation of
/// viewable layers.
/// the current schema is:
/// <layers>
///		<layer>
///			<layer_id>		12		</layer_id>
///			<layer_name>	abcd	</layer_name>
///			<display>		true	</display>
///		</layer>
///		...
///	</layers>
///
function generate_layer_xml($viewable_layers, $prefix){
	$n_layers = count($viewable_layers);
	// generate xml from viewable
	// layers
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
			. "<layers>";
	for($i = 0; $i < $n_layers; $i++){
		if($prefix == ""
			//|| substr($viewable_layers[$i]->layer_name, 0, strlen($prefix)) == $prefix
			|| substr_count($viewable_layers[$i]->layer_name, $prefix) > 0
			){
			$xml .= "<layer>"
				. "<id>"
				. $viewable_layers[$i]->layer_id
				. "</id>"
				. "<name>"
				. $viewable_layers[$i]->layer_name
				. "</name>"
				. "<display>"
				. $viewable_layers[$i]->display
				. "</display>"
				. "</layer>\n";
		}
	}
	
	$xml .= "</layers>";
	
	return $xml;
}	


///
/// launch_mapper()
/// generate conf files needed 
function launch_mapper($fist_file_gen){
	// generate mapfile
	if($fist_file_gen->generate_map_file() == NULL){
		echo "could not generate map file";
		return false;
	}
	 
	// -----------------------------------------------------------
	// this is the only code that needs to be changed to switch
	// between the fist to the new mapviewer. 
	// since the new viewer only needs a mapfile, there is no
	// need to generate layer-config or mapservice-config files.
	// see #13 for details
	
	// use app config to see what mapping agent we are using.
	// no need to generate mapservice / layerconfig if not
	// using fist.
	// see http://trac.geoborealis.ca/ticket/28
	if($_SESSION['app_config']->map_agent == "fist"){
		// generate layer-config.xml
		if($fist_file_gen->generate_layerconf_file() == NULL){
			echo "could not generate layer config file";
		return false;
		}
		// generate mapservice-config.xml
		$mapserv_conf_new;
		if(($mapserv_conf_new = $fist_file_gen->generate_mapservice_conf_file()) == NULL){
			echo "could not generate mapservice config file";
			return false;
		}
	}
	// -----------------------------------------------------------
	
	// required session variables ('map_path' and 'layers')
	// for new mapviewer
	$_SESSION['map_path'] = $fist_file_gen->output_mapfile;
	unset($_SESSION['layers']);
	$layers = array();
	$n_layers = count($fist_file_gen->viewable_layers);
	for($i = 0; $i < $n_layers; $i++){
		if($fist_file_gen->viewable_layers[$i]->display == "true")
			array_push($layers, $fist_file_gen->viewable_layers[$i]->layer_name);
	}
	$_SESSION['layers'] = $layers;
	// set session variable so that the fist 
	// can see the file path
	$_SESSION['fist_extern_mapserv_config'] = $mapserv_conf_new;

	// destroy the conf file generator object
	// so that the process can be started afresh
	// when the user comes back to select layers
	unset($_SESSION['fist_file_gen']);
	return true;
}
?>
