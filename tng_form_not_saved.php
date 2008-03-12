<?php
/*---------------------------------------------------------------
author:	alim karim
date:	July 12, 2007
file:	tng_login_successful.php

desc:	webpage that will be displayed upon
		successful login
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
// include necessary classes used by this page
// OR scripts that are included BEFORE 
// start_session() is called
include_once('classes/class_login.php');
include_once('tng_check_session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="style-new.css" type="text/css" />
<title>Form Not Saved</title>

<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>

<body>
  <div id="header">
    <?php include_once('top_div.html'); ?>
  </div>
  <div id="container">
	<div id="content" class="column">
		<h1 class="pageName"> Error </h1>
    	<p class="bodyText">
    		The form that you filled out could not be saved successfully.
    	</p>  
   </div>
	<div id="left" class="column">
   	<?php include_once('tng_links_post_login.php');?>
  	</div>  
  	<div id="right" class="column">
     <?php include_once('links_sidebar2.html');?>
  	</div>
  </div> <!-- end  container --> 
   <div id="footer">
    <?php include_once('links_footer.html');?></div>
   </div>
  </body>
</html>
