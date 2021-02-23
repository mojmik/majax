<?php
namespace MajaxWP;

class CustomFields {
  public $fieldsList=array();
  public function addField($c) {
    $this->fieldsList[] = $c;	  
  }
  public function getList() {
	return $this->fieldsList;
  }
  public function outFields() {
	$out="";
	foreach ($this->fieldsList as $f) {
	  if ($out!="") $out.="|";	
	  $out.=$f->outField();
	}
	return $out;
  }
  public function getFieldsFilteredGreaterThan($filter=[]) {
	$rows=[];
	foreach ($this->fieldsList as $f) {
		$skip=false;
		foreach ($filter as $key=>$value) {			
			if ($f->$key <= $value) { 
				$skip=true;				
			}	
		}
		if (!$skip) $rows[]=$f;
	  } 
	  return $rows;
  }
  public function getFieldsFiltered() {
	return $this->getFieldsFilteredGreaterThan(["filterOrder" => "0"]);
  }
  public function getFieldsDisplayed() {
	return $this->getFieldsFilteredGreaterThan(["displayOrder" => "0"]);
  }
   public function readValues(bool $doSave=true) {
	$out="";
	foreach ($this->fieldsList as $f) {
	  $out.="values:".$f->getValues();
	  if ($doSave) $f->save();
	}
	return $out;
  }
  public function getFields() {
	  return $this->fieldsList;
  }

  public function loadFromSQL() {
	global $wpdb;
	$query = "SELECT * FROM `".$wpdb->prefix."majax_fields` WHERE `displayorder`>0 ORDER BY `filterorder`";
	$load=false;
	foreach( $wpdb->get_results($query) as $key => $row) {
		$this->fieldsList[] = new CustomField($row->name,$row->value,$row->type,$row->title,$row->compare,$row->valMin,$row->valMax,$row->postType,$row->icon,$row->filterorder,$row->displayorder,$row->fieldformat);
		$load=true;
	}	
	return $load;
  }
  public function saveToSQL() {
	  foreach ($this->fieldsList as $f) {
		  $f->save();
	  }
  }
}


