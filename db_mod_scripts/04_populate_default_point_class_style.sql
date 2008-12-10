/*
author: alim karim
date: 	Dec 09, 2008 
desc:   sql statements to create a default 
		mapserver class and style for points.
		this class and style will be associated with a 
		newly created schema. without an existing style or class,
		the data loaded into a new schema will not be
		visible on the mapping agent.
*/

BEGIN TRANSACTION;
---------------------------------------------------------
-- Default Point

-- mapfile snippet
-- CLASS
--	NAME "default"
--	TEMPLATE "nepas.html"
--	STYLE
--	    SIZE 3
--	    COLOR 0 0 0
--	END
-- END
---------------------------------------------------------
-- create default point class 
INSERT INTO 
	tng_mapserver_class (name, class_desc)
	VALUES 
		(
			'point default', 
			'default point class for new schema'
		);

-- create default point style
INSERT INTO
	tng_mapserver_style(
						name,
						style_desc,
						symbol_name,
						symbol_size,
						color_r,
						color_g,
						color_b
						)
VALUES
		(
			'point default',
			'size 2  black circle',
			'circle',
			3, -- symbol size
			0, 
			0,
			0
		);	


ROLLBACK TRANSACTION;
