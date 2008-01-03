/*
author:	alim karim
date:	dec 31, 2007
desc:	this script will aid in migrating the
	data from the old portal permission 
	model (based on layers) to the new
	model (based on submissions).
	the script discovers the permissions
	on layers and uses the submission id of those
	layers to create entries in the tng_submission_permission
	table.

	this takes care of all submissions that have 
	at least one layer.

	the second part of the script looks at the uid 
	and uid_assigned of each submission and enters 
	these into the tng_submission_permission - provided
	they dont exist in that table already.
	
*/

begin transaction;
-- create entries based on layer permissions
insert into tng_submission_permission (sub_id, uid)
select 
	tng_spatial_layer.form_submission_id,
	tng_layer_permission.uid
from 
	tng_layer_permission
	inner join tng_spatial_layer on tng_layer_permission.layer_id = tng_spatial_layer.layer_id;
-- create entries based on who 
-- created the submission
insert into tng_submission_permission (sub_id, uid)
select 
	form_submission_id,
	uid
from
	tng_form_submission
where uid not in (select uid from tng_submission_permission);

-- create entries based on who the
-- submission is assigned to
insert into tng_submission_permission (sub_id, uid)
select 
	form_submission_id,
	uid_assigned
from
	tng_form_submission
where uid_assigned not in (select uid from tng_submission_permission);

rollback transaction;	

