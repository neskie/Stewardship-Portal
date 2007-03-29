<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_login.php

desc:	web page for logging in into the tng portal
---------------------------------------------------------------*/

dl('php_ogr.so');
include_once('classes/class_dbconn.php');

if(isset($_POST['posted'])){
	OGRRegisterAll();
	$shp_tmp_name = $_FILES['shp']['tmp_name'];
	$dbf_tmp_name = $_FILES['dbf']['tmp_name'];
	$shx_tmp_name = $_FILES['shx']['tmp_name'];
	
	$shp_name = "/tmp/test.shp";
	$dbf_name = "/tmp/test.dbf";
	$shx_name = "/tmp/test.shx";
	
	
	move_uploaded_file($shp_tmp_name, $shp_name);
	move_uploaded_file($dbf_tmp_name, $dbf_name);
	move_uploaded_file($shx_tmp_name, $shx_name);
	
	$src_driver = NULL;
    	$src_ds = OGROpen($shp_name, FALSE, $src_driver);
	if($src_ds == NULL){
		echo "Could not open src shape file";
	}else{
		$dst_layer = "";
		$dst_layer_name = "";
		$dst_attr_table_name = "";
		$dst_driver = NULL;
		/* change this so that the connection info is read from class_dbconn.php */
		$dst_ds = OGROpen("PG:host=142.207.144.71 dbname=tng_dev user=tng_readwrite password=tng_readwrite", FALSE, $dst_driver);
		if($dst_ds == NULL){
			echo "could not open dest.";
			exit;
		}
		echo "shapefile opened, postgres source opened";
		// shapefiles only have one layer, so 
		// the second arg to GetLayer is 0.
		$src_layer = OGR_DS_GetLayer($src_ds, 0);
		if($src_layer == NULL){
			echo "could not open source layer";
			return;
		}
		
		$feature_defn = OGR_L_GetLayerDefn($src_layer);
		
		/*---------------------------------------------------------------------------------------*/
		/* this is done to find out the geometry type only - should be moved to its own function */
		$feature = OGR_L_GetNextFeature($src_layer);
		$geometry = OGR_F_GetGeometryRef($feature);
		$geom_type = strtolower(OGR_G_GetGeometryName($geometry));
		// execute sql command using form id to find out spatial table name and attribute table
		// namme
		$sql_str = "SELECT "
		      		. "tng_spatial_data.table_name, "
		      		. "tng_spatial_attribute_table.attr_table_name "
				."FROM "
		    		. "tng_form_spatial_data "
		    		. "INNER JOIN tng_spatial_data ON tng_form_spatial_data.spatial_table_id = tng_spatial_data.spatial_table_id "
		    		. "INNER JOIN tng_spatial_attribute_table on tng_form_spatial_data.spatial_table_id = tng_spatial_attribute_table.spatial_table_id "
				. "WHERE "
		     		. "tng_form_spatial_data.form_id = 1 "
					. "AND "
					. "tng_spatial_data.geometry_type = '" . $geom_type . "'";
		
		$dbconn = new DBConn();
		$dbconn->connect();

		$result = pg_query($dbconn->conn, $sql_str);

		if(!$result){
			echo "An error occurred while executing the query - class_form.php:47 " . pg_last_error($dbconn->conn);
			$dbconn->disconnect();
		}else{ 
			$dst_layer_name = pg_fetch_result($result, 0, 0);
			$dst_attr_table_name = pg_fetch_result($result, 0, 1);
			$dbconn->disconnect();
		} 
		/* end find geometry type */
		/*---------------------------------------------------------------------------------------*/

		/* get the handle for the destination layer */
		$dst_layer = OGR_DS_GetLayerByName($dst_ds, $dst_layer_name);
		if($dst_layer == NULL){
			echo "could not create a handle for " . $dst_layer_name;
			exit;
		}
		
		echo "<br> dst layer : " . $dst_layer_name . "<br>";		
		
		OGR_L_ResetReading($src_layer);
		
		//for( $i = 0; $i < OGR_FD_GetFieldCount($feature_defn); $i++ )
		while (($feature = OGR_L_GetNextFeature($src_layer)) != NULL)
		    {
				// create a blank feature in the
				// destination layer.
				$dst_feature_defn = OGR_L_GetLayerDefn($dst_layer);
				$dst_feature = OGR_F_Create($dst_feature_defn);
				
				// now copy the feature from the source
				// layer to the dest. layer
				// the last value, bForgiving must be set to
				// true because our source and destination
				// schemas dont match
				if(OGR_F_SetFrom($dst_feature, $feature, TRUE) != OGRERR_NONE){
					echo "could not set destination feature from source feature";
					exit;
				}
				
				// otherwise the feature was set
				// from the source feature.
				// set the layer_id (fk) of the feature
				// to the layer id created at the time
				// of submission.
				$layerid_f_index = OGR_F_GetFieldIndex($dst_feature, "layer_id");
				OGR_F_SetFieldInteger($dst_feature, $layerid_f_index, -1);
				
				// now "create" this feature within the
				// destination layer. the method should
				// be called "add" rather than "create"
				if(OGR_L_CreateFeature($dst_layer, $dst_feature) != OGRERR_NONE){
					echo "could not create feature in destination layer ";
					exit;
				}
				
				//$geometry = OGR_F_GetGeometryRef($feature);
				//$txt_buffer = "";
				//$res = OGR_G_ExportToWkt($geometry, $txt_buffer);
				//$res = OGR_G_GetGeometryName($geometry);
				//OGR_G_ExportToWkb($geometry, $txt_buffer); 
				//OGR_G_ExportToWkb 
				//$len = OGR_G_WkbSize($geometry);
				
				
				echo "feature: " . $txt_buffer . ", " .$res . "<br> ----- [fields] : ";
		        //$field_defn = OGR_FD_GetFieldDefn( $feature, $i);
				$num_fields = OGR_F_GetFieldCount( $dst_feature );
				for( $i = 0; $i < $num_fields; $i++){
					//$field_defn = OGR_FD_GetFieldDefn( $feature_defn, $i);
					$field_defn = OGR_FD_GetFieldDefn( $dst_feature_defn, $i);
					$f_name = strtolower(OGR_Fld_GetNameRef($field_defn));
					$f_type = OGR_Fld_GetType($field_defn);
					if($f_type == OFTString)
						$f_type = "string";
					else if($f_type == OFTInteger)
							$f_type = "int";
					else if($f_type == OFTReal)
							$f_type = "double";		
					else
							$f_type = "unknown";
					$f_value = OGR_F_GetFieldAsString($dst_feature, $i);
					echo $f_name . ": " . $f_value . "; ";
				}
				echo "<br>";
		    }
		
	}
	
	//echo OGRGetDriverCount();
    //for( $iDriver = 0; $iDriver < OGRGetDriverCount(); $iDriver++ )
    //{
     //   printf( "  -> %s<br>", OGR_DR_GetName(OGRGetDriver($iDriver)) );
    //}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TNG Portal Login</title>
</head>
<body>
	<form id="tng_login_form" name="tng_login_form" method="post" enctype="multipart/form-data" action="php_ogr_test.php">
	<table>
		<tr>
			<td> shp: <input type="file" name="shp"/> </td>
		</tr>
		<tr>
			<td> dbf: <input type="file" name="dbf"/> </td>
		</tr>
		<tr>
			<td> shx: <input type="file" name="shx"/> </td>
		</tr>
			<tr>
				<td> shx: <input type="hidden" name="posted" value="posted"/> </td>
			</tr>
		<tr>
			<td> <input type="submit" value="sumbit"/> </td>
		</tr>				
	</table>
	</form>
<body>
</body>
</html>
