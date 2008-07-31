<?php
/*---------------------------------------------------------------
author:	alim karim
date:		July 31, 2008
file:		class_app_config.php

desc:		class to read and store application config
			variables from db.
notes:	
---------------------------------------------------------------*/
include_once('class_dbconn.php');

class App_Config{
	var $db_conn;
	var $upload_path;
	var $mapfile_path;
	var $layer_config_path;
	var $mapservice_config_path;
	var $mapservice_name;
	var $map_agent;
	var $map_agent_launch_url;
	var $output_dir;

	///
	/// constructor
	/// initialize object. read values
	/// from db.
	///
	function App_Config(){
		$this->dbconn =& new DBConn();
		if($this->dbconn == NULL)
			die('Could not create connection object');
		// if the querying succeeds, unset the dbconn
		// object. we don't want it to be stored as part
		// of the session. (App_Config will be stored as)
		// a session
		if($this->get_db_variables())
			unset($this->dbconn);
	}
	
	///
	/// get_db_variables()
	/// get variables from db config table
	/// and store them in local variables
	///
	function get_db_variables(){
		$sql_str = "SELECT "
							. "var_name, "
							. "var_value "
					. "FROM "
							. "portal_config_variables ";
		
		$this->dbconn->connect();
		$result = pg_query($this->dbconn->conn, $sql_str);
		if(!$result){
			echo "An error occurred while executing the query - " 
				. $sql_str ." - " 
				. pg_last_error($this->dbconn->conn);
			$this->dbconn->disconnect();
			return false;
		}
		$n_rows = pg_num_rows($result);
		$vars = Array();
		for($i = 0; $i < $n_rows; $i++)
			$vars[pg_fetch_result($result, $i, 'var_name')] = pg_fetch_result($result, $i, 'var_value');
		$this->dbconn->disconnect();
		// set member variables from name/value pairs		
		$this->upload_path = $vars["upload_path"];
		$this->mapfile_path = $vars["mapfile_path"];
		$this->layer_config_path = $vars["layer_config_path"];
		$this->mapservice_config_path = $vars["mapservice_config_path"];
		$this->mapservice_name = $vars["mapservice_name"];
		$this->map_agent = $vars["map_agent"];
		$this->map_agent_launch_url = $vars["map_agent_launch_url"];
		$this->output_dir = $vars["output_dir"];
		return true;
	}
}
?>