<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	class_login.php

desc:	to validate and store permissions associated with a user
		login.
		
		2008.07.31
		added create_connection and destroy_connection methods.
		these methods are called each time a db connection needs
		to be setup. it removes the need to have dbconn persistently
		store a connection object, which might be exposed when
		the login object is stored as a session variable.
		see http://trac.geoborealis.ca/ticket/28 
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
		$this->uname = $uname;	
	}
	
	///
	/// create_connection()
	/// connection setup. note that this method
	/// should be called each time before 'connect'
	/// is called
	///
	function create_connection(){
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
	}
	
	///
	/// destroy_connection()
	/// tear down connection by unsetting
	/// dbconn member
	///	 
	function destroy_connection(){
		//if($this->dbconn != null)
		//	$this->dbconn->disconnect();
		unset($this->dbconn);
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
					. "tng_user.email, "
					. "tng_user.active "
				. "FROM "
					. "tng_user "
				. "WHERE " 
					. "uname ='". $this->uname	. "'";
		$this->create_connection();
		$this->dbconn->connect();		
		$result = pg_query($this->dbconn->conn, $sql_str);
		
		if(!$result){
			echo "An error occurred while executing the query"
				. pg_last_error($this->dbconn->conn) . "\n"
				. $sql_str;
			$this->destroy_connection();
		}else{ // successfuly ran the query
			// if no rows are found, then no match exists 
			// for the provided user name.
			// also, make sure the current user is 'active'
			if(pg_num_rows($result) != 0
			&& pg_fetch_result($result, 0, 'active') == "t"){
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
		$this->destroy_connection();
		return $validate;
	}
	
	///
	/// is_admin()
	/// check whether the user logged in is an
	/// admin user or not.
	/// for now, just check the user name.
	/// a more appropriate method would be to check 
	/// in the db if the given user is in the admin
	/// group.
	/// note that since this function may be used to 
	/// assign the value to a JS variable, it is 
	/// necessary to return a string and not a boolean
	///
	function is_admin(){
		if($this->uname == "admin")
			return "true";
		else
			return "false";
	}
	
	///
	/// check to see if the user logged in
	/// belongs to the tng group
	///
	function is_tng_user(){
		$is_tng = false;
		$sql_str = "SELECT "
						. "tng_group.gname "
					. "FROM "
						. "tng_group_users "
						. "INNER JOIN tng_group ON tng_group_users.gid = tng_group.gid "
					. "WHERE "
						. "tng_group_users.uid = "  . $this->uid . " ";

		$this->create_connection();	
		$this->dbconn->connect();
		$result = pg_query($this->dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query"
				. pg_last_error($this->dbconn->conn) . "\n"
				. $sql_str;
			$this->dbconn->disconnect();
			$this->destroy_connection();
		}else{ // successfuly ran the query
			$n_groups = pg_num_rows($result);
			for($i = 0; $i < $n_groups; $i++){
				if(pg_fetch_result($result, $i, 'gname') == "1_TNG"){
					$is_tng = true;
					break;
				}
			}
			$this->dbconn->disconnect();
			$this->destroy_connection();
		}
		return $is_tng;
	}
}
?>
