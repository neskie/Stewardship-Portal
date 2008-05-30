<?php
/*---------------------------------------------------------------
author:	alim karim
date:		May 23, 2008
file:		class_ms_style.php

desc:	abstraction of a mapserver
		style

notes:	
---------------------------------------------------------------*/

include_once('class_dbconn.php');

class Mapserver_Style{
	var $id;
	var $name;
	var $style_desc;
	var $symbol_name;
	var $symbol_size;
	var $angle;
	var $width;
	// fill color
	var $color_r;
	var $color_g;
	var $color_b;
	// outline color
	var $outlinecolor_r;
	var $outlinecolor_g;
	var $outlinecolor_b;
	// bg color
	var $bgcolor_r;
	var $bgcolor_g;
	var $bgcolor_b;
	var $dbconn;
	
	//
	/// constructor
	/// instantiate a mapserver style
	/// object
	///
	function Mapserver_Style($style_id){
		$this->id = $style_id;
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
		if(!$this->get_attributes())
			return NULL;
	}
	
	///
	/// get_attributes
	/// get the attributes of a style
	///
	function get_attributes(){
		$sql_str = "SELECT "
					. "name, "
					. "style_desc, "
					. "symbol_name, "
					. "symbol_size, "
					. "angle, "
					. "width, "
					. "color_r, "
					. "color_g, "
					. "color_b, "
					. "outlinecolor_r, "
					. "outlinecolor_b, "
					. "outlinecolor_g, "
					. "bgcolor_r, "
					. "bgcolor_g, "
					. "bgcolor_b "
				. "FROM "
					. "tng_mapserver_style "
				. "WHERE "
					. "id = " . $this->id;
		
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - " . $sql_str ." - " . pg_last_error($this->dbconn->conn);
			//$this->dbconn->disconnect();
			return false;
		}
		
		// successfuly ran the query
		// store attributes
		$this->name = pg_fetch_result($result, 0, 'name');
		$this->style_desc = pg_fetch_result($result, 0, 'style_desc');
		$this->symbol_name = pg_fetch_result($result, 0, 'symbol_name');
		$this->symbol_size = pg_fetch_result($result, 0, 'symbol_size');
		$this->angle = pg_fetch_result($result, 0, 'angle');
		$this->width = pg_fetch_result($result, 0, 'width');
		$this->color_r = pg_fetch_result($result, 0, 'color_r');
		$this->color_g = pg_fetch_result($result, 0, 'color_g');
		$this->color_b = pg_fetch_result($result, 0, 'color_b');
		$this->outlinecolor_r = pg_fetch_result($result, 0, 'outlinecolor_r');
		$this->outlinecolor_g = pg_fetch_result($result, 0, 'outlinecolor_g');
		$this->outlinecolor_b = pg_fetch_result($result, 0, 'outlinecolor_b');
		$this->bgcolor_r = pg_fetch_result($result, 0, 'bgcolor_r');
		$this->bgcolor_g = pg_fetch_result($result, 0, 'bgcolor_g');
		$this->bgcolor_b = pg_fetch_result($result, 0, 'bgcolor_b');
		$this->dbconn->disconnect();
		
		return true;
	}
}