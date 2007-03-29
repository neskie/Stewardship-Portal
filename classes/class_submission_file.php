<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Mar 22, 2007
file:	class_submission_file.php

desc:	abstraction of a file attached to
		a submission

notes:	
---------------------------------------------------------------*/

include_once('class_dbconn.php');

class Submission_File{
	var $file_submission_id;
	var $file_name;
	var $file_path;
	var $dbconn;
	
	//
	/// constructor
	/// instantiate a submission file object
	///
	function Submission_File($file_submission_id){
		$this->file_submission_id = $file_submission_id;
		$this->dbconn = new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
		if(!$this->get_file_attributes())
			return NULL;
	}
	
	//
	/// get_file_attributes
	/// get the attributes of a file submission
	///
	function get_file_attributes(){
		$sql_str = "SELECT "
					. "file_name, "
					. "file_path "
				. "FROM "
					. "tng_file_submission "
				. "WHERE "
					. "file_submission_id = " . $this->file_submission_id;
		
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		// successfuly ran the query
		// store file attributes
		$this->file_name = pg_fetch_result($result, 0, 0);
		$this->file_path = pg_fetch_result($result, 0, 1);
		$this->dbconn->disconnect();
		
		return true;
	}
	
}