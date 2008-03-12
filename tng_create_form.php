<?php
/*---------------------------------------------------------------
author:	alim karim
date:	May 14, 2007
file:	tng_create_form.php

desc:	webpage allowing administrator to create
		forms
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
// include necessary classes used by this page
// OR scripts that are included BEFORE 
// start_session() is called
include_once('classes/class_login.php');
// include script to check for 
// login session variable
include_once('tng_check_session.php');
// only allow access if the logged in user
// is an admin user
if($_SESSION['obj_login']->is_admin() == "false")
	echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login_successful.php'>";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<title>Create Form</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_create_form.js"> </script>
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
		<form id="tng_create_form" 
				name="tng_create_form" 
				method="post" 
				enctype="multipart/form-data">
			<h1 class="pageName">Create Form </h1>
			<ul class="bodyText">
				<li> 
					Enter a name for the Form you wish to 
					create.
				</li>
				<br/>
				<li> 
					Add Field objects to the Form representing the 
					fields that the Form will contain. Note that
					each Field should have a Name,  a Data Type and a Rank.
					<br>
					The Rank determines where in the page the Field will
					be displayed when a user is filling out the Form. 
					The lower the rank, the closer to the top
					the Field will be displayed.
				</li>
				<br/>
				<li> 
					Click on Create Form to create the Form.
				</li>
				
			</ul>
			<hr/>
			<br/>
			<p class="bodyText">
				<label style="width:105px"> <b> Form Name: </b></label>
				<input type="text" id="form_name" name="form_name" size="45"/>
				<br/>
			</p>
			<p class="bodyText">
				<b> Attributes: </b>
				<br/>
				<div id="fields" >
					<!--style="border:1px solid black; width=650px;padding:5px"-->
					<hr/>
					<!-- automatically populated-->
				</div>
				<br/>
				<input type="button"
						value="Add Field"
						onClick="javascript: add_field()"/>
			</p>
			<br/>
			<br/>
			<input type="button"
					class="button"
					value="Create Form"
					onClick="javascript: ajax_check_form_name()"/>
			<br/>
			<br/>
		</form>
	</div>
	<div id="left" class="column">
    	<?php include_once('tng_links_post_login.php');?>
  	</div>  
  	<div id="right" class="column">
  	  <?php include_once('links_sidebar2.html');?>    
  	</div>
  </div>
  <div id="footer">
    <?php include_once('links_footer.html');?>
  </div>
</body>
</html>
