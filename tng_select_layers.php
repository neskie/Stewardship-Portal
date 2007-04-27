<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 18, 2007
file:	tng_select_layers.php

desc:	webpage to give the user a list of available
		layers that can be viewed in the mapviewer
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

//include_once('tng_select_layers_code.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Available Layers</title>
<script language="javascript">
<!--
// global variables to this script block
var xmlHttp;
var xmlDoc;
var target_url = "tng_select_layers_code.php";

///
/// create_http_request()
/// initialize the global xmlHttp variable
/// to the correct http request object based on
/// the browser type
///
function create_http_request(){
	try{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}catch (e){
    	// Internet Explorer
    	try{
      		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    	}catch (e){
      		try{
        		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      		}catch (e){
        		alert("Your browser does not support AJAX!");
        		return false;
       		}
     	}
   	}
}

///
/// create_xml_doc()
/// create xml document object from
/// xml string that is passed in
///
function create_xml_doc(xml_txt){
	if (window.ActiveXObject){
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async = false;
		xmlDoc.loadXML(xml_txt)
	}
	else{
		try{
			var parser = new DOMParser();
			xmlDoc = parser.parseFromString(xml_txt, "text/xml");
		}catch (e){
			alert('Your browser can\'t handle this script');
			return;
		}
	}
}
///
/// send_http_request()
/// send a request to the target with
/// the given parameters. note that params
/// should be url encoded i.e. multiple params
/// should be separated by &.
/// handler is the name of the function to be executed
/// when the readystate of the xml http
/// request changes state.
/// method can be GET or POST.
///
function send_http_request(handler, method, target, params){
	// set the content type 
	// for POST to work as a method
	// when opening the xmlHttp request
	// to the server
	var content_type = "application/x-www-form-urlencoded; charset=UTF-8";
	xmlHttp.onreadystatechange = handler;
	xmlHttp.open(method, target,true);
	xmlHttp.setRequestHeader("Content-Type", content_type);
	xmlHttp.send(params);
}

///
/// ajax_post_search()
/// called when the user enters a partial
/// layer name. the corresponding string is sent
/// to the php script, which limits the layers
/// that are displayed to those whose names begin
/// with the string provided by the user.
/// note that the script generates xml and does
/// the xml->html translation, so on the client, 
/// we do not have to worry about xslt parsing
/// and such.
///
function ajax_post_search(layer_name){
	create_http_request();
	// create the parameters to be
	// sent to the php target.
	// this is a string of name=value pairs. if
	// multiple parameters are to be sent, then they
	// are separated by an ampersand.
	var post_params = "ajax_layer_name=" + layer_name;
	// call method to sent the request
	send_http_request(xmlHttp_response_handler, "POST", target_url, post_params);
}

///
/// ajax_post_selected()
/// called when the user wishes to toggle
/// whether a layer will be displayed on
/// the map viewer or not.
/// note that we do not expect any data to
/// be returned by the php script, since we
/// only want to send data to the script.
///
function ajax_post_selected(layer_id){
	alert(layer_id);
	create_http_request();
	// create the parameters to be
	// sent to the php target.
	var post_params = "ajax_layer_id=" + layer_id + "&";
	if(document.getElementById(layer_id).checked == true)
		post_params += "display=true";
	else
		post_params += "display=false";
	
	// call method to sent the request
	// note, no handler method is specified
	// because we only wish to toggle the
	// display flag of a particular layer
	send_http_request(null, "POST", target_url, post_params);
}

///
/// ajax_submit_form()
/// called when the user clicks the button to 
/// launch the map viewing agent.
/// in this case we need to set a post parameter
/// called 'ajax_launch_fist' so that the 
/// script in the back end knows what to do.
/// the value of this parameter is irrelevant.
/// upon successful generation of all necessary
/// configuration files, the script will return a
/// window.open statement with the location of the
/// mapping agent.
///
function ajax_submit_form(){
	create_http_request();
	var post_params = "ajax_launch_fist=xx";
	send_http_request(launch_mapper, "POST", target_url, post_params);
}

