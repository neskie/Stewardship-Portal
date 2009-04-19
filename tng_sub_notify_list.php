<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 10, 2007
file:	tng_sub_notify.php

desc:	webpage to give the user an interface to 
		list/add contacts to be notified when any 
		activity takes place on this submission.

		the administrator can use this page on existing
		submissions to modify the notification list.

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
<title>Notification</title>

<script type="text/javascript" src="ext-2.2/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="ext-2.2/ext-all-debug.js"></script>
<script type="text/javascript" src="tng_sub_notify_list.js"></script>
<script type="text/javascript">
	var is_new_sub;
	var sub_id = -1;
	var uid = -1;
	var user_email = "";
	var is_admin = false;
<?php
	// make sure requests for this page are
	// only coming from valid referrer pages that
	// have set the appropriate session variable.
	// this prevents the page from being accessed 
	// by typing in the url
	/*
	if(isset($_SESSION['sub_notify_referrer'])){
		if($_SESSION['sub_notify_referrer'] == "tng_assign_sub_permission.php")
			echo "is_new_sub = true;";
		//unset($_SESSION['assign_sub_perm_referrer'] );
	}
	else{
		echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login_successful.php'>"; 
		return;
	}
	*/	
	// set submission id variable
	// from $_GET
	echo "sub_id = " . $_GET['sub_id'] . ";";
	// set uid from session variable
	echo "uid = " . $_SESSION['obj_login']->uid . ";";
	// set email address
	echo "user_email = '" . $_SESSION['obj_login']->email . "';";
	if($_SESSION['obj_login']->is_admin() =="true")
		echo "is_admin = true; ";
?>
</script>

<script type="text/javascript">
	Ext.onReady(sub_notify.app.init, sub_notify.app);
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
				<h1 class="pageName"> Notification List</h1>
				<p class="bodyText">
					From the list below, select the users 
					that you would like to allow to be notified about this submission.
				</p>
				<br/>
				<p>
					The email addresses that appear in the grid below are already part
					of the notification list.
				</p>
				<br/>
				<div id="search_div">
					<input id="search_combo" size=30/>
					<div id="add_button"></div>
				</div>
				<br/>
				<br/>
				<div id="grid_div">
				</div>
				<br/>
				<div id="submit_button"></div>
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
