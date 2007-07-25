// global variables to this script block
var target_url = "tng_link_schema_form_code.php";

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
/// ajax_populate_forms()
/// called on page load. send request for
/// list of forms
///
function ajax_populate_forms(){
	create_http_request();
	var post_params = "ajax_action=get_form_list";
	// call method to sent the request
	send_http_request(handler_ajax_populate_forms, "POST", target_url, post_params);
}

///
/// ajax_populate_object()
/// send request to get all schemas.
/// this function sends a request to get
/// the schemas that are linked to the
/// selected form. the handler for the
/// request sends a second req for the
/// unlinked schemas
///
function ajax_populate_schemas(){
	var form_list = document.getElementById('form_list');
	// clear linked and unlinked schema
	// lists when first option is chosen.
	// note - first option is a dummy option
	// 'Select  a Form'
	if (form_list.selectedIndex == 0){
		var blank_xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><objects></objects>";
		populate_list_from_xml(blank_xml, 'linked_schema_list');
		populate_list_from_xml(blank_xml, 'unlinked_schema_list');
	}else{
		create_http_request();
		var form_id  = form_list.options[form_list.selectedIndex].value;
		var post_params_allowed = "ajax_action=get_schemas&" 
									+ "ajax_object_set=linked&" 
									+ "form_id=" + form_id ;
									
		// call method to send the request. 
		// note - we want both sets - but both requests 
		// cannot be processed simultaneously.  the second request 
		// is sent from the handler.
		send_http_request(handler_ajax_populate_schemas, "POST", target_url, post_params_allowed);
	}
}

///
/// ajax_toggle_linkage()
/// toggle whether a schema is linked to
/// a form or not
///
function ajax_toggle_linkage(action){
	var form_list = document.getElementById('form_list');
	var list1;
	var list2;
	var ajax_action;
	if(action == "link"){ /* going from unlinked to linked */
		list1 = document.getElementById('unlinked_schema_list');
		list2 = document.getElementById('linked_schema_list');
		ajax_action = "link";
	}else{ /* going from linked to unlinked */
		list1 = document.getElementById('linked_schema_list');
		list2 = document.getElementById('unlinked_schema_list');
		ajax_action = "unlink";
	}
	
	// make sure something is selected in
	// the source list before proceeding
	if(list1.selectedIndex == -1)
		return;
		
	var form_id = form_list.options[form_list.selectedIndex].value;
	var schema_id = list1.options[list1.selectedIndex].id;
	// remove the object from list1
	// and place it in list2
	list2.options[list2.options.length] = new Option(list1.options[list1.selectedIndex].text,
																	schema_id);
	// length increases by one after the addition,
	// so use length - 1 to index into the new option
	list2.options[list2.options.length - 1].id = schema_id;
	list1.options[list1.selectedIndex] = null;
	// call php script through ajax so that
	// it can make these changes in the db.
	var post_params = "ajax_action=" + ajax_action + "&schema_id=" + schema_id + "&form_id=" + form_id;
	// note - no need for a handler
	// because we're only toggling
	create_http_request();
	send_http_request(tmp, "POST", target_url, post_params);
}

function tmp(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		alert(xmlHttp.responseText);
	}
}

//--------------------------------------------------------------------------
// ajax handlers
//--------------------------------------------------------------------------
/// 
/// handler_ajax_populate_forms()
/// populate form drop down list with data
/// from request
///
function handler_ajax_populate_forms(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'form_list');
	}
}

///
/// handler_ajax_populate_schemas()
/// called when the request sent by 
/// ajax_populate_schemas is complete.
/// this function sends a second request to
/// get unlinked schemas
///
function handler_ajax_populate_schemas(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		// populate 'linked' select list
		populate_list_from_xml(xmlHttp.responseText, 'linked_schema_list');
		// now that this request is complete,
		// send another request to get unlinked schemas
		create_http_request();
		var form_list = document.getElementById('form_list');
		var form_id  = form_list.options[form_list.selectedIndex].value;
		var post_params_disallowed = "ajax_action=get_schemas&" 
									+ "ajax_object_set=unlinked&" 
									+ "form_id=" + form_id;
		send_http_request(handler_ajax_populate_unlinked_schema, "POST", target_url, post_params_disallowed);
	}
}

///
/// handler_ajax_populate_unlinked_schema()
/// populate unlinked schemas
///
function handler_ajax_populate_unlinked_schema(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'unlinked_schema_list');
	}
}
