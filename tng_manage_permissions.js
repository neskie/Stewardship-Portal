// global variables to this script block
var xmlHttp;
var xmlDoc;
var target_url = "tng_manage_permissions_code.php";

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
function ajax_search_uname(uname){
	create_http_request();
	// create the parameters to be
	// sent to the php target.
	// this is a string of name=value pairs. if
	// multiple parameters are to be sent, then they
	// are separated by an ampersand.
	var post_params = "ajax_action=search_uname&ajax_uname=" + uname;
	// call method to sent the request
	send_http_request(xmlHttp_handle_uname_search, "POST", target_url, post_params);
}

function ajax_populate_object(){
	// the first option in the list is blank
	if(obj_index == 0)
		return;

	var manageable_objects = document.getElementById('manageable_objects');
	var obj_index =  manageable_objects.selectedIndex;
	var obj_type = manageable_objects.options[obj_index].value;
	var uid_list = document.getElementById('user_list');
	if (uid_list.selectedIndex == -1){
		alert("Please select a user");
		manageable_objects.selectedIndex = 0;
	}else{
		create_http_request();
		var uid  = uid_list.options[uid_list.selectedIndex].value;
		var post_params_allowed = "ajax_action=get_objects&ajax_object_set=allowed&uid=" + uid + "&ajax_obj_type=" + obj_type;
		// call method to send the request. 
		// note - we want both sets - but both requests 
		// cannot be processed simultaneously.  the second request 
		// is sent from the handler.
		send_http_request(xmlHttp_handle_object_search_allowed, "POST", target_url, post_params_allowed);
	}
}

///
/// ajax_toggle_permission()
/// grant or revoke permissions on an object.
/// two lists are created, each pointing
/// to the allowed/disallowed lists depending
/// on the action parameter.
///
function ajax_toggle_permission(action){
	var uid_list = document.getElementById('user_list');
	var object_list = document.getElementById('manageable_objects');
	var list1;
	var list2;
	var ajax_action;
	if(action == "grant"){ /* going from disallowed to allowed (grant) */
		list1 = document.getElementById('obj_list_disallowed');
		list2 = document.getElementById('obj_list_allowed');
		ajax_action = "grant_perm";
	}else{ /* going from allowed to disallowed (rekove)*/
		list1 = document.getElementById('obj_list_allowed');
		list2 = document.getElementById('obj_list_disallowed');
		ajax_action = "revoke_perm";
	}
	
	// make sure something is selected in
	// the source list before proceeding
	if(list1.selectedIndex == -1)
		return;
		
	var uid = uid_list.options[uid_list.selectedIndex].value;
	var obj_type = object_list.options[object_list.selectedIndex].value;
	var obj_id = list1.options[list1.selectedIndex].id;
	// remove the object from list1
	// and place it in list2
	list2.options[list2.options.length] = new Option(list1.options[list1.selectedIndex].text,
																	obj_id);
	list2.options[list2.options.length - 1].id = obj_id;
	list1.options[list1.selectedIndex] = null;
	// call php script through ajax so that
	// it can make these changes in the db.
	var post_params = "ajax_action=" + ajax_action + "&ajax_obj_type=" + obj_type + "&ajax_obj_id=" + obj_id + "&uid=" + uid;
	alert(post_params);
	// note - no need for a handler
	// because we're only toggling
	create_http_request();
	send_http_request(null, "POST", target_url, post_params);
}

///
/// populate_list_from_xml()
/// take the xml arg and populate a select
/// list with options.
///
function populate_list_from_xml(xml, list_id){
	var list = document.getElementById(list_id);
	list.options.length = 0;
	create_xml_doc(xml);
	var objects = xmlDoc.getElementsByTagName("object");
	for(var i=0; i < objects.length; i++){
		// note that objects[i].childNodes[0]
		// gives us the <id> node. the way the DOM
		// works, the <id> node then has a text node
		// child, which is why we need to go one more
		// level down before getting the node value.
		var id = objects[i].childNodes[0].childNodes[0].nodeValue;
		var value = objects[i].childNodes[1].childNodes[0].nodeValue;
		list.options[i] = new Option(value, id);
		list.options[i].id = id; // need to set the id explicitly 
	}
}

///
/// xmlHttp_handle_uname_search()
/// this is the handler that is called when the
/// user has supplied a partial user name. it
/// recieves the xml that represents the list
/// of users
///
function xmlHttp_handle_uname_search(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'user_list');
	}
}

///
/// xmlHttp_handle_object_search()
/// this is the handler that is called when the
/// user selected an object to manage permissions
// for. it
/// recieves the xml that represents the list
/// of allowed forms/layers/etc
///
function xmlHttp_handle_object_search_allowed(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		// populate 'allowed' select list
		populate_list_from_xml(xmlHttp.responseText, 'obj_list_allowed');
		// now that this request is complete,
		// send another request to get disallowed
		// layers		
		create_http_request();
		var manageable_objects = document.getElementById('manageable_objects')
		var obj_index =  manageable_objects.selectedIndex;
		var obj_type = manageable_objects.options[obj_index].value;
		var uid_list = document.getElementById('user_list');
		var uid  = uid_list.options[uid_list.selectedIndex].value;
		var post_params_disallowed = "ajax_action=get_objects&ajax_object_set=disallowed&uid=" + uid + "&ajax_obj_type=" + obj_type;
		send_http_request(xmlHttp_handle_object_search_disallowed, "POST", target_url, post_params_disallowed);
	}
}

///
/// xmlHttp_handle_object_search_disallowed()
/// handler called when the backend
function xmlHttp_handle_object_search_disallowed(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'obj_list_disallowed');
	}
}
