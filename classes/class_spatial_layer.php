<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 05, 2007
file:	class_spatial_layer

desc:	class to deal with spatial layers
		(shapefiles) submitted with a form
---------------------------------------------------------------*/

include_once('class_dbconn.php');
dl('php_ogr.so');

class SpatialLayer{
	var $shp_name;
	var $dbconn;
	var $submission_id;
	var $pk_col_name; /* name of PK column in destination layer */
	var $layer_id;
	var $geom_type;
	/* 
		ok, this took 4 hrs to figure out - 
		NEVER assign the data source to a 
		local variable that will get deleted 
		after the scope of the function.
		rather, use your brain and STORE the
		datasource as a member, its JUST as
		important as the layer.
	*/
	var $ogr_src_ds; 
	var $ogr_dst_ds; 
	var $ogr_src_layer;
	var $ogr_dst_layer;
	var $dst_layer_name;
	var $attr_table_id; 
	var $attr_table_name; 
	var $attr_table_schema;
	
	//
	/// constructor
	/// instantiate a SpatialLayer object
	/// steps:
	///		1. initialize non spatial member variables
	///		2. create OGR object for the source layer
	///		3. create OGR object for dest layer based on 
	///			geometry of the source layer
	///		4. get attribute table schema
	/// if any of these steps fail, a null object
	/// is returned.
	///
	function SpatialLayer($shp_path, $form_submission_id){
		// initialize member variables
		$this->dbconn = new DBConn();
		$this->shp_name = $shp_path;
		$this->submission_id = $form_submission_id;
		// hash table to hold the schema of the attr table
		$this->attr_table_schema = array();
		
		// now create the source and
		// destination layers
		
		//$src_layer = $this->get_source_layer($shp_path);
		$this->ogr_src_layer = $this->get_source_layer($shp_path);
		
		if($this->ogr_src_layer == NULL){
				echo "could not read source layer";
				return NULL;
		}
		
		// note that ogr_dst_layer also gets the attribute_table_id
		// for the destination layer and stores it in 
		// $this->attr_table_id
		$this->ogr_dst_layer = $this->get_dst_layer($form_submission_id, $this->get_geom_type($this->ogr_src_layer)); 
		
		if($this->ogr_dst_layer == NULL){
			echo "could not open destination layer";
			return NULL;
		}
		
	}
	
	///
	/// validate_layer()
	/// attempt to validate the schema of a layer
	/// against all possible attribute tables
	/// that are associated with this geometry
	///
	function validate_layer(){
		$schema_validated = false;
		$attr_table_ids = array();
		if(!$this->get_attr_table_ids($this->submission_id, $this->geom_type, $attr_table_ids))
			return false;
		
		// now loop through all the attribute tables,
		// get a schema for each one and validate the
		// layer.
		$n_tables = count($attr_table_ids);
		for($k = 0; $k < $n_tables; $k++){
			$schema_match = true;
			$this->attr_table_id = $attr_table_ids[$k];
			// each call to get_attr_table_schema
			// stores the schema and the table name
			// the the member variables
			// $this->attr_table_name
			// and $this->attr_table_schema
			if(!$this->get_attribute_table_schema($attr_table_ids[$k])){
				echo "could not query attribute table info ";
				return false;
			}
			
			// first the number of attributes should match
			// between the layer's attributes and the
			// attribute table schema
			$src_feature = OGR_L_GetNextFeature($this->ogr_src_layer);
			$n_src_attributes = OGR_F_GetFieldCount($src_feature);
			$src_feature_defn = OGR_L_GetLayerDefn($this->ogr_src_layer);
		
			if($n_src_attributes != count($this->attr_table_schema))
				return false;
	
			// now we know that the number of attributes
			// matches. verify the names of each of the attributes
			// attr_table_schema array acts as a hash table in
			// which the key to the table is the name of the
			// attribute and the value is the type. hence, if
			// we search the hash table for an attribute name
			// and none is found, then the shapefile does not
			// match the attribute table schema
			for($i = 0; $i < $n_src_attributes; $i++){
				$field_defn = OGR_FD_GetFieldDefn($src_feature_defn, $i);
				$f_name = strtolower(OGR_Fld_GetNameRef($field_defn));
				if(!array_key_exists($f_name, $this->attr_table_schema)){
					$schema_match = false;
					break;
				}
			}
			
			// if a matching schema has been found.
			// we can successfully break out of the outer
			// loop. the local member variables $attr_table_id,
			// $attr_table_name and $attr_table_schema should
			// be set by the time we reach this point.
			if($schema_match){
				$schema_validated = true;
				break;
			}
		}
				
		return $schema_validated;
	}

