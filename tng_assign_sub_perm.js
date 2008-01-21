/*---------------------------------------------------------------
author:	alim karim
date:	december 21, 2007
file:	tng_assign_sub_perm.php

desc:	script behind page to assign permissions
	to newly created submission
---------------------------------------------------------------*/
/// global variables to this script
var url = "tng_assign_sub_perm_code.php";
// the submission that we are dealing with.
// note that this will get its correct value
// from the php GET array
var sub_id = -1;
// the logged in user's id. this will be set
// from the php session variable
var uid = -1;
// determine if this is a new submission.
// used to set default permissions.
var is_new_sub = false;
///
/// populate_user_group_list()
/// populate list element with 
/// groups and users (as children)
///
function populate_user_group_list(list_elt){
	new Ajax.Request(url, 
					{
  						method:'get',
  						parameters: {ajax_req: 'get_groups_and_users', submission_id: sub_id},
  						requestHeaders: {Accept: 'application/json'}, 
						onSuccess: function(transport){
    								var response = transport.responseText.evalJSON(true);
    								var count = response.length;;
									var group_contains_logged_in_user = false;
    								for(var i = 0; i < count; i++){
										group_contains_logged_in_user = false;
          								var group = response[i];
										var group_elt = new Element('li', {class: "bodyText"});
										var group_chk = new Element('input', {	type: 'checkbox', 
																				id:  group.gid, 
																				name: "group",
																				onChange: "javascript: group_check(this)"
																			} );
										group_elt.insert(group_chk);
										group_elt.innerHTML = group_elt.innerHTML  + "&nbsp;" + group.gname;
										// add users of the group as children
										var u_list = new Element('ul');
										var u_count = group.users.length;
										for(var j = 0; j < u_count; j++){
											var u_elt = new Element('li');
											var chk = new Element('input', {	type: 'checkbox', 
																				id:  group.users[j].uid,
																				name: "user_chk",
																				checked: (group.users[j].selected) ? true:false
																			});
											if(group.users[j].uid == uid)
												group_contains_logged_in_user = true;//chk.writeAttribute({checked:true});
											u_elt.insert(chk);
											u_elt.innerHTML = u_elt.innerHTML + "&nbsp;" + group.users[j].uname;
											u_list.insert(u_elt);
										}
										
										group_elt.insert(u_list);
										list_elt.insert(group_elt);
										
										// if this is a new submission, and this group
										// contains the user making the submission, 
										// check all other users within that group 
										if(is_new_sub && group_contains_logged_in_user){
											// get all child checkbox elts
											 var g_users = group_elt.select("[type=checkbox]");
											  for (var j = 0; j < g_users.length; j++)
												g_users[j].writeAttribute({checked: true});
											// finally mark the group as checked	
											group_chk.writeAttribute({checked: true});
										}
										
										// disable tng group/users
										if(group.gid == 1){
											var g_users = group_elt.select("[type=checkbox]");
											// if this is a new submission, also check the
											// members of the tng group
											for(var j = 0; j < g_users.length; j++){
												g_users[j].writeAttribute({disabled: 1, checked: is_new_sub});
												//if(is_new_sub)
												//	g_users[j].writeAttribute({checked: true});
											}
											group_chk.writeAttribute({disabled: 1, checked: true});
										}
          							}
									
									
									
  								},
  						onFailure: function ( transport ) { alert ("an error occurred."); }			
					});
}

///
/// group_check()
/// check or uncheck all users 
/// under a group when the group
/// is checked or unchecked.
/// elt is the checkbox representing
/// the group
///
function group_check(elt){
	// <li> 
	//	| 
	//	--> <input type="checkbox">  <!-- group -->
	//	|
	//	--> <ul>
	//		|
	//		--> <li> 
	//			|
	//			--> <input type="checkbox"> <!-- user -->
	//
	// in this case, elt.nextSiblings will return an array
	// with one element. calling childElements on that
	// will return as many <li> user elements as there
	// are (i.e. the number of users in that group).
	// the first child of the user <li> is the checkbox
	// that we need to get to
	var children = elt.nextSiblings()[0].childElements();
	var check = (elt.checked) ? true:false;
	for(var i = 0; i < children.length; i++)
		children[i].firstChild.checked = check;
}

///
/// submit_users()
/// collect all users selected to be allowed
/// to view this submission. send an ajax
/// request to enter these into the appropriate
/// db table
///
function submit_users(form_elt){
	// get all checkboxes with name
	// 'user_chk'
	var user_checks = form_elt.getInputs('checkbox', 'user_chk');
	var allowed_users = new Array();
	for(var i = 0; i < user_checks.length; i++)
		// add selected users if they
		// dont exist in allowed_users
		if(user_checks[i].checked && allowed_users.indexOf(user_checks[i].id) == -1)
			allowed_users.push(user_checks[i].id);
			
	// issue ajax request		
	new Ajax.Request(url, 
					{
  					method:'post',
  					contentType: "application/x-www-form-urlencoded",
  					parameters: {ajax_req: 'submit_allowed_users',
  								submission_id: sub_id,
								user_list: allowed_users.toJSON()
	  							},
  					requestHeaders: {Accept: 'text/html'}, 
					onSuccess: function(transport){
    							if(/error/.test(transport.responseText))
									alert(transport.responseText);
								else
									window.location = "tng_form_saved.php";
 						},
  					onFailure: function (transport) { alert ("an error occurred."); }			
					});
}
