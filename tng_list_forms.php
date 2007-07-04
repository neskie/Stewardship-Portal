<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_list_forms.php

desc:	webpage to display a list of forms that that user
		can select to fill out
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// includes
// include file which has all the php code associated
// with this page.
include_once('tng_list_forms_code.php');

ini_set("display_errors", 1);
ini_set("error_log", '/tmp/tng_dev_errors.txt');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Select Action</title>
<script language="javascript">

function submit_form(action_value){
	document.getElementById('form_action').value = action_value;
	document.forms[0].submit();
}

</script>
</head>
<body>
	<form id="tng_list_forms" method="POST" action="tng_list_forms.php">
		<div id="leftcol">
					Much effort has been made to ensure that 
				the layouts in the BlueRobot Layout Reservoir appear 
				as intended in CSS2 compliant browsers. The content 
				should be viewable, though unstyled, in other web browsers. 
				If you encounter a problem that is not listed as a known 
				issue, I am most likely not aware of it. Your help will 
				benefit the other five or six people who visit this site. 
		</div>
		<div id="content">
			<h2> Choose an Option</h2>
			<p> There are various functions available on this page. </p>
			<ul>
				<li> To fill in a form, please see the fill form section. </li> <br/>
			 	<li> 
					To view the submissions which you have permission to,
					please see the Submissions section.
				</li><br/>
				<li> 
					To see which layers are available to you and to view
					these layers in a map viewer, please
					see the Available Layers section.
				</li><br/>
				<li> 
					To manage permissions on various objects, see the
					Manage Permission section.
				</li>
			</ul>
		</div>
		<div id="content">
			<h3> 1. Select Form </h3>
			<p>
				In this section, you can select a form to be filled for
				submitting various types of data.
				</br>
				For example, to initiate a Forestry Referral, please select
				the Referral form from the list below. Upon completing the
				form, please click the Submit button.
				<br/>
				Please select the form that you would like to fill in from the list below.
			</p>
				<select id="form_id" name="form_id" style="width:150px;">
					<?php
						// fill drop down with form names
						for($i=0; $i < $form_list_size; $i++){
							printf("<option value=\"%d\"> %s </option>\n", $form_list[$i][0], $form_list[$i][1]);
						}
					?>
				</select>
				<input type="button" class="button" value="Display Form" onClick="javascript:submit_form('fill_form');"/>
		</div>
		<div id="content">
			<h3> 2. Search Submissions </h3>
			<p>
				Click on the Search Submissions button below
				to search through the Submissions in the Portal.
				<br/>
				Note that you will not see submissions from other
				users unless you the appropriate have permissions.
			</p>
			<input type="button" 
				class="button" 
				value="Search Submissions" 
				onClick="window.location = 'tng_search_submissions.php'"/>
		</div>
		<div id="content">
			<h3> 3. View Layers </h3>
			<p>
				To view the spatial layers that you are permitted
				to see, click on the button below. You will also be
				able to search through the list of layers by name
				and select which layers you would like to see
				in the map viewer.
			</p>
			<input type="button" class="button" value="View Available Layers" onClick="window.location = 'tng_select_layers.php'"/>
		</div>
		<div id="content">
			<h3> 4. Manage Permissions </h3>
			<p>
				Clicking on the button below will enable you to 
				manage permissions on various objects such 
				as layers, forms, etc. that a user has
				access to.
			</p>
			<input type="button" 
					class="button" 
					value="Manage Permissions" 
					onClick="window.location = 'tng_manage_permissions.php'"/>
		</div>
		<div id="content">
			<h3> 5. Add/Edit Users </h3>
			<p>
				To add new users to the system or to change
				passwords for existing users, click on the
				button below.
			</p>
			<input type="button" 
					class="button" 
					value="Add/Edit Users" 
					onClick="window.location = 'tng_add_edit_user.php'"/>
		</div>
		<div id="content">
			<h3> 6. Create Spatial Schema </h3>
			<p>
				Please click the button below to create
				a new spatial schema
			</p>
			<input type="button" 
					class="button" 
					value="Create Schema" 
					onClick="window.location = 'tng_create_schema.php'"/>
		</div>
		<div id="content">
			<h3> 7. View Spatial Schemas </h3>
			<p>
				Clicking on the button below will allow you
				to see all spatial schemas that are available
				in the system.
			</p>
			<input type="button" 
					class="button" 
					value="View Schemas" 
					onClick="window.location = 'tng_view_schema.php'"/>
		</div>	
		<input type="hidden" id="form_action" name="form_action"/>
	
	
		<div id="rightcol"> 
			Much effort has been made to ensure that 
		the layouts in the BlueRobot Layout Reservoir appear 
		as intended in CSS2 compliant browsers. The content 
		should be viewable, though unstyled, in other web browsers. 
		If you encounter a problem that is not listed as a known 
		issue, I am most likely not aware of it. Your help will 
		benefit the other five or six people who visit this site.  
		</div>
	</form>
</body>
</html>
