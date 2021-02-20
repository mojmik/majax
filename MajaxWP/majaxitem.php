<?php
namespace MajaxWP;

Class MajaxItem {	
    private $metaFields=array();
    private $mainFields=array();  
    public function addMeta($metaKey,$metaValue) {
        $this->metaFields[$metaKey]=($metaValue);
        return $this;
    }    
    public function addField($fieldKey,$fieldValue) {
        $this->mainFields[$fieldKey]=($fieldValue);
        return $this;
    }
    public function expose() {        
        //return json_encode(get_object_vars($this));
        //return json_encode(array_merge($this->mainFields,$this->metaFields));
        $arr=$this->mainFields;
        $arr["meta"]=$this->metaFields;
        return json_encode($arr);        
    }
}