///
/// xmlHttp_response_handler()
/// this is the handler that is called when the
/// user has supplied a partial layer name. it
/// recieves the html that represents the list
/// of layers, and attaches this list to the
/// appropriate div in the page.
///
function xmlHttp_response_handler(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		//var layer_list_div = document.getElementById('layer_list');
		//layer_list_div.innerHTML = "";
		//layer_list_div.innerHTML = xmlHttp.responseText;
		populate_list_from_xml(xmlHttp.responseText, 'layer_list');
	}
}

function populate_list_from_xml(xml, list_id){
	var list = document.getElementById(list_id);
	// clear the list
	while(list.childNodes.length != 0)
		list.removeChild(list.firstChild);
		
	create_xml_doc(xml);
	// get all <layer> elements
	var layers = xmlDoc.getElementsByTagName("layer");
	// loop through and create <dd>
	// elements for each layer
	for(var i = 0; i < layers.length; i++){
		var list_item = document.createElement("dd");
		
		var chk_box = document.createElement("input");
		chk_box.setAttribute("type", "checkbox");
		chk_box.setAttribute("id", layers[i].childNodes[0].childNodes[0].nodeValue);
		chk_box.setAttribute("onChange", "javascript: ajax_post_selected(this.id)");
		// see if the display attribute is turned on
		if(layers[i].childNodes[2].childNodes[0].nodeValue == "true")
			chk_box.setAttribute("checked", "");
		
		// finally attach the checkbox to the <li>
		list_item.appendChild(chk_box);
		
		// get the layer name and display
		// it next to the checkbox	
		var layer_name = document.createTextNode(layers[i].childNodes[1].childNodes[0].nodeValue);
		list_item.appendChild(layer_name);
		
		// and attach the <li> to the <list>
		list.appendChild(list_item); 		
	}
}
///
/// launch_mapper()
/// use the eval function to execute a
/// window.open statement returned by the
/// php script from the ajax call in
/// submit_form
///
function launch_mapper(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		eval(xmlHttp.responseText);
	}
}

// the first time around, we should display
// all available layers. this is done by sending
// a blank string to the ajax_post_search function.
ajax_post_search("");
-->
</script>

</head>
<body>
	<!-- <div id="container"> -->
		<div id="content">
			<form id="tng_select_layers" 
					name="tng_select_layers" 
					method="post" 
					enctype="multipart/form-data" 
					action="tng_select_layers_code.php">
				<h2> Available Layers </h2>
				<p> The list below shows the layers that you have permission to view. Please 
					select the layers that you wish to appear in the mapping agent.
				</p>
				<p> To search the available layers by name, enter the 
					name of the layer in the field below.
				</p>
				<p> Name: 
				<input type="text"
						name="layer_name" 
						id="layer_name"
						size="50"
						onKeyUp="javascript: ajax_post_search(this.value);"/> 
				</p>
				<!-- <div id="layer_list"> -->
					<!-- this will be auto populated by javascript/ajax -->
				<!-- </div> -->
				<dl id="layer_list" name="layer_list">
					<!-- this will be auto populated by javascript/ajax -->
				</dl>
				<br/>
				<input type="button" class="button" value="Launch Map Viewer" onClick="javascript: ajax_submit_form()"/>
				<br/>
				<br/>
			</form>
		</div>
		<div id="leftcol"> whr is is that this didnt work before but it works just fine now ....</div>
		<div id="rightcol"> 
			Much effort has been made to ensure that 
			the layouts in the BlueRobot Layout Reservoir appear 
			as intended in CSS2 compliant browsers. The content 
			should be viewable, though unstyled, in other web browsers. 
			If you encounter a problem that is not listed as a known 
			issue, I am most likely not aware of it. Your help will 
			benefit the other five or six people who visit this site. 
		</div> 
	<!-- </div> -->
	</body>
</body>
</html>