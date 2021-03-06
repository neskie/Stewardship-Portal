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

// globals
$db_conn =& new DBConn();
$schema_creator_conn_str = $db_conn->schema_creator_conn_str;

// form is being loaded first time or
// it is being loaded through ajax
if(!isset($_SESSION['obj_login'])){
	echo "Please Login before proceeding";
}else{	
	if(isset($_POST['ajax_action'])){
		
		switch($_POST['ajax_action']){
			// check if a schema exists
			// with the same name
			case "check_schema_name":
				echo check_schema_name(strtolower($_POST['schema_name']));
			break;
			// check if the field names sent in are
			// SQL keywords
			case "check_field_sql_name":
				$n_fields = $_POST['n_fields'];
				$xml_response = check_field_sql_keywords($n_fields, $_POST);
				echo $xml_response;
			break;
			// the caller wishes to create
			// a schema
			case "create_schema":
				$schema_name = strtolower($_POST['schema_name']);
				$geom_type = $_POST['geom_type'];
				$n_fields = $_POST['n_fields'];
				$result = create_schema($schema_name, $geom_type, $n_fields, $_POST);
				if($result)
					echo "true";
				else
					echo "false";
				//$html = generate_html($xml, $xslt_user);
			break;
		}
	}
}


///
/// check_schema_name()
/// check if schema name already
/// exists (return 1) or if the schema name is a
/// SQL keyword (return 2).
/// return 0 if no matches found
///
function check_schema_name($schema_name){
	$sql_str = "SELECT "
					. "attr_table_id "
				. "FROM "
					. "tng_spatial_attribute_table "
				. "WHERE "
					. "attr_table_name = '" . $schema_name . "'";
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	// schema with the same name exits.
	if(pg_num_rows($result) > 0){
		$dbconn->disconnect();
		return 1;
	}
	$dbconn->disconnect();
	if(check_string_sql_keyword(strtoupper($schema_name)))
		return 2;
		
	return 0; // success
}

///
/// check_sql_keywords()
/// go through the array and for each
/// item, check if the name is an SQL
/// keyword. these are stored in a table
/// in the db.
///
function check_field_sql_keywords($n_fields, $post_vars){
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
		 		. "<result>";
	$is_sql = "false";
	$f_name = "";
	
	$fields = array();
	// populate fields array with names,
	// no need to extract type.
	collect_fields($fields, $n_fields, $post_vars, false);
	$f_names = array_keys($fields);
	
	
	for($i = 0; $i < $n_fields; $i++){
		// keyword match found
		if(check_string_sql_keyword(strtoupper($f_names[$i]))){
			$is_sql = "true";
			$f_name = $f_names[$i];
			break;
		}
	}
	$xml .="<is_sql>" . $is_sql . "</is_sql>";
	$xml .="<f_name>" . $f_name . "</f_name>";
	$xml .= "</result>";
	return $xml;
}

function check_string_sql_keyword($string){
	$is_keyword = false;
	$sql_str = "SELECT "
					. "id "
				. "FROM "
					. "tng_sql_keywords "
				. "WHERE "
					. "(" 
						. " postgresql = 'reserved' "
						. "OR "
						. "sql_2003 = 'reserved' "
						. "OR "
						. "sql_1999 = 'reserved' "
						. "OR "
						. "sql_92 = 'reserved' "
					. ") "
					. "AND "
					. "keyword = '". $string . "'";
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	// keyword match found
	if(pg_num_rows($result) > 0)
		$is_keyword = true;
	$dbconn->disconnect();
	
	return $is_keyword;
}
///
/// create_schema()
/// create a new spatial schema
///
function create_schema($schema_name, $geom_type, $n_fields, $post_vars){
	$result = false;
	$fields = array();
	// populate fields array with 
	// field_name and field type
	// pairs
	collect_fields($fields, $n_fields, $post_vars);
	$view_name = "vi_" . $schema_name;
	$attr_table_id = -1;
	if(($attr_table_id = create_schema_record($schema_name, $view_name, $geom_type, $fields)) != -1
		&& create_physical_table($schema_name, $geom_type, $attr_table_id, $fields)
		&& create_view($view_name, $schema_name, $geom_type, $fields)
		&& grant_permissions($schema_name, $view_name)
		&& associate_default_mapserv_class($attr_table_id, $geom_type))
			$result = true;
	return $result;
}

