<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	class_dbconn.php

desc:	to manage connections to the db
---------------------------------------------------------------*/

class DBConn{
	var $conn_str;
	var $mapserver_conn_str;
	var $conn;
	
	///
	/// constructor
	/// initialize connection string
	///
	function DBConn(){
		$this->conn_str = "host=127.0.0.1 dbname=tng_dev user=tng_readwrite password=tng_readwrite";
		$this->mapserver_conn_str = "host=127.0.0.1 dbname=tng_dev user=tng_readonly password=tng_readonly";
	}
	
	///
	/// connect()
	/// try and connect to the db
	///
	function connect(){
		$this->conn = pg_connect($this->conn_str) or die('Could not connect - DBConn line 16');
	}
	
	///
	/// disconnect()
	/// disconnect from the db
	///
	function disconnect(){
		pg_close($this->conn);
	}
}
?>