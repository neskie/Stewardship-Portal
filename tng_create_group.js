// global variables to this script block
var target_url = "tng_create_group_code.php";

///
/// ajax_check_gname()
/// check the group name entered to make
/// sure that there is no other group with
/// the same name
///
function ajax_check_gname(){
	var gname = document.getElementById('gname').value;
	if(gname == ""){
		alert("Please enter a valid name for the Group");
		return;
	}
	
	var post_params = "ajax_action=check_gname&gname=" + gname;
	create_http_request();
	send_http_request(handler_check_gname, "POST", target_url, post_params);
}

///
/// ajax_create_group()
/// send request to create a group
///
function ajax_create_group(gname){
	var post_params = "ajax_action=create_group&gname=" + gname;
	create_http_request();
	send_http_request(handler_create_group, "POST", target_url, post_params);
}

///
/// handler_check_gname()
/// check the response to see if the group
/// name that was entered already exists.
///
function handler_check_gname(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		var gname = document.getElementById('gname').value;
		if(xmlHttp.responseText != "false"){
			alert("There is already a group named: " + gname + ".\n"
				+ "Please select a different name");
			return;
		}
		// check passed. call method to send request to
		// create the group
		ajax_create_group(gname);
	}
}

///
/// handler_create_group()
/// check to see if the goup was successfully 
/// created
///
function handler_create_group(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		if(xmlHttp.responseText == "true")
			alert("The Group was successfully created");
		else
			alert("An error occurred while trying to create the group.\n" + xmlHttp.responseText);
	}
}