	///
	/// get_source_layer()
	/// create an OGR layer handle for
	/// the ogr_src_layer member
	///
	function get_source_layer($layer_name){
		OGRRegisterAll();
		$src_layer = NULL;
		$src_driver = NULL;
		$this->ogr_src_ds = OGROpen($layer_name, FALSE, $src_driver);
		// shapefiles only have one layer 
		// i.e the second arg which is the index
		// of the layer is 0 => layer[0]
		if($this->ogr_src_ds != NULL)
			return OGR_DS_GetLayer($this->ogr_src_ds, 0);
	}
	
	///
	/// get_src_geom_type()
	/// get the geometry type associated
	/// with the first feature of the
	/// given layer.
	///
	function get_geom_type($layer){
		$feature = OGR_L_GetNextFeature($layer);
		$geometry = OGR_F_GetGeometryRef($feature);
		$geom_type = strtolower(OGR_G_GetGeometryName($geometry));
		$this->geom_type = $geom_type;
		return $geom_type;
	}
	
	///
	/// get dst_layer()
	/// get the destination spatial table name
	/// based on the form submission id and the geometry 
	/// of the features that are going to be stored.
	/// an OGR handle to the destination layer
	/// is created and returned.
	///
	function get_dst_layer($form_submission_id, $geom_type){
		OGRRegisterAll();
		$dst_layer_name = "";
		$dst_layer = NULL;
		
		$sql_str = "SELECT "
		      		. "tng_spatial_data.table_name, "
					. "tng_spatial_data.pk_col_name "
				."FROM "
		    		. "tng_form_submission "
				    . "INNER JOIN tng_form_spatial_data on tng_form_submission.form_id = tng_form_spatial_data.form_id "
		    		. "INNER JOIN tng_spatial_data ON tng_form_spatial_data.spatial_table_id = tng_spatial_data.spatial_table_id "
		    		. "INNER JOIN tng_spatial_attribute_table ON tng_form_spatial_data.spatial_table_id = tng_spatial_attribute_table.spatial_table_id "
				. "WHERE "
		     		. "tng_form_submission.form_submission_id  = " . $form_submission_id . " "
					. "AND "
					. "tng_spatial_data.geometry_type LIKE '%" . $geom_type . "%'";
					// note: if geom_type is "multilinestring", then doing a straight
					// comparison (without the LIKE clause) will return no
					// results, because tng_spatial_data.geometry_type
					// is one of: polygon, linestring, point.
		
		$this->dbconn->connect();
		
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{ 
			$this->dst_layer_name = pg_fetch_result($result, 0, 0);
			$this->pk_col_name = pg_fetch_result($result, 0, 1);
			
			$this->dbconn->disconnect();
			// now attempt to create an OGR layer
			// from the spatial table name 
			// use the same connection string as the
			// one in the dbconn object
			$dst_driver = NULL;
			$this->ogr_dst_ds = OGROpen("PG:" . $this->dbconn->conn_str, FALSE, $dst_driver);
			if($this->ogr_dst_ds != NULL)
				$dst_layer = OGR_DS_GetLayerByName($this->ogr_dst_ds, $this->dst_layer_name);
		}
		
		return $dst_layer;
	}
	
	///
	/// get_attr_table_ids()
	/// get a list of attribute table ids that
	/// are related to the form based on 
	/// the geometry type.
	/// this allows for one geometry type to be
	/// related to multiple attribute tables.
	/// for e.g. the referral form could accept
	/// polygons for forest cover and polygons for
	/// proposed cut blocks. both these sets of
	/// polygons will have distinct attribute
	/// tables, but within the db, both are related
	/// to the same geometry table.
	/// the ids are stored in the array passed
	/// in by reference
	///
	function get_attr_table_ids($form_submission_id, $geom_type, &$attr_table_ids){
		$sql_str = "SELECT "
						. "tng_form_spatial_data.attr_table_id "
					. "FROM "
						. "tng_form_submission "
						. "INNER JOIN tng_form_spatial_data ON tng_form_submission.form_id = tng_form_spatial_data.form_id "
						. "INNER JOIN tng_spatial_data ON tng_form_spatial_data.spatial_table_id = tng_spatial_data.spatial_table_id "
					. "WHERE "
						. "tng_form_submission.form_submission_id = " . $form_submission_id . " "
						. "AND "
						. "tng_spatial_data.geometry_type LIKE '%" . $geom_type . "%'";
		
		$this->dbconn->connect();
		
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		$length = pg_num_rows($result);

		for($i = 0; $i < $length; $i++)
			$attr_table_ids[$i] = pg_fetch_result($result, $i, 0);
			
		$this->dbconn->disconnect();
		
		return true;
	}
	
