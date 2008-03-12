<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 24, 2007
file:	tng_create_group.php

desc:	webpage to allow the administrator
		to create new groups
		
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
<title>Add Group</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_create_group.js"> </script>
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
			<form id="create_group" 
				name="create_group" 
				method="post" 
				enctype="multipart/form-data">
				<h1 class="pageName"> Add Group </h1>
				<p> 
				</p>
				<p> 
					Type in a name for the group you wish to create.
					<br/>
					Click the "Create Group" button to create the Group.
				</p>
				<p>
				<label style="width:100px;"> Group Name: </label>
				<input type="text"
						name="gname" 
						id="gname"
						size="45"/> 
				</p>
				<input type="button"
					value="Create Group"
					onClick="javascript: ajax_check_gname()"/>
			</form>
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
</body>
</html>