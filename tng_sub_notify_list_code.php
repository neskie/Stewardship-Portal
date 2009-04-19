<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Apr 10, 2007
file:	tng_sub_notify_list.php

desc:	backend script to allow users to select
		which users should be notified about a 
		submission

---------------------------------------------------------------*/
include_once('classes/class_login.php');
include_once('classes/class_dbconn.php');
include_once('classes/class_app_config.php');
// since php4 does not have built in json support,
// make sure the json extension is loaded
if(phpversion() < 5.0)
	dl('json.so');
	
session_start();

// do not process any requests if the 
// session variable is not set
//if(!isset($_SESSION['obj_login']))
//	return;

// globals

if(isset($_REQUEST['ajax_req'])){
	switch($_REQUEST['ajax_req']){
		case "get_user_list":
			$searchStr = $_REQUEST['query'];
			$userEmailList = getUserEmailList($searchStr);
			echo json_encode($userEmailList);
		break;
		case "get_notification_list":
			$subID = $_REQUEST['submission_id'];
			// we deal with notification lists at the 
			// parent level. Check if this submission
			// is not a parent.
			if(!isParent($subID))
				// not parent, get the parent
				$subID = getParent($subID);

			$notificationList = getSubNotificationList($subID);
			echo  json_encode($notificationList);
		break;
		case "append_notification_list":
			$subID = $_REQUEST['submission_id'];
			// we deal with notification lists at the 
			// parent level. Check if this submission
			// is not a parent.
			$parentID = -1;
			if(!isParent($subID))
				// not parent, get the parent
				$parentID = getParent($subID);
			$notificationList =  json_decode(str_replace("\\", "", $_POST['notification_list']), true);
			$sendNotificationEmail = ($_REQUEST['notify'] == "true");
			foreach($notificationList as $email)
				addToNotificationList($parentID == -1 ? $subID : $parentID, $email);
			if($sendNotificationEmail)
				sendNotificationEmail($subID, $parentID);	
		break;
		case "delete_from_notification_list":
			$subID = $_REQUEST['submission_id'];
			// we deal with notification lists at the 
			// parent level. Check if this submission
			// is not a parent.
			if(!isParent($subID))
				// not parent, get the parent
				$subID = getParent($subID);
			$email = $_REQUEST['email'];
			delFromNotificationList($subID, $email);
		break;

	}
}


/*---------------------------------------------------------------*/
// functions
/*---------------------------------------------------------------*/

///
/// isParent
/// Check to see if the given submission is a parent
///
function isParent($subID){
	$dbconn =& new DBConn();
	$isParent = false;
	$sqlStr = "SELECT "
				. "pid "
			. "FROM "
				. "tng_form_submission "
			. "WHERE "
				. "form_submission_id = " . $subID;

	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sqlStr);	
	if(!$result){
		echo "An error occurred while executing the query - " 
				. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	// valid submission id ?	
	if(pg_num_rows($result) == 0)
		return NULL;

	if(pg_fetch_result($result, 0, 'pid') == -1)
		$isParent = true;

	$dbconn->disconnect();
	return $isParent;
}	

///
/// getParent
/// get the parent submission for the current
/// submission
///
function getParent($subID){
	$dbconn =& new DBConn();
	$parentID = -1;
	$sqlStr = "SELECT "
				. "pid "
			. "FROM "
				. "tng_form_submission "
			. "WHERE "
				. "form_submission_id = " . $subID;

	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sqlStr);	
	if(!$result){
		echo "An error occurred while executing the query - " 
				. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	
	// valid submission id ?	
	if(pg_num_rows($result) == 0)
		return NULL;

	$parentID = pg_fetch_result($result, 0, 'pid');
	return $parentID;
}

///
/// getUserEmailList
/// get list of all users in the Portal
///
function getUserEmailList($searchStr){
	$userList = array();
	$dbconn =& new DBConn();
	$groups = array();
	$sqlStr = "SELECT "
					. "uid, "
					. "fname || ' ' || lname AS name, "
					. "email "
				. "FROM "
					. "tng_user "
				. "WHERE "
					. "active = 'true' "
					. "AND ("
						. "fname LIKE '" . $searchStr . "%' "
						. "OR "
						. "email LIKE '" . $searchStr . "%' "
					. ") "
				. "ORDER BY "
					. "email ";
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sqlStr);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$nItems = pg_num_rows($result);
	for($i = 0; $i < $nItems; $i++){
		$userList[$i] = array(
							"id" => pg_fetch_result($result, $i, 'uid'),
							"name" => pg_fetch_result($result, $i, 'name'),
							"email" => pg_fetch_result($result, $i, 'email')
							);
	}
	$dbconn->disconnect();
	return $userList;

}

