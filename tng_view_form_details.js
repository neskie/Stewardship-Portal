var target_url = "tng_view_form_details_code.php";
// determines whether the user that is logged
// in can toggle the searchable flag on a field
var user_is_admin = false;

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
	list.options[0] = new Option("Select Form", -1);
	list.options[0].id = -1; // need to set the id explicitly
	var objects = xmlDoc.getElementsByTagName("form");
	for(var i=1; i < objects.length + 1; i++){
		// note that objects[i].childNodes[0]
		// gives us the <id> node. the way the DOM
		// works, the <id> node then has a text node
		// child, which is why we need to go one more
		// level down before getting the node value.
		var id = objects[i-1].childNodes[0].childNodes[0].nodeValue;
		var value = objects[i-1].childNodes[1].childNodes[0].nodeValue;
		list.options[i] = new Option(value, id);
		list.options[i].id = id; // need to set the id explicitly 
		
	}
}

///
/// clear_table()
/// delete all rows except the header
/// row from the given table.
///
function clear_table(table_id){
	var table = document.getElementById(table_id);
	var n_rows = table.rows.length;
	while(table.rows.length > 1)
		table.deleteRow(table.rows.length - 1);
}


///
/// ajax_get_forms()
/// send a request to get all
/// forms
///
function ajax_get_forms(){	 
	var post_params = "ajax_action=get_forms";
	create_http_request();
	send_http_request(handler_get_forms, "POST", target_url, post_params);
}

///
/// ajax_get_form_details()
/// send a request to get the details of
/// the selected form
///
function ajax_get_form_details(){
	var form_list = document.getElementById('form_list');
	var form_id = form_list.options[form_list.selectedIndex].id;
	if(form_id != -1){
		var post_params = "ajax_action=get_form_details&form_id=" + form_id;
		create_http_request();
		send_http_request(handler_get_form_details, "POST", target_url, post_params);
	}
}

///
/// ajax_toggle_searchable()
/// toggle searchable flag for a field
///
function ajax_toggle_searchable(){
	// note: in this context,
	// 'this' refers to the calling
	// object i.e. the checkbox
	var field_id = this.name;
	var searchable = document.getElementById(this.id).checked;
	var post_params = "ajax_action=toggle_searchable& " 
						+ "field_id=" + field_id + "&"
						+ "searchable=" + searchable;
	create_http_request();
	send_http_request(null, "POST", target_url, post_params);
						
}

///
/// handler_get_schemas()
/// populate dropdown list with 
/// schemas
///
function handler_get_forms(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'form_list');

	}
}

///
/// handler_get_form_details()
/// call method to populate list with 
/// the fields belonging to the selected form
///
function handler_get_form_details(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_form_details(xmlHttp.responseText, 'field_table');
	}
}


///
/// populate_form_details()
/// populate various fields on the form
/// with the details of a schema.
///	<form>
///		<form_id> 1 </form_id>
///		<form_name> sample </form_name>
///		
///		<field>
///			<field_id> 				2 				</field_id>
///			<field_name>			address 		</field_name>
///			<field_type>			text 			</field_type>
///			<field_label>			Address:		</field_label>
///			<field_rank>			1				</field_rank>
///			<field_label_css_class>	label css class </field_label_css_class>
///			<field_css_class>		field css class </field_css_class>
///			<field_searchable>		true			 </field_searchable>
///			<field_value>			4878 1st ave 	</field_value>
///		</field>
///		...
///	</form>
///	
function populate_form_details(xml, table_id){
	create_xml_doc(xml);
	var table = document.getElementById(table_id);
	// clear list
	//while(list.childNodes.length != 0)
	//		list.removeChild(list.firstChild);
	clear_table(table_id);
	var fields = xmlDoc.getElementsByTagName("field");
	var t_body = document.getElementById('detail_body');
	
	for(var i=0; i < fields.length; i++){
		//var row = table.insertRow(i + 1);
		var row = document.createElement("tr");
		//alert(fields[i].childNodes.length);
		var field_id = fields[i].childNodes[0].childNodes[0].nodeValue;
		//var cell = row.insertCell(0);
		var cell1 = document.createElement("td");
		cell1.setAttribute('class', 'td_search');
		cell1.style.cssText = 'td_search';
		var txt_elt = document.createTextNode(fields[i].childNodes[1].childNodes[0].nodeValue);
		cell1.appendChild(txt_elt);
		row.appendChild(cell1);
		
		//cell = row.insertCell(1);
		var cell2 = document.createElement("td");
		cell2.setAttribute('class', 'td_search');
		cell2.style.cssText = 'td_search';
		txt_elt = document.createTextNode(fields[i].childNodes[2].childNodes[0].nodeValue);
		cell2.appendChild(txt_elt);
		row.appendChild(cell2);
		
		//cell = row.insertCell(2);
		var cell3 = document.createElement("td");
		cell3.setAttribute('class', 'td_search_center');
		cell3.style.cssText = 'td_search_center';
		var chk_box = document.createElement("input");
		chk_box.setAttribute("type", "checkbox");
		chk_box.setAttribute("id", 'chk_' + field_id);
		chk_box.setAttribute("name", field_id);
		//chk_box.setAttribute('onclick', 'ajax_toggle_searchable(' + field_id + ')');
		//chk_box.onclick = function(){ajax_toggle_searchable();};
		chk_box.onclick = ajax_toggle_searchable;
		// I.E quirk - the checkbox must be appended
		// to the parent before the "checked" attribute
		// is set
		chk_box = cell3.appendChild(chk_box);
	
		var searchable = fields[i].childNodes[7].childNodes[0].nodeValue;
		if(searchable == "t")
			chk_box.setAttribute("checked", "1");
			
		// only enable the checkbox if an admin
		// user is loggen in
		if(!user_is_admin)
			chk_box.setAttribute("disabled", "1");
		
		row.appendChild(cell3);
		t_body.appendChild(row);
	}
}

