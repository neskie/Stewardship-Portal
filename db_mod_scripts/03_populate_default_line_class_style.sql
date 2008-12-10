/*
author: alim karim
date: 	Dec 09, 2008 
desc:   sql statements to create a default 
		mapserver class and style for lines.
		this class and style will be associated with a 
		newly created schema. without an existing style or class,
		the data loaded into a new schema will not be
		visible on the mapping agent.
*/

BEGIN TRANSACTION;
---------------------------------------------------------
-- Default Line 

-- mapfile snippet
-- CLASS
--	NAME "default"
--	TEMPLATE "nepas.html"
--	STYLE
--	    WIDTH 3
--	    COLOR 255 0 0
--	END
-- END
---------------------------------------------------------
-- create default line class 
INSERT INTO 
	tng_mapserver_class (name, class_desc)
	VALUES 
		(
			'line default', 
			'default line class for new schema'
		);

-- create default line style
INSERT INTO
	tng_mapserver_style(
						name,
						style_desc,
						width,
						color_r,
						color_g,
						color_b
						)
VALUES
		(
			'line default',
			'1px black line',
			1, -- width = 1 (line thickness)
			0, 
			0,
			0
		);	


ROLLBACK TRANSACTION;