///
/// getSubNotificationList
/// Given a submission ID, get the list of
/// email addresses associated with notification
///
function getSubNotificationList($subID){
	$notificationList = array();
	$dbconn =& new DBConn();
	$groups = array();
	$sqlStr = "SELECT "
					. "id, "
					. "email "
				. "FROM "
					. "tng_submission_notification "
				. "WHERE "
					. "sub_id = " . $subID
				. "ORDER BY "
					. "email ";
					
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sqlStr);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return NULL;
	}
	$nItems = pg_num_rows($result);
	for($i = 0; $i < $nItems; $i++){
		$notificationList[$i] = array(
							"id" => pg_fetch_result($result, $i, 'id'),
							"email" => pg_fetch_result($result, $i, 'email')
							);
	}
	$dbconn->disconnect();
	return $notificationList;
}

///
/// addToNotificationList
/// add an email address to the notification
/// list associated with a submission
///
function addToNotificationList($subID, $email){
	$dbconn =& new DBConn();
	// check if the email address is already
	// associated with the submission
	$sqlStr = "SELECT "
					. "sub_id " 
				. "FROM "
					. "tng_submission_notification "
				. "WHERE "
					. "sub_id = " . $subID . " " 
					. "AND email ='" . $email . "' ";
					
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sqlStr);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	// if a result was found, then there is no need
	// to insert this record again.
	// otherwise, insert the record.
	if(pg_num_rows($result) == 0){
		$sqlStr = "INSERT INTO "
						. "tng_submission_notification "
							. "( "
								. "sub_id, "
								. "email "
							. ") "
						. "VALUES "
							. "( "
								. $subID . ", "
								. "'" . $email . "' "
							. ") ";
		$result = pg_query($dbconn->conn, $sqlStr);
		if(!$result){
			echo "An error occurred while executing the query - " 
				. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
			$dbconn->disconnect();
			return false;
		}
	}
	$dbconn->disconnect();
	return true;
}

///
/// delFromNotificationList
/// delete given email address from the notification
/// list associated with a submission.
///
function delFromNotificationList($subID, $email){
	$dbconn =& new DBConn();
	$sqlStr = "DELETE FROM "
					. "tng_submission_notification "
				. "WHERE "
					. "sub_id = " . $subID . " " 
					. "AND email ='" . $email . "' ";
					
	$dbconn->connect();
	$result = pg_query($dbconn->conn, $sqlStr);
	if(!$result){
		echo "An error occurred while executing the query - " 
			. $sqlStr ." - " . pg_last_error($this->dbconn->conn);
		$dbconn->disconnect();
		return false;
	}
	$dbconn->connect();
	return true;
}

///
/// readMessageFromFile
/// given the file path, read the message into a local
/// variable and return it.
///
function readMessageFromFile($filepath){
	$handle = fopen($filepath, "r");
	$messageBuffer = "";
	if ($handle) {
		$messageBuffer = fread($handle, filesize($filepath));
		fclose($handle);
	}

	return $messageBuffer;	
}

///
/// sendNotificationEmail
/// send a notification Email to all addresses associated
/// with the given submission.
///
function sendNotificationEmail($subID, $pid){
	$notificationList = getSubNotificationList($pid != -1 ? $pid : $subID);
	// genrate 'from' list 
	$fromList = "";
	foreach($notificationList as $tuple)
		$fromList .= $tuple['email'] . ",";
	// generate subject
	$subject = "Submission Successful - " . $subID; 
	if($pid != -1) 
		$subject .= " - Amendment to " . $pid;

	// read template message from file into local array
	$messageTemplate = readMessageFromFile($_SESSION['app_config']->confirmation_email_template);
	// print list of failed files to be appended to the message
	$failed_files = "";
	if(isset($_SESSION['failed_files'])){
		foreach($_SESSION['failed_files'] as $failed_file)		
			$failed_files = "- " . $failed_file . "\n";
	}

	// print list of successful files to be appended to the message
	$successful_files = "";
	if(isset($_SESSION['successful_files'])){
		foreach($_SESSION['successful_files'] as $successful_file)		
			$successful_files = "- " . $successful_file . "\n";
	}
	// messageTemplate is formatted to be fed into sprintf. it 
	// requires the following arguments:
	// 1. User's name
	// 2. Submission ID (parent ID if applicable)
	// 3. Submission ID (again)
	// 4. List of failed files
	// 5. List of successful files

	$emailBody =  sprintf($messageTemplate, 
							$_SESSION['obj_login']->fname . " " . $_SESSION['obj_login']->lname,
							$pid != -1 ? $pid : $subID,
							$pid != -1 ? $pid : $subID,
							$failed_files,
							$successful_files);

	// additional headers
	$headers = "From: portaladmin@tsilhqotin.ca\r\n"
				. "Cc: portaladmin@tsilhqotin.ca\r\n";

	mail($fromList, $subject, wordwrap($emailBody,70), $headers);
}

?>
