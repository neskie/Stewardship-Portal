<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Mar 22, 2007
file:	class_submission_layer.php

desc:	abstraction of a mapserver
		class associate with a 
		layer

notes:	
---------------------------------------------------------------*/

include_once('class_dbconn.php');

class Submission_Layer_Class{
	var $class_id;
	var $class_name;
	var $class_expr;
	var $class_symbol;
	var $class_color_r;
	var $class_color_g;
	var $class_color_b;
	var $dbconn;

	//
	/// constructor
	/// instantiate a submission layer
	/// class object
	///
	function Submission_Layer_Class($class_id){
		$this->class_id = $class_id;
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
		if(!$this->get_class_attributes())
			return NULL;
	}
	
	///
	/// get_class_attributes
	/// get the attributes of a layer
	/// class
	///
	function get_class_attributes(){
		$sql_str = "SELECT "
					. "class_name, "
					. "class_expr, "
					. "class_symbol, "
					. "class_color_r, "
					. "class_color_g, "
					. "class_color_b "
				. "FROM "
					. "tng_layer_ms_class "
				. "WHERE "
					. "class_id = " . $this->class_id;
		
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		// successfuly ran the query
		// store layer attributes
		$this->class_name = pg_fetch_result($result, 0, 0);
		$this->class_expr = pg_fetch_result($result, 0, 1);
		$this->class_symbol = pg_fetch_result($result, 0, 2);
		$this->class_color_r = pg_fetch_result($result, 0, 3);
		$this->class_color_g = pg_fetch_result($result, 0, 4);
		$this->class_color_b = pg_fetch_result($result, 0, 5);
		
		$this->dbconn->disconnect();
		
		return true;
	}
}