	///
	/// get_attribute_table_schema()
	/// return an array representing the
	/// schema of the attribute table.
	/// the array is treated as a hash table
	/// where the key is the name of the 
	/// attribute and the value is the type
	/// of the attribute.
	///
	/// the name of the attribute table is
	/// also found and stored as a member
	/// variable of the calling object
	///
	/// since we need to come back and be able
	/// to look at the spatial data with its
	/// attributes, we need to know which
	/// view to use when querying the layer
	/// once its in the db. the name of the
	/// view is stored in the attribute table,
	/// but the view is built by hand
	///
	function get_attribute_table_schema($attr_table_id){
		$success = false;
		// query for the attribute table name
		$sql_str = "SELECT "
		      		. "attr_table_name "
				."FROM "
		    		. "tng_spatial_attribute_table "
				. "WHERE "
		     		. "attr_table_id = " . $attr_table_id;

		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{ 
			$this->attr_table_name = pg_fetch_result($result, 0, 0);
			$this->dbconn->disconnect();
			// now query for the attributes themselves
			$sql_str = "SELECT "
						. "attr_name, "
						. "attr_type "
					. "FROM "
						. "tng_spatial_attribute "
					. "WHERE "
						. "attr_table_id = " . $attr_table_id;
			
			$this->dbconn->connect();
			
			$result = pg_query($this->dbconn->conn, $sql_str);
			
			if(!$result){
				echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
				$this->dbconn->disconnect();
			}else{
				// clear any old attributes contained
				// in the attr_table_schema member
				$this->attr_table_schema = array();
				// populate with new values
				$n_attributes = pg_num_rows($result);
				for($i = 0; $i < $n_attributes; $i++)
					// set the name of the attribute
					// as the key, and the type of the
					// attribute as the value of the
					// key
					$this->attr_table_schema[pg_fetch_result($result, $i, 0)] = pg_fetch_result($result, $i, 1);

				$this->dbconn->disconnect();
				$success = true;
			}
		}
		
		return $success;	
	}
	
