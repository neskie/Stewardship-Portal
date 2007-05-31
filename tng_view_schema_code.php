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
}else{	
	if(isset($_POST['ajax_action'])){		
		switch($_POST['ajax_action']){
			// caller wants list of all available
			// schemas
			case "get_schemas":
				$schema_list = array();
				$xml = "";
			 	if(get_schema_list($schema_list))
					$xml = convert_schema_list_to_xml($schema_list);
				echo $xml;
			break;
			// the caller wishes get details
			// on a particular schema
			case "get_schema_details":
				$attr_table_id = $_POST['schema_id'];
				$xml = get_schema_details($attr_table_id);
				echo $xml;
			break;
		}
	}
}


///
/// get_schema_list()
/// get list of available schemas in the db.
/// store results in the array as
/// array[schema_name] = schema_id
///
function get_schema_list(&$schema_list){

	$sql_str = "SELECT "
					. "attr_table_id, "
					. "attr_table_name "
				. "FROM "
					. "tng_spatial_attribute_table "
				. "ORDER BY "
					. "attr_table_name";
					
	$dbconn =& new DBConn();

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$n_schemas = pg_num_rows($result);
	
	for($i = 0; $i < $n_schemas; $i++)
		$schema_list[pg_fetch_result($result, $i, 'attr_table_name')] = 
							pg_fetch_result($result, $i, 'attr_table_id');
							
	$dbconn->disconnect();
	return true;
}

///
/// convert_schema_list_to_xml()
/// convert schema list to xml representation
///	<schema>
///		<id>	1	</id>
///		<name> abc </name>
///	</schema>
///
function convert_schema_list_to_xml($schema_list){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
	$n_schemas = count($schema_list);
	$schema_names = array_keys($schema_list);
	$xml .= "<schemas>";
	// note: the javascript xml parser is picky
	// about the newline character, so a \n after
	// opening <schema> will actually register as
	// a child of <schema>
	for($i = 0; $i < $n_schemas; $i++)
		$xml .= "<schema>"
				. "<id>" . $schema_list[$schema_names[$i]] . "</id>"
				. "<name>" . $schema_names[$i] . "</name>"
				. "</schema>\n";
	$xml .= "</schemas>";
	return $xml;
}

///
/// get_schema_details()
/// get xml representation of a particular
/// schema
///	<schema>
///		<geom_type> polygon </geom_type>
///		<attribute>
///			<name> area </name>
///			<type> double precision </type>
///		</attribute>
///		...
///	</schema>
function get_schema_details($attr_table_id){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
	$sql_str = "SELECT "
					. "tng_spatial_data.geometry_type "
				. "FROM "
					. "tng_spatial_attribute_table "
					. "INNER JOIN tng_spatial_data ON " 
							. "tng_spatial_attribute_table.spatial_table_id = tng_spatial_data.spatial_table_id "
				. "WHERE "
					. "tng_spatial_attribute_table.attr_table_id = " . $attr_table_id;
	$dbconn =& new DBConn();

	$dbconn->connect();

	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	$xml .= "<schema>\n";
	$xml .= "<geom_type>" . pg_fetch_result($result, 0, 'geometry_type') . "</geom_type>\n";
	$dbconn->disconnect();
	
	$sql_str = "SELECT "
					. "tng_spatial_attribute.attr_name, "
					. "tng_spatial_attribute.attr_type "
				. "FROM "
					. "tng_spatial_attribute "
				. "WHERE "
					. "tng_spatial_attribute.attr_table_id = " . $attr_table_id;
	
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	$n_attributes = pg_num_rows($result);
	for($i = 0; $i < $n_attributes; $i++)
		$xml .= "<attribute>"
				. "<name>" . pg_fetch_result($result, $i, 'attr_name') . "</name>"
				. "<type>" . pg_fetch_result($result, $i, 'attr_type') . "</type>"
				. "</attribute>\n";
	$dbconn->disconnect();
	$xml .= "</schema>";
	return $xml;
}

?>