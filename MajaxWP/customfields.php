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
	foreach ($this->fieldsList as $f) {
	  if ($out!="") $out.="|";	
	  $out.=$f->outField();
	}
	return $out;
  }
   public function readValues(bool $doSave=true) {
	foreach ($this->fieldsList as $f) {
	  $out.="min:".$f->getValMin();
	  $out.="max:".$f->getValMax();
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
	$query = "SELECT * FROM `".$wpdb->prefix."majax_fields`";
	$load=false;
	foreach( $wpdb->get_results($query) as $key => $row) {
		$this->fieldsList[] = new CustomField($row->name,$row->value,$row->type,$row->title,$row->compare,$row->valMin,$row->valMax,$row->$postType);
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


