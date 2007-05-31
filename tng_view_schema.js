var target_url = "tng_view_schema_code.php";

///
/// ajax_get_schemas()
/// send a query to get all
/// schemas
///
function ajax_get_schemas(){	 
	var post_params = "ajax_action=get_schemas";
	create_http_request();
	send_http_request(handler_get_schemas, "POST", target_url, post_params);
}

///
/// ajax_get_schema_details()
/// send a query to get the details of
/// the selected schema
///
function ajax_get_schema_details(){
	var schema_list = document.getElementById('schema_list');
	var schema_id = schema_list.options[schema_list.selectedIndex].id;
	if(schema_id != -1){
		var post_params = "ajax_action=get_schema_details&schema_id=" + schema_id;
		create_http_request();
		send_http_request(handler_get_schema_details, "POST", target_url, post_params);
	}
}

///
/// handler_get_schemas()
/// populate dropdown list with 
/// schemas
///
function handler_get_schemas(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'schema_list');
	}
}

///
/// handler_get_schemas()
/// populate dropdown list with 
/// schemas
///
function handler_get_schema_details(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_schema_details(xmlHttp.responseText, 'geom_type', 'attr_list');
	}
}


///
/// populate_list_from_xml()
/// take the xml arg and populate a ul
/// list with list items.
///	<schema>
///		<id>	1	</id>
///		<name> abc </name>
///	</schema>
///
function populate_list_from_xml(xml, list_id){
	var list = document.getElementById(list_id);
	create_xml_doc(xml);
	// create blank element
	list.options[0] = new Option("Select Schema", -1);
	list.options[0].id = -1; // need to set the id explicitly
	var objects = xmlDoc.getElementsByTagName("schema");
	for(var i=1; i < objects.length + 1; i++){
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
/// populate_schema_details()
/// populate various fields on the form
/// with the details of a schema.
///	<schema>
///		<geom_type> polygon </geom_type>
///		<attribute>
///			<name> area </name>
///			<type> double precision </type>
///		</attribute>
///		...
///	</schema>
///	
function populate_schema_details(xml, geom_label, attr_list){
	create_xml_doc(xml);
	var geom_type = xmlDoc.getElementsByTagName("geom_type");

	document.getElementById(geom_label).value = geom_type[0].childNodes[0].nodeValue;
	var list = document.getElementById(attr_list);
	// clear list
	while(list.childNodes.length != 0)
		list.removeChild(list.firstChild);

	var attributes = xmlDoc.getElementsByTagName("attribute");
	for(var i=0; i < attributes.length; i++){
		var li = document.createElement("li");
		var txt_elt = document.createTextNode(attributes[i].childNodes[0].childNodes[0].nodeValue 
											+ ", " 
											+ attributes[i].childNodes[1].childNodes[0].nodeValue);
		li.appendChild(txt_elt);
		list.appendChild(li);
		// note that objects[i].childNodes[0]
		// gives us the <id> node. the way the DOM
		// works, the <id> node then has a text node
		// child, which is why we need to go one more
		// level down before getting the node value.
		//var id = objects[i].childNodes[0].childNodes[0].nodeValue;
		//var value = objects[i].childNodes[1].childNodes[0].nodeValue;
		//list.options[i] = new Option(value, id);
		//list.options[i].id = id; // need to set the id explicitly 
	}
}
