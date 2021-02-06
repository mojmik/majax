<?php
namespace MajaxWP;

class CustomField {
 
	public function __construct($name="",$value="",$type="",$title="",$compare="=",$valMin=false,$valMax=false,$postType="hp_listing") {
	 $this->name=$name;	 
	 $this->value=$value;	 
	 $this->type=$type;	 
	 $this->title=$title;	
	 $this->compare=$compare;	   
	 $this->valMin=$valMin;	
	 $this->valMax=$valMax;	
	 $this->postType=$postType;  
	}
	public function outName() {
		return "{$this->name}";
	}
	public function outField() {
		return "{$this->name};{$this->value};{$this->type};{$this->compare}";
	}
	public function outSelectOptions() {
	   $values=explode(";",$this->value);
	   foreach ($values as $val) {
		$out.="<option value='$val'>$val</option>";	
	   }
	   return $out;
	}
	public function outFieldFilter() {
		$labelFor="for='custField".urlencode($this->name)."'";
		if ($this->compare=="") return ""; //compare=="" => field not filterable, only displayable
		if ($this->typeIs("select") && $this->value!="too many") {
			//lets gen a nice selectbox
		   return "<label {$labelFor}>{$this->title}</label>
		   <select name='".$this->name."' 
		   data-group='majax-fields' 
		   id='custField".urlencode($this->name)."' 
		   class='majax-select' 
		   multiple='multiple'>
		   {$this->outSelectOptions()}
		   </select>";
		}
		else if ($this->compare==">") {
		   return "<label {$labelFor}>{$this->title}</label>
			   <div class='sliderrng' id='majax-slider-".urlencode($this->name)."'></div>
			   <input class='sliderval' type='text' name='".$this->name."' data-group='majax-fields' data-mslider='majax-slider-".urlencode($this->name)."' id='custField".urlencode($this->name)."'></input>					
			   "; 
		}
		else if ($this->type=="bool") {
		   return "<label {$labelFor}>{$this->title}</label><input class='majax-fireinputs' type='checkbox' name='".$this->name."' data-group='majax-fields' id='custField".urlencode($this->name)."'></input>";	 
		}
		return "<label {$labelFor}>{$this->title}</label><input class='majax-fireinputs' type='text' name='".$this->name."' data-group='majax-fields' id='custField".urlencode($this->name)."'></input>";
	}
	public function initValues() {
	   global $wpdb;
	   $maxValues=50;
		   
	   $query="SELECT DISTINCT(`meta_value`) AS val FROM ".$wpdb->prefix."postmeta AS pm 
	   WHERE pm.meta_key like '{$this->name}' LIMIT 0,".($maxValues+10);
	   
	   foreach( $wpdb->get_results($query) as $key => $row) {	
		   if ($n>$maxValues) {
			   $vals="too many";
			   break;
		   }
		   if ($n>0) $vals.=";";
		   $vals.=$row->val;		
		   $n++;
	   }	
	   
	   $this->value=$vals;
	}
	public function initValMin() {
	   global $wpdb;
	   
	   $query="SELECT MIN(`meta_value`) AS min FROM ".$wpdb->prefix."postmeta AS pm, ".$wpdb->prefix."posts AS po 
	   WHERE pm.meta_key like '{$this->name}' AND po.post_status = 'publish' 
	   AND po.post_type = '{$this->postType}'";
	   
	   $query="SELECT MIN(`meta_value`) AS min FROM ".$wpdb->prefix."postmeta AS pm 
	   WHERE pm.meta_key like '{$this->name}'";
	   
	   $min = $wpdb->get_var($query);	 
	   $this->valMin=$min;
	   //echo "<br />".$query;
	}
	 public function initValMax() {
	   global $wpdb;
	   $query = "SELECT MAX(`meta_value`) AS max FROM ".$wpdb->prefix."postmeta AS pm, ".$wpdb->prefix."posts AS po 
	   WHERE pm.meta_key like '{$this->name}' AND po.post_status = 'publish' 
	   AND po.post_type = '{$this->postType}'";	
   
	   $query = "SELECT MAX(`meta_value`) AS max FROM ".$wpdb->prefix."postmeta AS pm 
	   WHERE pm.meta_key like '{$this->name}'";	
	   
	   $max = $wpdb->get_var($query);	 
	   $this->valMax=$max;
	   //echo "<br />".$query;
	}
	public function getValMin() {
	  if ($this->valMin===false) $this->initValMin();
	  return $this->valMin;
	}
	public function getValMax() {
	  if ($this->valMax===false) $this->initValMax();
	  return $this->valMax;
	}
	public function getValues() {
	  if ($this->values=="") $this->initValues();
	  return $this->value;
	}
	public function typeIs($type) {
	 if (strtoupper($this->type)==strtoupper($type)) return true;
	 return false;
	}
	public function getFieldFilter() {
			   $val=$_POST[$this->name];			   
			   if ($val=="") {
				return false;	
			   }
			   $val=filter_var($val, FILTER_SANITIZE_STRING);
			   if (strpos($val,"|")>0) {
				   //multiple values in select field
				   $compare="IN";	//multiple values selection
				   if ($this->typeIs("NUMERIC")) $compare="BETWEEN"; //numeric range
				   return array(
					   'key'		=> $this->name,
					   'value'		=> explode("|",$val),
					   'type'		=> $this->type,
					   'compare'	=> $compare
				   );				
			   }
			   else if ($this->typeIs("bool")) {				
				   if ($val=="on" || $val=="1") $val="1";				
				   else $val="0";
				   return array(
					   'key'		=> $this->name,
					   'value'		=> $val,					
					   'compare'	=> '='
				   );	
			   }
			   else {				
				   //single value
				   return array(
					   'key'		=> $this->name,
					   'value'		=> $val,
					   'type'		=> $this->type,
					   'compare'	=> $this->compare
				   );
			   }
	}
	public function save() {
	  global $wpdb;
	  $query = "DELETE FROM `".$wpdb->prefix."majax_fields` WHERE `name` like '{$this->name}';";   
	  $result = $wpdb->get_results($query);	 
	  
	  $query = "INSERT INTO `".$wpdb->prefix."majax_fields` ( `name`, `value`, `type`, `title`, `compare`, `valMin`, `valMax`, `postType`) 
	   VALUES ('{$this->name}', '{$this->value}', '{$this->type}', '{$this->title}', '{$this->compare}', '{$this->valMin}', '{$this->valMax}', '{$this->postType}');";   
	  $result = $wpdb->get_results($query);	 
	  return "<br />{$this->name} saved $query";
	}
   }
