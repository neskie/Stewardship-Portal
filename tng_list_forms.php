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
		Please select the form that you would like to fill in from the list below.
		<br/>
		<br/>
		<br/>
		<select id="form_id" name="form_id">
			<?php
				// fill drop down with form names
				for($i=0; $i < $form_list_size; $i++){
					printf("<option value=\"%d\"> %s </option>\n", $form_list[$i][0], $form_list[$i][1]);
				}
			?>
		</select>
		<input type="button" class="button" value="Display Form" onClick="javascript:submit_form('fill_form');"/>
		<br>
		<hr>
		<input type="button" class="button" value="View Submissions" onClick="window.location = 'tng_display_submissions.php'"/>
		<hr>
		<input type="button" class="button" value="View Available Layers" onClick="window.location = 'tng_select_layers.php'"/>
		<input type="hidden" id="form_action" name="form_action"/>
	</form>
</body>
</html>
