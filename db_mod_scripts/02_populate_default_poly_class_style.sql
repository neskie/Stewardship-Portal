/*
author: alim karim
date: 	Dec 09, 2008 
desc:   sql statements to create a default 
		mapserver class and style for polygons.
		this class and style will be associated with a 
		newly created schema. without an existing style or class,
		the data loaded into a new schema will not be
		visible on the mapping agent.
*/

BEGIN TRANSACTION;

---------------------------------------------------------
-- Default 

-- mapfile snippet
-- CLASS
--	NAME "default"
--	TEMPLATE "nepas.html"
--	STYLE
--		COLOR -1 -1 -1
--		OUTLINECOLOR 255 0 0     
--	END
-- END
---------------------------------------------------------
-- create default class
INSERT INTO 
	tng_mapserver_class (name, class_desc)
VALUES 
	(
	'polygon default', 
	'default polygon class for new schema'
	);

-- create default style
INSERT INTO
	tng_mapserver_style(
				name,
				style_desc,
				color_r,
				color_g,
				color_b,
				outlinecolor_r,
				outlinecolor_g,
				outlinecolor_b
			)
VALUES
	(
	  'polygon default',
	  'polygons with black outline and no fill',
	  -1, -- no fill color
	  -1,
	  -1,
	  0, -- black outline color
	  0,
	  0
	);

ROLLBACK TRANSACTION;