///
/// collect_fields
/// go through the post variables
/// and extract fields that the user
/// created.
/// these are expected to be in the form
/// field_1_name = xxx 
/// field_1_type = yyy
///
function collect_fields(&$fields, $n_fields, $post_vars, $extract_type = true){
	$prefix = "field_";
	$name_suffix = "_name";
	$type_suffix = "_type";
	for($i = 0; $i < $n_fields; $i++){
		$f_name = $post_vars[$prefix . $i . $name_suffix];
		if($extract_type)
			$fields[$f_name] = $post_vars[$prefix . $i . $type_suffix];
		else
			$fields[$f_name] = "";
	}
}

///
/// create_schema_record()
/// create a record in the tng_spatial_attribute
/// table. before this is done, we need to find
/// out which geometry table this new schema 
/// should be linked to.
/// once this is done and a record is created,
/// we can proceed to create the attribute
/// entries in tng_spatial_attribute
///
function create_schema_record($schema_name, $view_name, $geom_type, $attributes){
	$attr_table_id = -1;
	$sql_str = "SELECT "
				. "spatial_table_id, "
				. "pk_col_name "
			. "FROM "
				. "tng_spatial_data "
			. "WHERE "
				. "geometry_type LIKE '%" . $geom_type . "%'";
	
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return -1;
	}

	$spatial_table_id = pg_fetch_result($result, 0, 'spatial_table_id');
	$spatial_pk_col = pg_fetch_result($result, 0, 'pk_col_name');
	$dbconn->disconnect();
	
	$sql_str = "INSERT INTO " 
					. "tng_spatial_attribute_table "
					. "("
						. "spatial_table_id, "
						. "attr_table_name, "
						. "view_name "
					. ") "
					. "VALUES "
					. "("
						. $spatial_table_id . ", "
						. "'" . $schema_name . "', "
						. "'" . $view_name . "' "
					. "); "
					. "SELECT "
						. "max(attr_table_id) "
					. "FROM "
						. "tng_spatial_attribute_table ";
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return -1;
	}
	
	$attr_table_id = pg_fetch_result($result, 0, 0);
	$dbconn->disconnect();
	
	// now insert the individual
	// attributes into the
	// tng_spatial_attribute table
	$attr_names = array_keys($attributes);
	$n_attributes = count($attr_names);
	$dbconn->connect();
	for($i = 0; $i < $n_attributes; $i++){
		$sql_str = "INSERT INTO "
						. "tng_spatial_attribute "
						. "("
							. "attr_table_id, "
							. "attr_name, "
							. "attr_type"
						. ") "
						. "VALUES "
						. "("
							. $attr_table_id . ", "
							. "'" . $attr_names[$i] . "', "
							. "'" . $attributes[$attr_names[$i]] . "'"
						. ")";
		
		$result = pg_query($dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
			$dbconn->disconnect();
			return -1;
		}
	}
	$dbconn->disconnect();
	
	return $attr_table_id;
}

///
/// create_physical_table()
/// create the actual table representing the
/// schema in the db.
/// note: a different user connects when 
/// executing the CREATE query
///
function create_physical_table($table_name, $geom_type, $attr_table_id, $fields){
	global $schema_creator_conn_str;
	
	$sql_str = "SELECT "
					. "table_name, "
					. "pk_col_name "
				. "FROM "
					. "tng_spatial_data "
				. "WHERE "
					. "geometry_type LIKE '%" . $geom_type . "%'";
					
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$spatial_table_name = pg_fetch_result($result, 0, 'table_name');
	$pk_col_name = pg_fetch_result($result, 0, 'pk_col_name');
	
	$dbconn->disconnect();
	
	$field_names = array_keys($fields);
	$n_fields = count($field_names);
	
	$sql_str = "CREATE TABLE "
					. $table_name
					. "("
						. "id SERIAL PRIMARY KEY, "
						. $pk_col_name . " INTEGER REFERENCES "  . $spatial_table_name . " (" . $pk_col_name . "), ";
	
	for($i = 0; $i < $n_fields; $i++){
		$sql_str .= $field_names[$i] . " " . $fields[$field_names[$i]];
		if($i < $n_fields - 1)
			$sql_str .= ", ";
	}
	
	$sql_str .= "); ";
	
	$dbconn =& new DBConn();
	$dbconn->conn_str = $schema_creator_conn_str;
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	return true;
}

