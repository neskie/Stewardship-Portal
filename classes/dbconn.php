<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	class_dbconn.php

desc:	to manage connections to the db.
---------------------------------------------------------------*/

class DBConn{
	var $conn_str;
	var $conn;
		
	function DBConn(){
		$this->conn_str = "host=142.207.144.71 dbname=tng_dev user=tng_readonly password=tng_readonly";
		this->conn = pg_connect($conn_str) or die ('Could not connect - '
	}
}


?>