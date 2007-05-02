<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 05, 2007
file:	class_form.php

desc:	abstraction of a form

notes:	each element of the 'files' array contains two
		values: 
		[0] => the temporary name of the file as known
		by php on the server. this is only useful for
		moving the file from the temp location to a 
		persistent one.
		[1] => the actual name of the file. this will be
		used to name the file
		
		this class has been extended so that along with the
		form id, the constructor can also recieve a
		form submission id, in which case the values for
		each field can also be queried. the query for the
		field values is a left join, meaning that if no 
		submission id is passed it, the values will be null,
		which is what we want anyway.
		
---------------------------------------------------------------*/
include_once('class_field.php');
include_once('class_dbconn.php');
include_once('class_spatial_layer.php');

class Form{
	var $id;
	var $name;
	var $fields;
	var $files;
	var $dbconn;
	
	//
	/// constructor
	/// instantiate a form object
	/// note that the second arg is 
	/// optional
	///
	function Form($id, $submission_id = -1){
		$this->id = $id;
		$this->fields = array();
		$this->files = array();
		$this->dbconn = new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object - class_form.php:26');
		// fill the rest of the attributes by
		// querying the db
		$this->get_form_attributes();
		$this->get_form_fields($submission_id);
	}
	
	///
	/// get_form_attributes()
	/// get form attributes such as the name,
	/// etc. from the db
	/// for now we are just retrieving the name
	///
	function get_form_attributes(){
		$sql_str = "SELECT "
					. "form_name "
				. "FROM "
					. "tng_form "
				. "WHERE "
					. "form_id = " . $this->id;
					
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - class_form.php:47 " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{ // successfuly ran the query
			// since the form id will be passed in
			// from the form containing the drop down
			// list, we can be sure that the sql statement
			// will return a result - unless some 
			// dumbass deletes the form in between the page calls.
			$this->name = pg_fetch_result($result, 0, 0);
		}
	}
	
	///
	/// get_form_fields()
	/// populate the fields array with 
	/// fields belonging to this form.
	/// if a valid submission id is passed
	/// in, then the values that were 
	/// filled for each field for the
	/// submission are also queried.
	/// since the query is a left join, 
	/// an invalid submission id will
	/// mean that the field values will
	/// be null.
	///
	function get_form_fields($submission_id){
		$sql_str = "SELECT "
						. "tng_form_field.field_id, "
						. "tng_form_field.field_name, "
						. "tng_form_field.field_type, "
						. "tng_form_field.field_label, "
						. "tng_form_field.field_rank, "
						. "tng_form_field.field_label_css_class, "
						. "tng_form_field.field_css_class, "
						. "tng_field_submission.field_value "
					. "FROM "
						. "tng_form_field "
						. "LEFT JOIN tng_field_submission ON tng_form_field.field_id = tng_field_submission.field_id " 
															."AND tng_field_submission.form_submission_id = " . $submission_id . " "
					. "WHERE "
						. "form_id = " . $this->id
					. " ORDER BY field_rank ASC";
		
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - class_form.php:84 " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{ // successfuly ran the query
			// for each result, create a field object and
			// store it in the fields array
			$rows = pg_num_rows($result);
			for($i = 0; $i < $rows; $i++){
				$this->fields[$i] = new Field(pg_fetch_result($result, $i, 0),
										pg_fetch_result($result, $i, 1),
										pg_fetch_result($result, $i, 2),
										pg_fetch_result($result, $i, 3),
										pg_fetch_result($result, $i, 4),
										pg_fetch_result($result, $i, 5),
										pg_fetch_result($result, $i, 6),
										pg_fetch_result($result, $i, 7));
			}
		}	
	}
	
	///
	/// generate_xml()
	/// generate an xml schema of a form object
	/// and its fields.
	/// current schema is as follows:
	///	<form>
	///		<form_id> 1 </form_id>
	///		<form_name> sample </form_name>
	///		
	///		<field>
	///			<field_id> 				2 				</field_id>
	///			<field_name>			address 		</field_name>
	///			<field_type>			text 			</field_type>
	///			<field_label>			Address:		</field_label>
	///			<field_rank>			1				</field_rank>
	///			<field_label_css_class>	label css class </field_label_css_class>
	///			<field_css_class>		field css class </field_css_class>
	///			<field_value>			4878 1st ave 	</field_value>
	///		</field>
	///		<field>
	///			<field_id>			 	3 				</field_id>
	///			<field_name>			some field		</field_name>
	///			<field_type>			text 			</field_type>
	///			<field_label>			Label:			</field_label>
	///			<field_rank>			2				</field_rank>
	///			<field_label_css_class>	label css class </field_label_css_class>
	///			<field_css_class>		field css class	</field_css_class>
	///			<field_value>			48 2nd ave	 	</field_value>
	///		</field>
	/// <form>
	///
	function generate_xml(){
		$xml_txt = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
				 . "<form>\n"
				 . "<form_id> " 		. $this->id . " </form_id>\n"
				 . "<form_name> "	. $this->name . " </form_name>\n";

		// loop through all fields
		$num_fields = count($this->fields);
		for($i = 0; $i < $num_fields; $i++){
			$xml_txt .= "<field>\n"
						. "<field_id>" 				. $this->fields[$i]->id 				. "</field_id>\n"
						. "<field_name>" 			. $this->fields[$i]->name 				. "</field_name>\n"
						. "<field_type>" 			. $this->fields[$i]->type 				. "</field_type>\n"
						. "<field_label>" 			. $this->fields[$i]->label 				. "</field_label>\n"
						. "<field_rank>" 			. $this->fields[$i]->rank 				. "</field_rank>\n"
						. "<field_label_css_class>"	. $this->fields[$i]->label_css_class	. "</field_label_css_class>\n"
						. "<field_css_class>" 		. $this->fields[$i]->css_class 			. "</field_css_class>\n"
						. "<field_value>" 			. $this->fields[$i]->value				. "</field_value>\n"
						. "</field>\n";
		}
		
		$xml_txt .= "</form>";
		return $xml_txt;
	}
	
	///
	/// set_field_value()
	/// find the field in question and then
	/// call the field class' set_field
	/// method on the found field object to
	/// set its value
	///
	function set_field_value($field_id, $value){
		
		$success = false;
		// when acessing a reference return
		// value, an '&' must be added after the 
		// assignment operator
		$field =& $this->get_field($field_id);
		// make sure the field was found
		if($field != NULL){
			$field->set_value($value);
			$sucess = true;
		}
		return $sucess;
	}
	
	///
	/// get_field()
	/// return the field object that has the
	/// given field_id.
	/// notes:
	/// (i) this function returns a direct
	/// 	reference to the field object, not
	///		a copy of the object
	/// (ii) the search is linear. if the number
	///		of fields gets large, then this
	///		will be an issue and a better searching
	///		algorithm will need to be implemented
	///
	function &get_field($field_id){
		$length = count($this->fields);
		for($i = 0; $i < $length; $i++){
			if($this->fields[$i]->id == $field_id){
				return $this->fields[$i];
			}
		}
		return NULL;
	}
	
	///
	/// save_form()
	/// create a form submission and use this
	/// form submission id (FK) to insert records
	/// into the field submission table.
	/// if a valid pid (parent id) is provided,
	/// link the newly created submission to the
	/// parent.
	/// once the fields are dealt with, 
	/// loop through the array of files and
	/// store them in a fixed location on the
	/// server. if  the files are shapefiles, use
	/// ogr to insert them into the corresponding
	/// spatial table.
	///
	function save_form($uid, $pid){
		$form_submission_id = -1;
		$parent_id = -1;
		if($pid != -1){
			// check if a valid submission id
			// was provided for the parent.
			if(!$this->check_pid($pid)){
				echo "invalid parent submission";
				return false;
			}
			else
				$parent_id = $pid;
		}
			
		// create a form submission record and get
		// the id of the record.
		$sql_str = "INSERT INTO tng_form_submission "
						. "( "
							. "uid, "
							. "form_id, "
							. "pid "
						. ") "
						. "VALUES "
						. "( "
							. $uid . ", "
							. $this->id . ", "
							. $parent_id
						. "); "
						. "SELECT " 
							. "max(form_submission_id) "
						. "FROM "
							. "tng_form_submission ;";
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query  " 
				. $sql_str . " - "
				. pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		// obtain the form submission id
		$form_submission_id = pg_fetch_result($result, 0, 0);
		
		// loop through the fields 
		// and insert each one into the db
		$length = count($this->fields);
		for($i = 0; $i < $length; $i++){
			// no point in storing blank fields, we can
			// easily query these using a left join
			if($this->fields[$i]->value != ""){
				if(!($this->submit_field_to_db($form_submission_id, $this->fields[$i])))
					return false;
			}
		}	
		$this->dbconn->disconnect();
		
		$this->save_files($form_submission_id);
		
		return true;	
	}
	
	///
	/// check_pid()
	/// when the user provides a parent submission
	/// id that they wish to link this submission
	/// to, we must check to see if the id is valid
	/// i.e. whether it exists in the db
	///
	function check_pid($pid){
		$res = false;
		
		$sql_str = "SELECT "
						. "form_submission_id "
					. "FROM "
						. "tng_form_submission "
					. "WHERE "
						. "form_submission_id  = " . $pid;
					$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query " 
				. $sql_str . " - "
				. pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		if(pg_num_rows($result) == 1)
			$res = true;
		
		$this->dbconn->disconnect();
		
		return $res;
	}
	
	///
	/// submit_field_to_db()
	/// take the submission id (FK) and a field
	/// object and store the properties of the field
	/// object in the database.
	///
	function submit_field_to_db($form_submission_id, $field){
		$sql_str = "INSERT INTO tng_field_submission "
						. "( "
							. "form_submission_id, "
							. "field_id, "
							. "field_value "
						. ") "
						. "VALUES "
						. "( "
							. $form_submission_id . ", "
							. $field->id . ", "
							. "'" . $field->value . "' "
						. ");";
						
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		return true;				
	}
	
	///
	/// add_file_name
	/// add the temporary and actual names of a file 
	/// attached to this form to the form object
	///
	function add_file($tmp_name, $act_name){
		$file_elt = array($tmp_name, $act_name);
		array_push($this->files, $file_elt);
	}
	
	///
	/// save_files()
	/// go through the list of files added to this
	/// form and save them to a specific folder on the
	/// server. note that the path for saving the files
	/// is hard coded. this should be changed so that
	/// it is read from some table in the db.
	///
	function save_files($form_submission_id){
		$upload_path = "/home/karima/public_html/trunk/tng_uploads/";
		$length = count($this->files);
		
		for($i = 0; $i < $length; $i++){
			$prefix = uniqid(true) . "_";
			// check if this is a spatial file. if so,
			// call the appropriate method to deal
			// with spatial layers
			if(substr_count($this->files[$i][1], ".shp") == 1){
				// shapefile found, now find the
				// associated dbf and shx files
				// and move them to the tmp
				// directory
				if($this->move_spatial_files($this->files[$i][1], "/tmp/", $prefix)){
					$spatial_layer =& new SpatialLayer("/tmp/" . $prefix . $this->files[$i][1], $form_submission_id);
					// if the layer object was successfully
					// created, then validate the layer
					if($spatial_layer != NULL){
						if(!$spatial_layer->validate_layer())
							echo "the shape file " 
								. $this->files[$i][1]
								. " does not match the standard schema";
						// if the layer passes validation, then
						// load it into the db
						else if(!$spatial_layer->add_layer_to_db())
							echo "the shape file " 
								. basename($this->files[$i][1]) 
								. " could not be loaded"; 
					}
					
				}
			// look for non spatial files
			}else if (substr_count($this->files[$i][1], ".dbf") == 0
					&& substr_count($this->files[$i][1], ".shx") == 0){			
				$upload_name = $prefix .  $this->files[$i][1] ;
				if(!move_uploaded_file($this->files[$i][0], $upload_path . $upload_name))
					echo "the file " . $this->files[$i][1] . " could not be uploaded.<br>";
				else
					$this->create_file_record($form_submission_id, $this->files[$i][1], $upload_path . $upload_name);
			}
		}
	}
	
	///
	/// move_spatial_files()
	/// given the name of a shapefile, move
	/// the shapefile and any associated 
	/// dbf and shx files attached to this form
	/// to a temp location so that the spatial
	/// layer class can operate on them.
	///
	function move_spatial_files($shp_name, $upload_dir, $prefix){
		$basename = substr($shp_name, 0, strpos($shp_name, ".shp"));
		$dbf_name = $basename . ".dbf";
		$shx_name = $basename . ".shx";
			
		$length = count($this->files);
		for($i = 0; $i < $length; $i++){
			// should the file meet any of the
			// conditions, file_path is where the
			// file will be uploaded to
			$file_path = $upload_dir . $prefix . $this->files[$i][1];
			if($this->files[$i][1] == $dbf_name
				|| $this->files[$i][1] == $shx_name
				|| $this->files[$i][1] == $shp_name){
				if(!move_uploaded_file($this->files[$i][0], $file_path)){
					echo "the file " . $this->files[$i][1] . " could not be uploaded.<br>";
					return false;
				}
			}
		}
		
		return true;
	}
	
	///
	/// create_file_record()
	/// create a record for a non spatial file
	/// in the tng_file_submission table so 
	/// that this file is linked to the form
	///
	function create_file_record($form_submission_id, $file_name, $file_path){
		$sql_str = "INSERT INTO tng_file_submission "
					. "( "
						. "form_submission_id, "
						. "file_name, "
						. "file_path "
					. ") "
					. "VALUES "
					. "( "
						. $form_submission_id . ", "
						. "'" . $file_name . "', "
						. "'" . $file_path . "' "
					. ")";
		
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query " . pg_last_error($this->dbconn->conn);
			return false;
		}
		
		return true;			
	}
}
?>