<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	class_login.php

desc:	to validate and store permissions associated with a user
		login.
---------------------------------------------------------------*/
include_once('class_dbconn.php');

class Login{
	var $dbconn;
	var $uid;
	var $uname;
	var $permission;
	
	///
	/// constructor
	/// instantiate dbconn object
	/// validate login
	///
	function Login($uname){
		$this->dbconn = new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object - class_login.php:23');
		// connection successful
		$this->uname = $uname;	
	}
	
	///
	/// validate_login()
	/// check if username exists
	/// and password matches supplied password
	///
	function validate_login($passwd){
		$validate = false;
		$sql_str = "SELECT "
					. "tng_user.uid, "
					. "tng_user.passwd "
				. "FROM "
					. "tng_user "
				. "WHERE uname ='"
					. $this->uname
				. "'";
				
		$this->dbconn->connect();
				
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query - class_login.php:50 " . pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
		}else{ // successfuly ran the query
			// if no rows are found, then no match exists 
			// for the provided user name
			if(pg_num_rows($result) != 0){
				$row = pg_fetch_row($result);
				// check if password matches, if
				// it does, set validate to true and
				// set the uid value
				if($row[1] == md5($passwd)){
					$validate = true;
					$this->uid = $row[0];
				} 
			}
		}

		return $validate;
	}
}
?>