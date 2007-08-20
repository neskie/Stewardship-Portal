<?php
/*---------------------------------------------------------------
author:	alim karim
date:	August 20th, 2007
file:	tng_logout.php

desc:	webpage to log a user out of the TNG portal
---------------------------------------------------------------*/
//header('Pragma: no-cache'); 
//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
// simply unset the session variable
// and redirect to the main page
session_start();
$_SESSION['obj_login'] = NULL;
unset($_SESSION['obj_login']);
echo "<META HTTP-EQUIV='Refresh' Content='0; URL=index.php'>";  
?>
