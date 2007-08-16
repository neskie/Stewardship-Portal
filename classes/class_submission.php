<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Mar 22, 2007
file:	class_submission.php

desc:	abstraction of a submission

notes:	
---------------------------------------------------------------*/
include_once('class_form.php');
include_once('class_submission_file.php');
include_once('class_submission_layer.php');
include_once('class_dbconn.php');

class Submission{
	var $sub_id;
	var $sub_time;
	var $uname;
	var $form_id;
	var $dbconn;
	var $submission_files;
	var $submission_layers;
	var $child_submissions;
	
	//
	/// constructor
	/// instantiate a submission object
	///
	function Submission($sub_id){
		$this->sub_id = $sub_id;
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
			
		// create arrays to hold
		// submission_file
		// and submission_layer
		// objects
		$this->submission_files = array();
		$this->submission_layers = array();
		// child submission objects
		$this->child_submissions = array();	
	}
	
	function load_sub_details(){
		if(!$this->get_submission_attributes())
			return NULL;
		if(!$this->get_child_submissions())
			return NULL;
		if(!$this->get_submission_files())
			return NULL;
		if(!$this->get_submission_layers())
			return NULL;
	}
	
	///
	/// get_submission_attributes()
	/// get other attributes associated
	/// with a submission, such as the time
	/// of the submission, the user name of
	/// the user that made the submission
	///
	function get_submission_attributes(){
		$sql_str = "SELECT "
						. "tng_form_submission.form_submission_time, "
						. "tng_form_submission.form_id, "
						. "tng_user.uname "
					. "FROM "
						. "tng_form_submission "
						. "INNER JOIN tng_user ON tng_form_submission.uid = tng_user.uid "
					. "WHERE "
						. "tng_form_submission.form_submission_id = " . $this->sub_id;
		
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		// query succeeded, set attributes
		$this->sub_time = pg_fetch_result($result, 0, 0);
		$this->form_id = pg_fetch_result($result, 0, 1);
		$this->uname = pg_fetch_result($result, 0, 2);
		
		$this->dbconn->disconnect();
		return true;
	}
	
	///
	/// get_child_submissions()
	/// query the db for all submissions
	/// having this submission as a parent
	///
	function get_child_submissions(){
		$sql_str = "SELECT "
						. "form_submission_id "
					. "FROM "
						. "tng_form_submission "
					. "WHERE "
						. "pid = " . $this->sub_id;
		
		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		$n_children = pg_num_rows($result);
		
		// note that this will result in the child
		// submission querying for submissions to 
		// which IT is a parent, until we reach a 
		// parent that has no children.
		for($i = 0; $i < $n_children; $i++){
			$this->child_submissions[$i] =& new Submission(pg_fetch_result($result, $i, 0));
			if($this->child_submissions[$i] == NULL)
				return false;
		}
		
		// no need to disconnect -
		// pg_close() doesnt need to 
		// be called since it is called
		// automatically when a script
		// finishes execution
		//$this->dbconn->disconnect();
		
		return true;
	}
	
	///
	/// get_submission_files()
	/// get the list of files submitted with
	/// this submission
	///
	function get_submission_files(){
		$sql_str = "SELECT "
						. "file_submission_id "
					. "FROM "
						. "tng_file_submission "
					. "WHERE "
						. "form_submission_id = " . $this->sub_id;

		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}

		// successfuly ran the query
		// get the list of submission files and
		// create an object for each
		$n_files = pg_num_rows($result);
		for($i = 0; $i < $n_files; $i++){
			$this->submission_files[$i] = new Submission_File(pg_fetch_result($result, $i, 0));
			if($this->submission_files[$i] == NULL)
				return false;
		}
		
		$this->dbconn->disconnect();
		
