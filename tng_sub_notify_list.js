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
Ext.namespace('sub_notify');

Ext.BLANK_IMAGE_URL = 'ext-2.2/resources/images/default/s.gif'

sub_notify.app = function (){

	//private variables
	var submissionID = -1;
	var userID = -1;
	var userEmail = "";
	var isAdmin = false;
	var isNewSubmission = true;
	var backendURL = "tng_sub_notify_list_code.php";
	// list to hold user ids that are turned on in the tree
	var checkedUIDList = new Array();
	var addedEmailRecords = new Array();
	// HTML DIV containers
	var comboDiv = "search_combo";
	var addButtonDiv = "add_button";
	var submitButtonDiv = "submit_button";
	var gridDiv = "grid_div";
	// data stores
	var comboStore;
	var gridStore;
	// controls
	var searchCombo;
	var addButton;
	var notifyGrid;

	
	// ------------------ utility functions ------------------------------------

	///
	/// checkEmailInList
	/// check if the given email address exists in the grid
	/// store (notification list)
	///
	var checkEmailInList = function(email){
		return (gridStore.find("email", email) != -1)
	}

	///
	/// addEmailToList
	/// add given email address to the grid store (notification
	/// list)
	///
	var addEmailToList = function(email){
		// create record object
		var emailRecord = Ext.data.Record.create([
			{ name: 'email', mapping: 'email'}
		]);	
		// create data object based on record object
		var emailData = new emailRecord({email: email});
		// add record to store
		gridStore.add(emailData);
		// keep track of the new record
		addedEmailRecords.push(email);
	}
	
	///
	/// gridGetSelectedRecord
	/// get currently selected cell from the notify grid
	///
	var gridGetSelectedRecord = function(){
		var selModel = notifyGrid.getSelectionModel();
		// getSelectedCell returns an array of the form
		// [row, column]. 
		var cell = selModel.getSelectedCell();
		if(cell == null)
			return null;
		// use the first element to get the row index
		return gridStore.getAt(cell[0]);
	}
	
	///
	/// isNewEmailRecord
	/// check to see if the record is one that was added
	/// to the grid (email list) in the current session
	///
	var isNewEmailRecord = function(record){
		return (addedEmailRecords.indexOf(record) != -1)
	}
	
	///
	/// removeEmailFromList
	/// remove the given record from the gridStore andd the local
	/// array
	var removeEmailFromList = function(record){
		// remove record from grid store
		gridStore.remove(record);
		// remove record from addedEmailRecords array
		addedEmailRecords.remove(record.data["email"]);
	}

	///
	/// deleteEmailFromDB
	/// Admin function - deletes an email from the notification
	/// list from the backend i.e. non-new address
	///
	var deleteEmailFromDB = function(dataRecord){
		Ext.Ajax.request({
				url: backendURL,
				method: 'post',
				params: { 
					ajax_req: 'delete_from_notification_list',
					submission_id: submissionID,
					email: dataRecord.data['email']
				},
				success: function(transport){
					if(/error/.test(transport.responseText))
						alert(transport.responseText);
					// now delete it from the local store too
					gridStore.remove(dataRecord);
				},
				failure: function(transport){
					alert("An error occurred");
				}
			});
	}

	/// submitNotificationList
	/// submit the addedEmailRecords array to the backend script
	///
	var submitNotificationList = function(notifyTrueFalse){
		Ext.Ajax.request({
				url: backendURL,
				method: 'post',
				params: { 
					ajax_req: 'append_notification_list',
					submission_id: submissionID,
					notify: notifyTrueFalse,
					notification_list: Ext.util.JSON.encode(addedEmailRecords)
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

	}

	// ------------------ end utility functions ------------------------------------
	
	
	// ------------------ event handlers ------------------------------------
	///
	/// comboSelectHandler
	/// called when an item is selected from the search combo box
	///	
	var comboSelectHandler = function(combo, record, index){
		// get email addr from record
		var email = record.data['email'];
		// check if email already exists in notification list
		if(!checkEmailInList(email))
		// call function to add email to grid store
			addEmailToList(email);
		return true;
	}

	///
	/// addButtonHanlder
	/// called when the Add button next to the search combo
	/// is clicked.
	/// Used when the user types in a email adress that is not
	/// listed in the Portal's user table.
	/// The typed in email is added to the notification list
	var addButtonHandler = function(button, eventObj){
		var comboText = searchCombo.getValue();
		if(comboText != ""){
			// check if email already exists in notification list
			if(!checkEmailInList(comboText))
				// call function to add email to grid store
				addEmailToList(comboText); 
		}
	}	

	///
	/// gridDeleteButtonHandler
	/// called when the Delete button in the grid is clicked.
	///
	var gridDeleteButtonHandler = function(){
		// get selected row from the grid
		var record = gridGetSelectedRecord();
		// check if a valid record was selected
		if(record != null){	
			// check if the selected record is a newly
			// added record. We only allow users to delete
			// email addresses they entered in this session.
			if(isNewEmailRecord(record.data["email"]))
				// delete the record
				removeEmailFromList(record);
			else if(isAdmin) // admin users can delete old records
				deleteEmailFromDB(record);
		}
	}
	
	///
	/// submitButtonHandler
	/// called when the user clicks the submit button
	///
	var submitButtonHandler = function(){
		// admin users can access this page from two origins:
		// 1. new submissions
		// 2. editing from 'view submission details'
		// That is why we have to issue a prompt asking if a notification
		// email should be sent out (if they're editing the notification list, they
		// probably don't want to send out a notification email).
		if(isAdmin){
			Ext.MessageBox.show({
				title: 'Send Notification?',
				msg: 'Would you like to send a notification Email to the recipient list now?',
				buttons: Ext.Msg.YESNO,
   				fn: adminConfirmHanlder,
   				icon: Ext.MessageBox.QUESTION
			});
		}else
			// call function to submit added emails to the notification
			// list
			submitNotificationList(true);
	}
	
	///
	/// adminConfirmHanlder
	/// called when the admin user access this page and is given a Yes/No
	/// option about sending notifications right now.
	///
	var adminConfirmHanlder = function(yesNoResult){
		submitNotificationList(yesNoResult == "yes")
	}

	// ------------------ end event handlers ------------------------------------
	
	// public space
	return{
		///
		/// createComboStore
		/// create data store that serves the combo box's
		/// data
		///	
		createComboStore: function(){
			comboStore = new Ext.data.Store({
							proxy: new Ext.data.HttpProxy({
											url: backendURL,
											method: 'post'
							}),
							baseParams: {
								ajax_req: 'get_user_list'
							},
							reader: new Ext.data.JsonReader({
									id: 'id'
									},
									[
										{name: 'id', mapping: 'id'}, 
										{name: 'email', mapping: 'email'},
										{name: 'name', mapping: 'name'}
								]),
						});
		},
		
		///
		/// createGridStore
		/// create data store that serves the grid's
		/// data
		///	
		createGridStore: function(){
			gridStore = new Ext.data.Store({
							url: backendURL,
							baseParams: {
								ajax_req: 'get_notification_list',
								submission_id: submissionID
							},
							reader: new Ext.data.JsonReader({
									id: 'id'
									},
									[{name: 'id', mapping: 'id'}, {name: 'email', mapping: 'email'}]),
								listeners: {
									// load event is fired after the data is loaded from 
									// the AJAX call. Add the current user to the notification
									// list if they are not part of it yet.
									'load' :{ 
										fn: function(){
												// for non admin users,
												// if the current user is not on the notification list
												if(!isAdmin && !checkEmailInList(userEmail))
													// add them on
													addEmailToList(userEmail);
										}
									}
								}

						});
			gridStore.load();
		},

		///
		/// buildComboConfig
		/// create a new ext combo box config object
		///
		buildComboConfig: function(fnComboSelectHandler){
			// Custom rendering Template
	    	var resultTpl = new Ext.XTemplate(
        		'<tpl for="."><div class="x-combo-list-item">',
				'<p><b>{email}</b> </p>',
				'<p>{name}</p> </div></tpl>'
    		);

			var comboConfig = {
								store: comboStore,
								displayField: 'email',
								typeAhead: false,
								width: 300,
								pageSize: 10,
								fieldLabel: 'Search: ',
								emptyText: 'Enter a name or email address',
								minChars: 3,
								hideTrigger: true,
								tpl: resultTpl,
								applyTo: comboDiv,
								listeners: {
									'select': {
										fn:fnComboSelectHandler,
										scope: this
									}
								}	
							};
			return comboConfig;
		},
	
		///
		/// buildButtonConfig
		/// build a config for an ext button to appear in the given div
		///
		buildButtonConfig: function(buttonText, destDiv, fnClickHandler){
			var addButtonConfig = {
								text: buttonText,
								applyTo: destDiv,
								handler: fnClickHandler
							};

			return addButtonConfig;
		},
	
		///
		/// buildGridConfig
		/// create Ext gridpanel object config and columnModel
		/// for the grid.
		///
		buildGridConfig: function(fnDeleteButtonHanlder){
			var columnModel = new Ext.grid.ColumnModel([
							{ 
								id: 'email',
								header: 'E-mail Address',
								width: 220,
								dataIndex: 'email'
							}
			]);

			var gridConfig = {
								store: gridStore,
								cm: columnModel,
								autoExpandColumn: 'email',
								applyTo: gridDiv,
								height: 200,
								tbar: [{
										text: 'Delete',
										handler: fnDeleteButtonHanlder
								}]
							};
			return gridConfig;
		},

		init: function(){
			// note that sub_id, uid and is_new_submission
			// are global variables set via php in
			// tng_sub_notify.php
			submissionID = sub_id;
			userID = uid;
			isNewSubmission = is_new_sub;
			userEmail = user_email;
			isAdmin = is_admin; 
			// call function to build combo data store
			this.createComboStore();	
			// call function to build grid data store
			this.createGridStore();
			// call function to build combo config
			var comboConfig = this.buildComboConfig(comboSelectHandler);
			searchCombo = new Ext.form.ComboBox(comboConfig);
			// call function to build add button config
			var addButtonConfig = this.buildButtonConfig("Add", addButtonDiv, addButtonHandler);
			this.addButton = new Ext.Button(addButtonConfig);
			// call function to build grid config
			var gridConfig = this.buildGridConfig(gridDeleteButtonHandler);
			notifyGrid = new Ext.grid.EditorGridPanel(gridConfig);
			// call function to build submit button config
			var submitButtonConfig = this.buildButtonConfig("Submit", submitButtonDiv, submitButtonHandler);
			this.submitButton = new Ext.Button(submitButtonConfig);
		}
	};
}();
