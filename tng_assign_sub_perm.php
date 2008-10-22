<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 24, 2007
file:	tng_assign_sub_perm.php

desc:	webpage to give the user an interface to 
 		allow/disallow other users and groups from viewing
		this submission.
		
		the administrator can use this page on existing
		submissions to grant/revoke viewing privileges

		2008.10.21
		changed layout of user list to use an EXT tree
		control. see trac ticket #36 for details.
			
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ext-2.2/resources/css/ext-all.css" />
<title>Assign Submission Permissions</title>

<script type="text/javascript" src="ext-2.2/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="ext-2.2/ext-all.js"></script>
<script type="text/javascript" src="tng_assign_sub_perm-ext.js"></script>
<script type="text/javascript">
	var is_new_sub;
	var sub_id = -1;
	var uid = -1;
<?php
	// make sure requests for this page are
	// only coming from valid referrer pages that
	// have set the appropriate session variable.
	// this prevents the page from being accessed 
	// by typing in the url
	if(isset($_SESSION['assign_sub_perm_referrer'])){
		if($_SESSION['assign_sub_perm_referrer'] == "tng_display_form.php")
			echo "is_new_sub = true;";
		//unset($_SESSION['assign_sub_perm_referrer'] );
	}
	else{
		echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login_successful.php'>"; 
		return;
	}
		
	// set submission id variable
	// from $_GET
	echo "sub_id = " . $_GET['sub_id'] . ";";
	// set uid from session variable
	echo "uid = " . $_SESSION['obj_login']->uid . ";";
?>
</script>

<script type="text/javascript">
	Ext.onReady(assign_perm.app.init, assign_perm.app);
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
		
		<!-- end #sidebar2 -->
		<div id="content" class="column">
			<form id="user_checklist_form">
				<h1 class="pageName"> Assign Permissions </h1>
				<p class="bodyText">
					From the list below, select the users and/or groups
					that you would like to allow to see this submission.
				</p>
				<br/>
				<div id="user_tree">
				</div>
				<br/>
				<!-- <input type="button" class="button" onClick="submit_users($('user_checklist_form'))" value="Submit"/> -->
				<input type="button" class="button" onClick="assign_perm.app.submitUserList()" value="Submit"/>
			</form>
		</div>
		<div id="left" class="column">
    		<?php include_once('tng_links_post_login.php');?>
		</div>  
  		<div id="right" class="column">
			<?php include_once('links_sidebar2.html');?>
		</div>
	</div> <!-- end container-->
	<div id="footer">
	  <?php include_once('links_footer.html');?></div>
	</div>
	  <!-- end #footer -->
	</div>
	<!-- end #container -->
</body>
</html>
