<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<title>Untitled Document</title>
</head>

<body>
<table id="nav" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td >&nbsp;<br />
		 &nbsp;<br /></td>
        </tr>
        <tr>
          <td >
			<a href="javascript:window.location='tng_login_successful.php';" 
				class="navText">Stewardship Home</a></td>
        </tr>
        <tr>
          <td >
			<a href="javascript:window.location='tng_search_submissions.php';" 
				class="navText">Find Submissions</a>
			</td>
        </tr>
		<tr>
          <td >
			<a href="javascript:window.location='tng_list_forms.php';" 
				class="navText">Fill A Form</a>
			</td>
        </tr>
        <tr>
          <td ><a href="javascript:window.location='tng_select_layers.php';" 
				class="navText">Map Layers</a>
		  </td>
        </tr>
		<tr>
          <td >
			<a href="javascript:window.location='tng_view_schema.php';" 
				class="navText">View Available Schemas </a>
		  </td>
        </tr>
		<tr>
          <td ><a href="javascript:window.location='tng_view_form_details.php';" 
				class="navText">View Form Fields</a>
		  </td>
        </tr>
		<tr>
          <td ><a href="javascript:window.location='tng_doc_links.php';" 
				class="navText">Document Downloads</a>
		  </td>
        </tr>
		<tr>
          <td ><a href="javascript:window.location='tng_logout.php';" 
				class="navText">Logout</a>
		  </td>
        </tr>
		<?php
			if(!isset($_SESSION['obj_login']))
				return;
			
			if($_SESSION['obj_login']->is_admin() == "false"){
				echo "</table>";
				return;
			}
		?>
		<tr>
          <td >
			<a href="javascript:window.location='tng_add_edit_user.php';" 
				class="navText">User Management</a>
		  </td>
        </tr>
			<tr>
	          <td >
				<a href="javascript:window.location='tng_create_group.php';" 
					class="navText">Add Group</a>
			  </td>
	        </tr>
		<tr>	
          <td >
			<a href="javascript:window.location='tng_manage_permissions.php';" 
				class="navText">Manage Permissions</a>
		  </td>
        </tr>
		<tr>
          <td >
			<a href="javascript:window.location='tng_create_form.php';" 
				class="navText">Create Form </a>
		  </td>
        </tr>
		<tr>
          <td >
			<a href="javascript:window.location='tng_create_schema.php';" 
				class="navText">Create Spatial Schema </a>
		  </td>
        </tr>
		<tr>
          <td >
			<a href="javascript:window.location='tng_link_schema_form.php';" 
				class="navText">Associate Schema with Form </a>
		  </td>
        </tr>
      </table>
</body>
</html>
