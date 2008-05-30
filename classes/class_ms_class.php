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
include_once('class_ms_style.php');

class Mapserver_Class{
	var $id;
	var $name;
	var $class_desc;
	var $expression;
	var $styles;
	var $dbconn;
	
	//
	/// constructor
	/// instantiate a submission layer
	/// class object
	///
	function Mapserver_Class($class_id){
		$this->id = $class_id;
		$this->styles = array();
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
		// get attributes and styles
		if(! ($this->get_attributes() && $this->get_styles()) )
			return NULL;
	}
	
	///
	/// get_attributes
	/// get the attributes of the
	/// mapserver class as stored in the
	/// db
	///
	function get_attributes(){
		$sql_str = "SELECT "
					. "name, "
					. "class_desc, "
					. "expression "
				. "FROM "
					. "tng_mapserver_class "
				. "WHERE "
					. "id = " . $this->id;
		
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		
		// successfuly ran the query
		// store class attributes
		$this->name = pg_fetch_result($result, 0, 'name');
		$this->class_desc = pg_fetch_result($result, 0, 'class_desc');
		$this->expression = pg_fetch_result($result, 0, 'expression');
		$this->dbconn->disconnect();
		
		return true;
	}
	
	///
	/// get_styles()
	/// get all mapserver styles associated
	/// with this class
	///
	function get_styles(){
		$sql_str = "SELECT "
						. "ms_style_id "
					. "FROM "
						. "tng_ms_class_ms_style "
					. "WHERE "
						. "ms_class_id = " . $this->id;
		$this->dbconn->connect();
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - " 
					. $sql_str ." - " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		// get style ids and create an object
		// for each one.
		$n_styles = pg_num_rows($result);
		for($i = 0; $i < $n_styles; $i++){
			$this->styles[$i] =& new Mapserver_Style(pg_fetch_result($result, $i, 'ms_style_id'));
			if($this->style[$i] == NULL){
				$this->dbconn->disconnect();
				return false;
			}
		}
		
		//$this->dbconn->disconnect();

		return true;
	}
}