	///
	/// add_layer_to_db()
	/// take a form id and copy all the
	/// geometries to the appropriate
	/// geometry table.
	/// the attributes are copied to the
	/// attribute table associated with the
	/// form and geometry table.
	///
	function add_layer_to_db(){
		OGRRegisterAll();
		$success = false;
		// create layer record so that all 
		// these geometries can be linked to 
		// one layer
		$basename = basename($this->shp_name);
		$layer_name = substr($basename, strpos($basename, "_") + 1, strpos($basename, ".shp") - strpos($basename,"_") - 1);
		$layer_id = $this->create_layer_record($this->submission_id, $layer_name);
		if($layer_id != -1){
			// loop through source layer, copy
			// each feature to the destination
			// layer
			OGR_L_ResetReading($this->ogr_src_layer);
			$dst_feature_defn = OGR_L_GetLayerDefn($this->ogr_dst_layer);
			while(($src_feature = OGR_L_GetNextFeature($this->ogr_src_layer)) != NULL){
					// create a blank feature
					// based on the destination
					// feature definition
					$dst_feature = OGR_F_Create($dst_feature_defn);
					// now copy the feature object 
					// from the source to the destination.
					// the last value, bForgiving must be set to
					// true because our source and destination
					// schemas dont match. if bForgiving is
					// false, OGR_F_SetFrom quits when it
					// finds a feature that doesnt match both
					// schemas.
					if(OGR_F_SetFrom($dst_feature, $src_feature, TRUE) != OGRERR_NONE){
						echo "could not set destination feature from source feature";
						return false;
					}
					
					// set the layer_id (fk) of the feature
					// to the layer id created earlier in
					// this method
					$layerid_f_index = OGR_F_GetFieldIndex($dst_feature, "layer_id");
					OGR_F_SetFieldInteger($dst_feature, $layerid_f_index, $layer_id);
						
					// ------------------------------------------
					// begin locked section
					// ------------------------------------------
					// since the feature creation does not automatically
					// query the ID of the feature from the db, we
					// need to query it ourselves. to prevent any
					// interleaving of threads that are executing this
					// method, we add a semaphore lock before the
					// feature is created and release the lock once
					// the ID of the feature has been queried.
					// see php docs for sem_get(), sem_acquire()
					// and sem_release
					
					// note the second arg to sem_get must be "1"
					// so that only one process at a time can 
					// acquire the semaphore
					$sem_num = sem_get(5001, 1);
					
					// block until semaphore is available
					sem_acquire($sem_num);
					
					// destination feature successfully set.
					// now "create" the dst feature within the
					// destination layer. 
					if(OGR_L_CreateFeature($this->ogr_dst_layer, $dst_feature) != OGRERR_NONE){
						echo "could not create feature in destination layer ";
						return false;
					}
					
					
					// now that the feature has been 
					// created within the db, we need to
					// get its ID so that we can use it
					// as a FK when inserting the attributes
					// of the feature.
					$pk_col_name = ""; /* unused. see comments in get_max_id() */
					$feature_id = $this->get_max_id($this->dst_layer_name, $pk_col_name);

					// release the semaphore
					sem_release($sem_num);
					
					// ------------------------------------------
					// end locked section
					// ------------------------------------------
					
					if($feature_id == -1){
						echo "could not obtain new ID for feature ";
						return false;
					}
					
					// if the value of the ID was successfully
					// obtained, attempt to create
					// records for the attributes in the attribute
					// table	
					if(!$this->add_attributes_to_db($src_feature, $feature_id, $this->pk_col_name)){
						echo "could not insert attributes for feature ID " . $feature_id;
						return false;
					}
					
					OGR_F_Destroy($src_feature);
					OGR_F_Destroy($dst_feature);							
			}
		}
		return true;
	}
	
	///
	/// create_layer_record()
	/// create an entry in the tng_spatial_layer
	/// table and return the id of
	/// the newly created record
	///
	function create_layer_record($form_submission_id, $layer_name){
		$layer_id = -1;
		// query the form submission table for the
		// user id
		$sql_str = "SELECT "
						. "uid "
					. "FROM "
						. "tng_form_submission "
					. "WHERE "
						. "form_submission_id = " . $form_submission_id;
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{ 
			$uid = pg_fetch_result($result, 0, 0);
			$this->dbconn->disconnect();
			$sql_str = "INSERT INTO tng_spatial_layer "
						. "( " 
							. "form_submission_id, " 
							. "layer_name, "
							. "attr_table_id " 
						. ") "
						. "VALUES "
						. "( "
							. $form_submission_id 		. ", "
							. "'" . $layer_name 		. "', "
							. $this->attr_table_id		. " "
						. "); "
						. "SELECT " 
							. "MAX(layer_id) "
						. "FROM "
							. "tng_spatial_layer ;";
			$this->dbconn->connect();

			$result = pg_query($this->dbconn->conn, $sql_str);

			if(!$result){
				echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
				$this->dbconn->disconnect();
			}else{
				$layer_id = pg_fetch_result($result, 0, 0);
				$this->dbconn->disconnect();
				// once the layer record has been created,
				// grant this user the permission to see
				// the layer.
				// also create a default mapserver class
				// record so that the user can see the
				// layer
				$sql_str = "INSERT INTO tng_layer_permission "
							. "( "
								. "uid, "
								. "layer_id "
							. ") "
							. "VALUES "
							. "( "
								. $uid . ", "
								. $layer_id
							. "); "
							. "INSERT INTO tng_layer_ms_class "
								. "( "
									. "layer_id, "
									. "class_name, "
									. "class_color_r, "
									. "class_color_b, "
									. "class_color_g "
								. ") "
								. "VALUES "
									. "( "
										. $layer_id . ", "
										. "'default_class', "
										. "46, "
										. "139, "
										. "87"
									.");";
				
				$this->dbconn->connect();

				$result = pg_query($this->dbconn->conn, $sql_str);

				if(!$result){
					echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
					$layer_id = -1;
				}	
				$this->dbconn->disconnect();
				
			}
		}
		
		return $layer_id;
	}