///
/// create_view()
/// create a view which links the newly created
/// schema with the spatial table via the
/// foreign key.
/// NOTE: a different user connects when 
/// performing the CREATE query
///
function create_view($view_name, $schema_name, $geom_type, $fields){
	global $schema_creator_conn_str;
	$layer_table_name = "tng_spatial_layer";
	
	$sql_str = "SELECT "
				. "table_name, "
				. "pk_col_name "
			. "FROM "
				. "tng_spatial_data "
			. "WHERE "
				. "geometry_type LIKE '%" . $geom_type . "%'";
	
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	$dbconn->disconnect();
	
	$spatial_table_name = pg_fetch_result($result, 0, 'table_name');
	$pk_col_name = pg_fetch_result($result, 0, 'pk_col_name');
	
	$sql_str = "CREATE VIEW "
					. $view_name . " "
					. "AS "
						. "SELECT \n"
							. $spatial_table_name . "." . $pk_col_name . ",\n"
							. $spatial_table_name . ".the_geom,\n"
							. $spatial_table_name . ".layer_id,\n"
							. $layer_table_name . ".form_submission_id AS submission_id,\n";
							
	$field_names = array_keys($fields);
	$n_fields = count($fields);
	for($i = 0; $i < $n_fields; $i++){
		$sql_str .= $schema_name . "." . $field_names[$i];
		if($i < $n_fields - 1)
			$sql_str .= ",";
		$sql_str .= "\n";
	}
	
	$sql_str .= "FROM "
				. $schema_name . "\n"
				. "INNER JOIN " . $spatial_table_name 
					. " ON " . $schema_name . "." . $pk_col_name . " = " . $spatial_table_name . "." . $pk_col_name . " "
				. "INNER JOIN " . $layer_table_name 
					. " ON " . $spatial_table_name . "." . "layer_id = " . $layer_table_name . ".layer_id";
	
	$dbconn->conn_str = $schema_creator_conn_str;
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	$dbconn->disconnect();
	return true;
}

///
/// grant_permissions()
/// grant permissions on newly created
/// db objects to other db users
/// permissions: 
///		tng_admin (rwx), 
///		tng_readwrite (rw), 
///		tng_readonly (r)
///
function grant_permissions($table_name, $view_name){
	global $schema_creator_conn_str;
	$sql_str = "GRANT SELECT ON " . $table_name . ", " . $view_name . " "
				. "TO tng_readwrite, tng_readonly; "
				. "GRANT SELECT ON " . $table_name . "_id_seq "
				. "TO tng_readonly; "
 				. "GRANT INSERT ON " . $table_name . " "
				. "TO tng_readwrite; "
				. "GRANT SELECT, UPDATE ON " . $table_name . "_id_seq "
				. "TO tng_readwrite; "
				. "GRANT ALL PRIVILEGES ON " . $table_name . ", " . $view_name . " "
				. "TO tng_admin";
	$dbconn =& new DBConn();
	$dbconn->conn_str = $schema_creator_conn_str;
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);
	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	return true;
}

///
/// associate_default_mapserv_class()
/// based on the type of schema this is, add an entry
/// in tng_attr_table_ms_class so that this schema is
/// associated with a default class.
/// note that if the schema is not affiliated with any
/// classes, then the data loaded using this schema will
/// not be able to be displayed in the mapping agent
///
function associate_default_mapserv_class($attr_table_id, $geom_type){
	// construct query based on geom_type to
	// find out ID for the default class for 
	// that geometry type
	$sql_str = "SELECT "
					. "id "
				. "FROM "
					. "tng_mapserver_class "
				. "WHERE "
					. "tng_mapserver_class.name = ";
	
	switch($geom_type){
		case "polygon":
			$sql_str = $sql_str . "'polygon default' ";
		break;
		case "line":
			$sql_str = $sql_str . "'line default' ";
		break;
		case "point":
			$sql_str = $sql_str . "'point default' ";
		break;
	}

	// query to obtain the class id
	$dbconn =& new DBConn();
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);

	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$class_id = pg_fetch_result($result, 0, 'id');
	$dbconn->disconnect();

	// insert attr_table_id and class id
	// into tng_attr_table_ms_class.
	$sql_str = "INSERT INTO " 
					. "tng_attr_table_ms_class "
					. "( "
						. "attr_table_id, "
						. "ms_class_id"
					. ") "
					. "VALUES "
					. "("
						. $attr_table_id . ", "
						. $class_id
					. ") ";
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sql_str);

	if(!$result){
		echo "An error occurred while executing the query " . pg_last_error($dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	
	$dbconn->disconnect();

	return true;
}

?>
