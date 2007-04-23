<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_display_form.php

desc:	webpage to display all submissions
---------------------------------------------------------------*/

include('tng_display_submissions_code.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Display Submissions</title>
<script language="javascript">
///
/// expand_collapse
/// expand or collapse a div
/// based on its ID
///
function expand_collapse(id){
	if(document.getElementById(id).style.display == "none"){
		document.getElementById(id).style.display = 'block';
	}else{
		document.getElementById(id).style.display = 'none';			
	}
}

///
/// sunmit_form
/// called when a file or layer is clicked on.
/// the function then sets the value of the
/// hidden variables corresponding to what was
/// clicked and submits the form.
///
function submit_form(elt_type, elt_id){
	// elt_type can be "form" or "layer"
	// see tng_submission_transform.xslt
	// to change values.	
	document.getElementById('elt_selected').value = elt_type;
	document.getElementById('elt_id').value = elt_id;
	document.forms[0].submit();
}

</script>

</head>
<body>
	<div id="leftcol">wefwef </div>
	<div id="content">
		<form id="tng_display_submissions" name="tng_display_submissions" method="post" action="tng_display_submissions.php">
			<?php
				echo $generated_submission_html
			?>
			<input name="elt_selected" id="elt_selected" type="hidden" value=""/>
			<input name="elt_id" id="elt_id" type="hidden"/>
			<!-- 
				dummy variables needed in case user wishes
				to redirect to display_form.php
			-->
			<input name="form_id" id="form_id" type="hidden"/>
			<input name="submission_id" id="submission_id" type="hidden"/>
			<input name="readonly" id="readonly" type="hidden"/>
		</form>
	</div>
	<div id="rightcol"> wefwef  </div> 
</body>
</html>