	///
	/// get_max_id()
	/// get the unique id of the
	/// last feature inserted in 
	/// the given table. note that
	/// this method only needs the table
	/// name. the PK column is queried
	/// from the postgres system views,
	/// thus making this independent of
	/// any naming issues. the name of the
	/// column is returned by reference to
	/// the caller
	///
	function get_max_id($table_name, &$pk_col_name){
		$max_id = -1;
		
		/* ------------------------------------------------------------ 
		NOTE: this was a nice thought, to make things very
		generic. it would allow the primary key of the
		table containing the geometries to be renamed on the
		fly and the code would still work. 
		
		there is one snag - to be able to execute the query below,
		the tng_readwrite (or whoever is connecting to the db)
		needs to own the table(s) that are being queried for.
		i.e. the geometry tables would need to be owned by
		the tng_readwrite user. bearing all things in mind, it
		would be rather unsecure to give such authority to
		essential tables to a user whose password may be
		exposed while connecting from the web. 
		
		so, instead i have decided that the name of the 
		primary key column will be stored in the
		tng_spatial_data table and will be queried when the
		destination layer is queried.
		
		// first use the information_schema.key_column_usage
		// and information_schema.column_name
		// to get the name of the primary key
		// column in the given table
		$sql_str = "SELECT "
		      			. " key_column_usage.column_name "
					. "FROM "
		    			. "information_schema.key_column_usage "
		    			. "INNER JOIN information_schema.table_constraints " .
		"ON information_schema.key_column_usage.constraint_name = information_schema.table_constraints.constraint_name "
					. "WHERE " 
						. "key_column_usage.table_name = '" . $table_name . "' "
						. "AND " 
						. "table_constraints.constraint_type = 'PRIMARY KEY' ";
		$this->dbconn->connect();
			
		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{
			$pk_col_name = pg_fetch_result($result, 0, 0);
			$this->dbconn->disconnect();
		}
		------------------------------------------------------------*/
		
		// now that we have the column name, 
		// construct a query to get the
		// max value of the column
		$sql_str = "SELECT "
						. "max(" . $this->pk_col_name . ") "
				 	. "FROM "
						. $table_name;
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{
			$max_id = pg_fetch_result($result, 0, 0);
			$this->dbconn->disconnect();
		}
		
		return $max_id;
	}

	///
	/// add_attributes_to_db()
	/// build an sql string to insert
	/// the attributes of a feature into
	/// the appropriate attribute table
	/// 
	function add_attributes_to_db($src_feature, $feature_id, $fk_col_name){
		$success = false;
		$num_fields = OGR_F_GetFieldCount($src_feature);
		$sql_str = "INSERT INTO "
						. $this->attr_table_name
					. "(" . $fk_col_name . ", ";
		
		// append all field names to the
		// sql string first
		for( $i = 0; $i < $num_fields; $i++){
			$field_defn = OGR_FD_GetFieldDefn(OGR_L_GetLayerDefn($this->ogr_src_layer), $i);
			$f_name = strtolower(OGR_Fld_GetNameRef($field_defn));
			$sql_str .= $f_name;
			if($i < $num_fields -1)
				$sql_str .= ", ";
		}	
		
		$sql_str .= ") VALUES ( " . $feature_id . ", ";
		// append field values				
		for( $i = 0; $i < $num_fields; $i++){
			$field_defn = OGR_FD_GetFieldDefn(OGR_L_GetLayerDefn($this->ogr_src_layer), $i);
			$f_name = strtolower(OGR_Fld_GetNameRef($field_defn));
			$f_type = OGR_Fld_GetType($field_defn);
			$f_value = OGR_F_GetFieldAsString($src_feature, $i);
			// surround the value with quotes if
			// the type is string
			if($f_type == OFTString)
				$sql_str .=  "'" . str_replace("'", "''", $f_value) . "'";
			else if($f_value == "")
				$sql_str .= "NULL";
			else
				$sql_str .= $f_value;
			if($i < $num_fields -1)
				$sql_str .= ", ";
		}
		
		$sql_str .= ")";
		
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result)
			echo "An error occurred while executing the query: " . $sql_str .pg_last_error($this->dbconn->conn) . "\n\n";
		else
			$success = true;
			
		$this->dbconn->disconnect();
		
		return $success;
	}
}

?>