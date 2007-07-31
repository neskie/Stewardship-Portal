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
	var $fname;
	var $lname;
	var $email;
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
					. "tng_user.passwd, "
					. "tng_user.fname, "
					. "tng_user.lname, "
					. "tng_user.email "
				. "FROM "
					. "tng_user "
				. "WHERE " 
					. "uname ='". $this->uname	. "'";
				
		$this->dbconn->connect();
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query"
				. pg_last_error($this->dbconn->conn) . "\n"
				. $sql_str;
			$this->dbconn->disconnect();
		}else{ // successfuly ran the query
			// if no rows are found, then no match exists 
			// for the provided user name
			if(pg_num_rows($result) != 0){
				$db_passwd = pg_fetch_result($result, 0, 'passwd');
				// check if password matches, if
				// it does, set validate to true and
				// set the uid value
				if($db_passwd == md5($passwd)){
					$validate = true;
					$this->uid = pg_fetch_result($result, 0, 'uid');
					$this->fname = pg_fetch_result($result, 0, 'fname');
					$this->lname = pg_fetch_result($result, 0, 'lname');
					$this->email = pg_fetch_result($result, 0, 'email');
				} 
			}
		}
		$this->dbconn->disconnect();
		return $validate;
	}
	
	///
	/// is_admin()
	/// check whether the user logged in is an
	/// admin user or not.
	/// for now, just check the user name.
	/// a more appropriate method would be to check 
	/// in the db if the given user is in the admin
	/// group
	///
	function is_admin(){
		if($this->uname == "tng")
			return "true";
		else
			return "false";
	}
}
?>