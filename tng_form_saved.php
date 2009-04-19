<?php
/*---------------------------------------------------------------
author:	alim karim
date:	July 12, 2007
file:	tng_login_successful.php

desc:	webpage that will be displayed upon
		successful login

notes:	2009.04.19
		Added list of successful files to be displayed on 
		this page.
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
<title>Form Saved</title>
<link rel="stylesheet" type="text/css" href="ext-2.2/resources/css/ext-all.css" />
<script type="text/javascript" src="ext-2.2/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="ext-2.2/ext-all-debug.js"></script>
<script type="text/javascript">
	var failed_files = new Array();
	var successful_files = new Array();
	<?php
		// small snippet to set populate local array with 
		// any files that failed to load into the portal.
		if(isset($_SESSION['failed_files'])){
			foreach($_SESSION['failed_files'] as $file)
				echo "failed_files.push('" . $file . "');\n";
			unset($_SESSION['failed_files']);		
		}
		// do the same for successful files
		if(isset($_SESSION['successful_files'])){
			foreach($_SESSION['successful_files'] as $file)
				echo "successful_files.push('" . $file . "');\n";
			unset($_SESSION['successful_files']);
		}
	?>
	
	///
	/// init()
	/// populate html failed and successful file list
	/// from array
	///
	function init(){
		var domHelper = Ext.DomHelper;
		for(var i = 0; i < successful_files.length; i++)
			domHelper.append('successful_list', {tag: 'li', html: successful_files[i]});
		
		for(var i = 0; i < failed_files.length; i++)
			domHelper.append('failed_list', {tag: 'li', html: successful_files[i]});
		
	}
	
</script>
<script type="text/javascript">
	Ext.onReady(init, this);
</script>

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
	   <h1 class="pageName"> Form Saved Successfully </h1>
	   <p class="bodyText">
	   	The form that you filled out was saved successfully.
	   </p>
		<p class="bodyText">
			The following files were uploaded successfully into the Portal:
			<br/>
			<hr/>
			<ul id="successful_list">
				<!-- automatically populated -->
			</ul>
			<hr/>
		</p>
		<br/>
		<p>
			The following files were <b>NOT</b> uploaded into the Portal.  
			If the list contains a shapefile, please make sure that shapefile 
			matches a valid schema in the Portal.
			<br/>
			<br/>
			You will be sent an email shortly that identifies the successfully uploaded 
			and failed files, and identifies the Submission ID.  Please print or 
			otherwise save the forthcoming email for your records.  That email will also be 
			sent to recipients on the previous page’s notification list. 
 			<br/>
			<br/>
			Your next step is to review the information you submitted and, if this is a 
			new referral, please create a Name for this submission.  Refer to the manual 
			for step-by-step instructions.  (Find Submissions -> View Form Data and 
			Find Submissions -> Update Name). 
			<br/>
			<hr/>
			<ul id="failed_list">
				<!-- automatically populated -->
			</ul>
			<hr/>
		</p>
	  </div>
	  <div id="left" class="column">
	  		<?php include_once('tng_links_post_login.php');?>
	  </div>  
	  <div id="right" class="column">
	  		<?php include_once('links_sidebar2.html');?>      
	  </div>
	</div> <!-- end container -->  
  <div id="footer">
    <?php include_once('links_footer.html');?></div>
   </div>
  <!-- end #footer -->
  </div>
<!-- end #container -->
</body>
</html>
