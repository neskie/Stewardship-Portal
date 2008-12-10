/*
author: alim karim
date: 	Dec 09, 2008 
desc:   sql statements to link previously created default classes
		to styles.

		note that the script assumes the classes and styles have the
		same name and are identical to the ones listed in the 
		IN list.
*/

BEGIN TRANSACTION;

INSERT INTO tng_ms_class_ms_style
SELECT 
	tng_mapserver_class.id, 
	tng_mapserver_style.id
FROM 
	tng_mapserver_class 
	INNER JOIN tng_mapserver_style 
	ON tng_mapserver_class.name = tng_mapserver_style.name 
WHERE tng_mapserver_class.name IN ('polygon default', 'line default', 'point default');

ROLLBACK TRANSACTION;
