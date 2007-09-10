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
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="style.css" type="text/css" />
<title>Document Downloads</title>

<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar3 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>

<body class="thrColHybHdr">

<div id="container">
  <div id="header">
    <?php include_once('top_div.html'); ?>
  </div>
  <!-- end #header -->
  
  <div id="sidebar1">
    <?php
		
 		if(isset($_SESSION['obj_login']))
			include_once('tng_links_post_login.php');
		else
			include_once('links_no_login.html');
	?>
  </div>  
  <!-- end #sidebar1 -->
  
  <div id="sidebar3">
	<?php include_once('links_sidebar_alternate.html');?>      
  </div>
  <!-- end #sidebar3 -->

  <div id="mainContent">
    <h1 class="pageName"> Document Downloads </h1>
    <p class="bodyText">
    	The documents below are available for download.
    </p>
	<p class="bodyText">
		<ol>
		      <li class="bodyText">
				<p><a href="PortalUserLoginFields.doc"> Portal Access </a></p>
                        <p>A user account is needed to log into the Stewardship Portal. Click on the link above, download and complete the word document then email it to the <a href="mailto:tsdgis@tsilhqotin.ca ">Portal 
                      Administrator</a> to receive access to the portal.</p>
			</li>
                  <li class="bodyText">
				<p><a href="portal_docs/forestry_block-specific_information_document.doc"> Forestry Referral Block Info </a></p>
                        <p>Please complete and attach this to your main forestry referral submission.</p>
			</li>
                  <li class="bodyText">
				<p><a href="TSDTNGPhotographFieldSheet_revised.doc"> Photograph GPS Field Sheet </a></p>
                        <p>Print this form and take it to the field to document GPS Waypoints and photo numbers. </p>
			</li>


		</ol>
	</p>
  </div>
	<!-- end #mainContent -->
	<!-- This clearing element should immediately follow 
    the #mainContent div in order to force the 
    #container div to contain all child floats -->
   <br class="clearfloat" />
   <div id="footer">
    <?php include_once('links_footer.html');?></div>
   </div>
  <!-- end #footer -->
  </div>
<!-- end #container -->
</body>
</html>
