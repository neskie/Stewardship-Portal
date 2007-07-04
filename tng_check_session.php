<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 24, 2007
file:	tng_check_session.php

desc:	script to check if session variable is set.
		
---------------------------------------------------------------*/
session_start();
// go to the login page
if(!isset($_SESSION['obj_login'])){
	echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login.php'>";
}
?>