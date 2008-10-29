/*
 * notes:
 * 	2008.10.29
 * 	added toString method to arguments that check for a group
 * 	name (isDefaultOnGroup, isAdminGroup, etc) so that the group
 * 	name is converted properly to a string. this problem was being
 * 	caused by groups having names such as: 1_TNG, which aren't
 * 	parsed right off the bat as strings.
 *
 */
Ext.namespace('assign_perm');

Ext.BLANK_IMAGE_URL = 'ext-2.2/resources/images/default/s.gif'

assign_perm.app = function (){

	//private variables
	var submissionID = -1;
	var userID = -1;
	var isNewSubmission = true;
	var backendURL = "tng_assign_sub_perm_code.php";
	var treeDivID = "user_tree";
	var adminGroups = ["1_TNG"];
	var defaultOnGroups = ["1_TNG", "BCGovernment"] ;
	// list to hold user ids that are turned on in the tree
	var checkedUIDList = new Array();
	var treePanel;

	
	// ------------------ utility functions ------------------------------------
	///
	/// checkTreeNode
	/// check/uncheck a tree node by using
	/// its UI
	///
	var checkTreeNode = function(node, onOff){
		var treeUI = node.getUI();
		treeUI.toggleCheck(onOff);
	};

	///
	/// isTreeNodeChecked
	/// use the node's UI to check if the checkmark
	/// is on or off for this node
	///
	var isTreeNodeChecked = function(node){
		var treeUI = node.getUI();
		return treeUI.isChecked();
	};


	///
	/// disableTreeNode
	/// disable the node passed in
	///
	var disableTreeNode = function(node){
		node.disable();
	};
	///
	/// groupContainsCurrentUser
	/// function to check if the current user
	/// (this.userID) exists in group
	///
	var groupContainsCurrentUser = function(group){
		var groupUsers = group.childNodes;
		for(var i = 0; i < groupUsers.length; i++)
			if(groupUsers[i].attributes.uid == userID)
				return true;

		return false;
	};

		
	///
	/// isDefaultOnGroup
	/// check if the passed in groupName is
	/// in the defaultOnGroup array
	///
	var isDefaultOnGroup = function(groupName){
		if(defaultOnGroups.indexOf(groupName.toString()) < 0)
			return false;
		else
			return true;
	};

	///
	/// isAdminGroup
	/// check if the given group exists in
	/// the adminGroup list
	///
	var isAdminGroup = function(groupName){
		if(adminGroups.indexOf(groupName.toString()) < 0)
			return false;
		else
			return true;
	};

	// ------------------ end utility functions ------------------------------------
	
	///
	/// checkDefaultOnGroups
	/// turn on groups that are listed in
	/// the defaultOnGroups array
	///
	var checkDefaultOnGroups = function(rootNode){
		// get all child nodes (containing groups)
		// of the root node.
		var groupNodes = rootNode.childNodes;

		// for each group node
		for(var groupIndex = 0; groupIndex < groupNodes.length; groupIndex++){
			// if this node is a group
			// node and it exists in the 
			// defaultOnGroups array, change the
			// check mark to ON.
			if("gname" in groupNodes[groupIndex].attributes
				&& isDefaultOnGroup(groupNodes[groupIndex].attributes.gname)){
					checkTreeNode(groupNodes[groupIndex], true);
			}
		}

	};

	///
	/// checkUsersGroup
	/// iterate through the group list. turn on
	/// any groups that the user is a member of
	///
	var checkUsersGroup = function(groupArray){
		// iterate through each group
		for(var groupIndex = 0; groupIndex < groupArray.length; groupIndex++)
			// check if the group is not in the defaultOn list
			// and that the group contains the user making the
			// submission
			if(!isDefaultOnGroup(groupArray[groupIndex].attributes.gname) 
				&& groupContainsCurrentUser(groupArray[groupIndex])){
				// turn on the whole group
				checkTreeNode(groupArray[groupIndex], true);
				// expand the group
				groupArray[groupIndex].expand();
			}

	};
	
	///
	/// disableAdminGroups
	/// go through the admin group list and compare
	/// each group in the groupList. if a match is
	/// found, disable the group so users cannot 
	/// check/uncheck it.
	///
	var disableAdminGroups = function(groupList){
		// iterate through group
		for(var groupIndex = 0; groupIndex < groupList.length; groupIndex++)
			// if the given group is an admin
			// group, disable it
			if(isAdminGroup(groupList[groupIndex].attributes.gname)){
				disableTreeNode(groupList[groupIndex]);
				// call the disableTreeNode function for each
				// child (user) as well.
				groupList[groupIndex].eachChild(disableTreeNode);
			}
	};
	
	///
	/// turnOnGroupsWithAllUsersChecked
	/// for old submissions, go through the list
	/// of groups and check if every group member has
	/// permissions to view the submissions. if so, turn
	/// on the group's check mark.
	/// this is purely for costmetic reasons, it does not
	/// hold any semantic value.
	///
	var turnOnGroupsWithAllUsersChecked = function(groupList){
		// iterate through group list
		for(groupIndex = 0; groupIndex < groupList.length; groupIndex++){
			// for each group ...
			// get list of members (users)
			var userList = groupList[groupIndex].childNodes;
			var allChecked = true;
			// if a group has no users, then we do not wish
			// its check mark to be on.
			if (userList.length == 0)
				allChecked = false;
			// iterate through group's members
			for(var userIndex = 0; userIndex < userList.length; userIndex++){
				// check if each group member is checked
				if(!isTreeNodeChecked(userList[userIndex])){
					allChecked = false;
					break;
				}
			}
			// if so, turn on the group's check mark
			if (allChecked)
				checkTreeNode(groupList[groupIndex], true);
		}
	};

	///
	/// traverseTreeCollectCheckedUsers
	/// walk through the tree and add each checked 
	/// user (leaf node) to the checkedUIDList.
	/// if the node is not a leaf, call this function
	/// recursively on the child nodes.
	///
	var traverseTreeCollectCheckedUsers = function(node){
		// if node is leaf, and it is checked
		if(node.isLeaf() && isTreeNodeChecked(node)){
			// check if uid exists in list already. this may
			// happen if a user is part of multiple groups, 
			// they may appear in the tree multiple times
			if(checkedUIDList.indexOf(node.attributes.uid) < 0) 
				//add to checkedUIDList
				checkedUIDList.push(node.attributes.uid);
			// return (recursion base case)
			return;
		}else{
			// otherwise, iterate through node's children
			var children = node.childNodes;
			// call traverseTreeCollectChecked on each child
			for(var nodeIndex = 0; nodeIndex < children.length; nodeIndex++)
				traverseTreeCollectCheckedUsers(children[nodeIndex]);
		}
	};

	// ------------------ event handlers ------------------------------------
	///
	/// checkChangeEventHandler
	/// called when a tree node is checked/unchecked.
	///
	var checkChangeEventHandler = function(node, checked){
		
		// if the node being checked/unchecked is a user (leaf),
		// then nothing to be done.
		if(node.isLeaf())
			return true;
		// else if the node being checked/unchecked is a group (not leaf)
		// then check all child nodes (users) under it
		var userNodes = node.childNodes;
		for(var i = 0; i < userNodes.length; i++){
			checkTreeNode(userNodes[i], checked);
		}
	};
	
	///
	/// appendEventHandler
	/// called when a new node is appended to
	/// treePanel
	///
	var appendEventHandler = function(tree, parent, node){
		// use the nodes gname/uname attribute as the
		// display text
		
		// if the node is a group, it will have a gname
		if("gname" in node.attributes)
			node.text = node.attributes.gname;
		// otherwise the node will have a uname
		else
			node.text = node.attributes.uname;
	};
	
	///
	/// treePostLoadHandler
	/// fired after the tree has completed loading.
	/// this is where most of the processing for post tree load
	/// happens. specifically:
	///	- expand and collapse the tree to make sure all nodes are loaded
	/// - for new submissions:
	///		- turn the groups on that are supposed to be on by default
	///		- check the rest of the group that the user is in
	/// - disable admin groups from being able to be modified.
	///
	var treePostLoadHandler = function(loader, rootNode, response){
		//expand the whole tree so that all nodes
		// are loaded
		rootNode.expandChildNodes(true);
		rootNode.collapseChildNodes(true);
		// for new submissions
		if(isNewSubmission){
			// turn groups that should be on by default
			checkDefaultOnGroups(rootNode);
			// turn on the group that the user belongs to
			// rootNode.childNodes is the array of groups
			checkUsersGroup(rootNode.childNodes);
		}else{
			// for old submissions, there probably will be some
			// permissions set up. go through the tree and if a
			// group has all its members checked, turn the group
			// check box on.
			turnOnGroupsWithAllUsersChecked(rootNode.childNodes);
		}
		// disable admin groups from being modified
		disableAdminGroups(rootNode.childNodes);
	};

	// ------------------ end event handlers ------------------------------------
	
	// public space
	return{
		
		///
		/// buildLoader()
		/// create a new ext treeLoader config object.
		/// note that this method only builds the config
		/// object, not a real ext treeLoader object.
		/// note that most of the processing that takes place
		/// after the tree is loaded, happens in 
		/// the postLoadHander
		///
		buildLoaderConfig: function(fnTreePostLoadHandler){
			// create a treeLoader config object with all
			// properties that we would like the tree loader
			// to have
			var loaderConfig = {
								requestMethod: 'GET',
								preloadChildren: true,
								dataUrl: backendURL,
								baseAttrs:{
									checked: false
								},
								baseParams: {
									ajax_req: 'get_groups_and_users', 
									submission_id: submissionID
								},
								listeners: {
									'load': {
										fn: fnTreePostLoadHandler,
										scope: this
									}
								}
							};
			// return the config object
			return loaderConfig;
		},

		///
		/// buildTree
		/// create a new ext tree control and render it
		/// to the treeDivID
		///
		buildTree: function(loaderConfig, fnAppendEventHandler, fnCheckChangeEventHandler){
		
			// create a tree config obj to hold all the 
			// properties that we would like the tree to have
			var treeConfig = {
								renderTo: treeDivID,
								useArrows: true,
								animate: true,
								endableDD: false,
								rootVisible: false,
								// dummy root
								root: new Ext.tree.AsyncTreeNode({
										text: 'root'
									}),
								listeners: {
									'append': {
										fn: fnAppendEventHandler,
										scope: this
									},
									'checkchange' : {
										fn: fnCheckChangeEventHandler,
										scope: this
									}
								},
								loader: new Ext.tree.TreeLoader(loaderConfig)
							};

			// pass the config object to the treePanel constructor
			treePanel = new Ext.tree.TreePanel(treeConfig);
		},
	
		///
		/// submitUserList
		/// collect and submit all the user ids that 
		/// have been checked to the backend url.
		///
		submitUserList: function(){
			// collect the user ids as an array and store it in
			// this.checkedUIDList
			traverseTreeCollectCheckedUsers(treePanel.root);
			// make an ajax request and submit the array
			// as json data
			Ext.Ajax.request({
				url: backendURL,
				method: 'post',
				params: { 
					ajax_req: 'submit_allowed_users',
					submission_id: submissionID,
					user_list: Ext.util.JSON.encode(checkedUIDList)
				},
				success: function(transport){
					if(/error/.test(transport.responseText))
						alert(transport.responseText);
					else
						window.location = "tng_form_saved.php";
				},
				failure: function(transport){
					alert("An error occurred");
				}
			});
		},

		init: function(){
			// note that sub_id, uid and is_new_submission
			// are global variables set via php in
			// tng_assign_sub_perm.php
			submissionID = sub_id;
			userID = uid;
			isNewSubmission = is_new_sub;
			// call function to build the loader config object
			var loaderConfig = this.buildLoaderConfig(treePostLoadHandler);
			// call function to build the tree.
			this.buildTree(loaderConfig, appendEventHandler, checkChangeEventHandler);
		}
	};
}();
