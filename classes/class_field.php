<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 05, 2007
file:	class_field.php

desc:	abstraction of a field in a form
---------------------------------------------------------------*/

class Field{
	var $id;
	var $name;
	var $type;
	var $label;
	var $rank;
	var $label_css_class;
	var $css_class;
	var $searchable;
	var $value;
	
	//
	/// constructor
	/// instantiate a field object
	///
	function Field($id, $name, $type, $label, $rank, $label_css_class, $css_class, $searchable, $value){
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->label = $label;
		$this->rank = $rank;
		$this->label_css_class = $label_css_class;
		$this->css_class = $css_class;
		$this->searchable = $searchable;
		$this->value = $value;
	}
	
	///
	/// set_value()
	/// set the value member of the
	/// field to the given value
	///
	function set_value($value){
		$this->value = $value;
	}
}

?>