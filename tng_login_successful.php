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
<title>Stewardship Portal Main Page</title>

<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar3 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>

<body>
		<div id="header"><?php include_once('top_div.html'); ?></div>
		<div id="container">
			<div id="content" class="column">
				<h1 class="pageName"> Welcome to The Stewardship Portal </h1>
    			<p>
    			You have successfully logged in to The Stewardship Portal. The list
				below outlines the functions that are accessible from the menu on the left.
    			</p>
					<ul>
      			<li> <h1 class="subHeader"> Find Submissions </h1></li>
      				<p> This function allows you to search through the Submissions 
        					in the Portal. Use this function to identify the appropriate 
        					submission to which you should add an attachment/addendum. 
        					Note that you will not see submissions from other users unless 
        					you the appropriate have permissions. 
      				</p>
      			<li><h1 class="subHeader"> Fill A Form </h1> </li>
      				<p> Information is submitted to the Stewardship Portal by filling in a form. 
      					<br/>
        					For example, to initiate a Forestry Referral, 
        					click on <b>Fill A Form</b> and select the <b>Forestry Referral</b> 
        					Form from the list. 
        				</p>
      			<li> <h1 class="subHeader"> Map Layers </h1> </li>
      				<p> To view the spatial layers that you are permitted to 
        					see, click on the <b>Map Layers</b> link. <br/>
        					You will be able to search through the list of layers by name and select 
        					which layers you would like to see in the Map Viewer, and then you can 
        					launch the Map Viewer from here. 
        				</p>
      			<li> <h1 class="subHeader"> View Available Schemas </h1></li>
	      			<p> The Stewardship Portal requires that all shapefiles submitted 
	      				meet specific formatting requirements, known as schemas, 
	      				which have already been established. If you are having trouble 
	      				uploading shapefiles to the portal, please view the schema 
	      				requirements for the data-type you are trying to upload.
	      			</p>
      			<li> <h1 class="subHeader"> View Form Fields </h1> </li>
      				<p> Click on <b>View Form Fields</b> to see all the fields on each form. 
	      				It is a good idea to ensure you have all the relevant information ready to 
	      				enter into the portal so you don't make an incomplete submission.
      				</p>
					<li> <h1 class="subHeader"> Document Downloads </h1> </li>
      				<p> Download shapefile templates and blank forms from the 
	      				<b>Document Downloads</b> page. A user ID is not required for 
	      				access to this page.
      				</p>
   				<li> <h1 class="subHeader"> Logout </h1></li>
      				<p> Please ensure you end your Stewardship Portal Session by logging out.</p>
					</ul>
  
			</div>
			<div id="left" class="column"> 
     		 <?php include_once('tng_links_post_login.php');?>
     		</div>
						
			<div id="right" class="column"> 
			 <?php include_once('links_sidebar2.html');?>
			</div>
		</div> <!-- end container -->
		<div id="footer">
			<?php include_once('links_footer.html');?>
		</div>
	     
</body>
</html>