		return true;
	}
	
	///
	/// get_submission_layers()
	/// get the list of spatial layers
	/// submitted with this submission
	///
	function get_submission_layers(){
		$sql_str = "SELECT "
						. "layer_id "
					. "FROM "
						. "tng_spatial_layer "
					. "WHERE "
						. "form_submission_id = " . $this->sub_id;

		$this->dbconn->connect();

		$result = pg_query($this->dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}

		// successfuly ran the query
		// get the list of submission layers and
		// create an object for each
		$n_files = pg_num_rows($result);
		for($i = 0; $i < $n_files; $i++){
			$this->submission_layers[$i] = new Submission_Layer(pg_fetch_result($result, $i, 0));
			if($this->submission_layers[$i] == NULL)
				return false;
		}
		
		$this->dbconn->disconnect();
		
		return true;
	}
	
	///
	/// generate_xml()
	/// generate xml representation of 
	/// a submission, its files and its
	/// spatial layers
	///
	/// the current schema being followed is:
	/// <submission>
	///		<id> 123 				</id>
	///		<form_id> 3				</form_id>
	///		<user> default			</user>
	///		<date> jan 12th 2007	</date>
	///		<file>
	///			<fid>	45					</fid>
	///			<fname> abcd.pdf			</fname>
	///			<path> /home/abcd/abcd.pdf	</path>
	///		</file>
	///		<layer>
	///			<lid>	861 			<lid>
	///			<lname>	latlong.shp		</lname>
	///			<view>	vi_forest_poly	<view>
	///		</layer>
	///		<children>
	/// 		<submission>
	///				<id> 123 				</id>
	///				<form_id> 3				</form_id>
	///				<user> default			</user>
	///				<date> jan 12th 2007	</date>
	///				<layer>
	///					<lid>	861 			<lid>
	///					<lname>	latlong.shp		</lname>
	///					<view>	vi_forest_poly	<view>
	///				</layer>
	///				<file>
	///				</file>
	///			</submission>
	///		</children>
	///	</submission>
	///
	function generate_xml(){
		$xml_txt = "<submission>\n"
					. "<id>" 		. $this->sub_id 	. "</id>\n"
					. "<form_id>" 	. $this->form_id 	. "</form_id>\n"
					. "<user>"		. $this->uname		. "</user>\n"
					. "<date>"		. $this->sub_time	. "</date>\n";
		
		//$xml_txt .= "<files>\n";
		// loop through and generate xml for files
		$count = count($this->submission_files);
		for($i = 0; $i < $count; $i++){
			$xml_txt .= 
					"<file>\n"
					. "<fid>" 	. $this->submission_files[$i]->file_submission_id 	. "</fid>\n"
			 		. "<fname>" . $this->submission_files[$i]->file_name 			. "</fname>\n"
					. "<path>"  . $this->submission_files[$i]->file_path 			. "</path>\n"
					. "</file>\n";
		}
		//$xml_txt .= "</files>\n";
		//$xml_txt .= "<layers>\n";
		// loop through and generate xml for layers
		$count = count($this->submission_layers);
		for($i = 0; $i < $count; $i++){
			$xml_txt .= 
					"<layer>\n"
					. "<lid>" 	. $this->submission_layers[$i]->layer_id 	. "</lid>\n"
			 		. "<lname>" . $this->submission_layers[$i]->layer_name	. "</lname>\n"
					. "<view>"  . $this->submission_layers[$i]->view_name 	. "</view>\n"
					. "</layer>\n";
		}
		//$xml_txt .= "<layers>\n";
		$xml_txt .= "<children>\n";
		$n_children = count($this->child_submissions);
		$child_xml = "";
		// recursively call this method to 
		// generate xml for all child 
		// submissions for which this submission
		// is a parent.
		for($i = 0; $i < $n_children; $i++)
			$child_xml .= $this->child_submissions[$i]->generate_xml();
		$xml_txt .= $child_xml;
		$xml_txt .= "</children>\n";
		$xml_txt .= "</submission>";
		
		return $xml_txt; 
	}
	
	///
	/// permit_layer_download()
	/// check to see if the form that created this submission
	/// has the allow_layer_download flag set to true.
	///
	function permit_layer_download(){
		$allow = false;
		$sql_str = "SELECT "
						. "tng_form.allow_layer_download "
					. "FROM "
						. "tng_form_submission "
						. "INNER JOIN tng_form ON tng_form_submission.form_id = tng_form.form_id "
					. "WHERE "
						. "tng_form_submission.form_submission_id = " . $this->sub_id;
		
		$this->dbconn->connect();
		$result = pg_query($this->dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}

		if(pg_fetch_result($result, 0, 'allow_layer_download') == "t")
			$allow = true;
		$this->dbconn->disconnect();				
		return $allow;
	}
}
?>