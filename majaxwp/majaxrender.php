<?php
namespace MajaxWP;

use stdClass;

Class MajaxRender {	
	private $callFromAjax;	
	private $postType;
	private $htmlElements;

	function __construct($ajax=false) {	
			$this->htmlElements=new MajaxHtmlElements();
			$this->callFromAjax=$ajax;
			//init custom fields			
			if ($this->callFromAjax) { 
				$this->setPostType();				
				$this->loadFields();
			}
	}
	function loadFields() {
		//$this->logWrite("cpt!@ {$this->getPostType()}");
		$this->fields=new CustomFields();
		$this->fields->prepare($this->getPostType());
		$this->fields->loadPostedValues();			
		ImageCache::loadImageCache($this->getPostType());		
	}
	function getPostType() {			
		return $this->postType; 	
	}
	function setPostType($cpt="") {		
		if ($cpt) $this->postType=$cpt;	
		else $this->postType=filter_var($_POST['type'], FILTER_SANITIZE_STRING); 	
		Caching::setPostType($this->getPostType());
	}

	function regShortCodes() {		
		add_shortcode('majaxfilter', [$this,'printFilters'] );
		add_shortcode('majaxcontent', [$this,'printContent'] );
		add_shortcode('majaxstaticcontent', [$this,'showStaticContent'] );
	}
	
	function showStaticContent($atts = []) {					
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		if (isset($atts["type"])) $type=$atts["type"]; //we load postType from shortcode attribute				 
		$this->setPostType($type);		
		$this->loadFields();		
		ob_start();
		$this->htmlElements->showMainPlaceHolderStatic(true,$type);
		$postId=filter_var($_GET['id'], FILTER_SANITIZE_STRING);
		if ($postId) { 
			$query=$this->buildSingle($postId);
			$this->htmlElements->showIdSign();
		}
		else $query=$this->buildQuerySQL();
		$rows=Caching::getCachedRows($query);
		
		foreach ($rows as $row) {
			$metaMisc=$this->buildInit();
			$item=[];
			$item=$this->buildItem($row,"","",0);		
			$this->logWrite("row ".$item["image"]);				
			$this->htmlElements->showPost(1,$row["post_name"],$row["post_title"],$item["image"],$item["content"],$metaMisc["misc"],$item["meta"]);
		}

		$this->htmlElements->showMainPlaceHolderStatic(false);
		return ob_get_clean();
	}
	
	function printFilters($atts = []) {
		 $atts = array_change_key_case( (array) $atts, CASE_LOWER );
		 if (isset($atts["type"])) $type=$atts["type"]; //we load postType from shortcode attribute				 
		 $this->setPostType($type);
		 $this->loadFields();
		//prints filter, run by shortcode majaxfilter	
		ob_start();		
		 $this->htmlElements->showFilters($this->postType,$this->fields->getFieldsFiltered());		 
		 return ob_get_clean();
	}	
	function printContent($atts = []) {	
		//prints content, run by shortcode majaxcontent		
		ob_start();
		$this->htmlElements->showMainPlaceHolder();
		return ob_get_clean();
	}	
	function buildSingle($id) {	
		//get all posts and their metas						
		$limit=""; //need all rows for counts
		$mType = $this->getPostType();
		$col="";
		$filters="";
		$colSelect="";
		foreach ($this->fields->getFieldsFilteredOrDisplayed() as $field) {			
			$fieldName=$field->outName();		
			$col.=$this->getSqlFilterMeta($fieldName);
			$colSelect.=$this->getSqlSelectMeta($fieldName);		

		}
		$additionalMetas=["_thumbnail_id"];
		foreach ($additionalMetas as $fieldName) {			
			$col.=$this->getSqlFilterMeta($fieldName);
			$colSelect.=$this->getSqlSelectMeta($fieldName);
		}

		$filters=" WHERE post_name like '$id'";
		$query=
		"
		SELECT post_title,post_name,post_content{$colSelect}  FROM
		(SELECT post_title,post_content,post_name 
			$col
			FROM wp_posts LEFT JOIN wp_postmeta pm1 ON ( pm1.post_id = ID) 
			WHERE post_id=id 
			AND post_status like 'publish' 
			AND post_type like '$mType'			
			GROUP BY ID, post_title
			) AS pm1
			$filters
			$limit
		";
		$this->logWrite("queryitem {$query}");

		return $query;
	}	
	function getSqlFilterMeta($fieldName) {
		return ",MAX(CASE WHEN pm1.meta_key = '$fieldName' then pm1.meta_value ELSE NULL END) as `$fieldName`";
	}
	function getSqlSelectMeta($fieldName) {
		return ",pm1.`$fieldName`";
	}
	function buildQuerySQL() {	
		//get all posts and their metas, filter only non selects for multiple selections		
		$limit=" LIMIT 10";		
		$limit=""; //need all rows for counts
		$mType = $this->getPostType();
		$col="";
		$filters="";
		$colSelect="";
		foreach ($this->fields->getFieldsFilteredOrDisplayed() as $field) {			
			$fieldName=$field->outName();		
			$col.=$this->getSqlFilterMeta($fieldName);
			$colSelect.=$this->getSqlSelectMeta($fieldName);
			$filter=$field->getFieldFilterSQL();
			if ($filter && !$field->typeIs("select")) {
				if ($filters) $filters.=" AND ";
				$filters.=$filter;
			}

		}
		$additionalMetas=["_thumbnail_id"];
		foreach ($additionalMetas as $fieldName) {			
			$col.=$this->getSqlFilterMeta($fieldName);
			$colSelect.=$this->getSqlSelectMeta($fieldName);
		}

		if ($filters) $filters=" WHERE $filters";
		$query=
		"
		SELECT post_title,post_name,post_content{$colSelect}  FROM
		(SELECT post_title,post_name,post_content 
			$col
			FROM wp_posts LEFT JOIN wp_postmeta pm1 ON ( pm1.post_id = ID) 
			WHERE post_id=id 
			AND post_status like 'publish' 
			AND post_type like '$mType'			
			GROUP BY ID, post_title
			) AS pm1
			$filters
			$limit
		";
		$this->logWrite("queryitem {$query}");

		return $query;
	}
	function filterMetaSelects($rows) {
		$outRows=[];
		$fields=$this->fields->getFieldsOfType("select");	

		foreach ($rows as $row) {
			foreach ($fields as $field) {				
				$skip=false;
				
				if (!$field->isInSelect($row[$field->outName()])) {					
					$skip=true;
					break;
				}
				
			}
			if (!$skip) $outRows[]=$row;
		}
		return $outRows;
	}		
	
	function buildItem($row,$addFieldKey="",$addFieldValue="",$getJson=1) {
		$ajaxItem=new MajaxItem();
		$ajaxItem->addField("title",$row["post_title"])
		->addField("name",$row["post_name"])
		->addField("id",$row["ID"])
		->addField("content",$row["post_content"])->addField("url",$row["slug"])
		->addField("image",ImageCache::getImageUrlFromId($row["_thumbnail_id"]));
		if ($addFieldKey && $addFieldValue) $ajaxItem->addField($addFieldKey,$addFieldValue);
		foreach ($this->fields->getFieldsDisplayed() as $field) {
		 $ajaxItem->addMeta($field->outName(),$row[$field->outName()]);
		}	
		$out=$ajaxItem->expose($getJson);
		$this->logWrite("exposed: ".$out);
		return $out;					
	}
	function buildInit() {
		$row=[];
		$row["title"]="buildInit";

		foreach ($this->fields->getFieldsFilteredOrDisplayed() as $field) {								
			$row["misc"][$field->outName()]["icon"]=$field->icon;
			$row["misc"][$field->outName()]["fieldformat"]=$field->fieldformat;
			$row["misc"][$field->outName()]["min"]=$field->valMin;
			$row["misc"][$field->outName()]["max"]=$field->valMax;			
			$row["misc"][$field->outName()]["displayorder"]=$field->displayOrder;	
			$row["misc"][$field->outName()]["title"]=$field->title;	
			$row["misc"][$field->outName()]["type"]=$field->type;	
		}
		return $row;	
	}
	function buildCounts($rows,$cachedJson) {
		$out=[];
		$c=[];	
		if ($cachedJson) {
			$out=$cachedJson;
		}
		else {
			$out[]=["meta_key" => "clearall", "meta_value" => "clearall", "count" => "0", "post_title" => "" ];

			foreach ($rows as $row) {
				foreach ($this->fields->getFieldsFiltered() as $field) {			
					$val=$row[$field->outName()];
					$c[$field->outName()][$val]++;
				}	
				
			}
			foreach ($this->fields->getFieldsFiltered() as $field) {			
				$fieldName=$field->outName();						
				foreach ($c[$fieldName] as $val => $cnt) {	
					//$this->logWrite("iter:{$fieldName} {$val} {$cnt} ");				
						$m["meta_key"]=$fieldName;
						$m["meta_value"]=$val;
						$m["count"]=$cnt;
						$m["post_title"]="counts";
						$out[]=$m;
				}
			}	
			
			$out[]=["meta_key" => "endall", "meta_value" => "endall", "count" => "0", "post_title" => "" ];
	
			$this->logWrite("json out:".json_encode($out));
		}		
		return $out;
	}
	function showPagination($cntTotal,$aktPage,$cntPerPage) {
		$row=[];
		$row["title"]="pagination";
		$pages=ceil($cntTotal/$cntPerPage);				
		if ($pages<=0) return $row;
		for ($n=0;$n<$pages;$n++) {			
			if ($n==$aktPage) $row[$n] = "2";
			else $row[$n] = "1";
		}		
		return $row;
	}
	function getMiscAction($action="",$postTitle="") {		
		$row=[];
		if ($action=="action") {
			$row["title"]="action";
			$row["content"]="";
			$row["postTitle"]=$postTitle;
		}
		if ($action=="contactFilled") {
			$postedFields=["fname" => "Jméno", "lname" => "Příjmení", "email" => "Email", "start_date" => "Začátek pronájmu", 
			"end_date" => "Konec pronájmu", "phone_no" => "Telefon", "expected_mileage" => "Předpoklad km", "business" => "Již je firemní zákazník", 
			"postTitle" => "Vybrané auto"];
			$out="";
			foreach ($postedFields as $name => $value) {
				if ($out) $out.="\n";
				//$out.="$name: ".filter_var($_POST[$name], FILTER_SANITIZE_STRING);
				$out.="$name - $value: ".$_POST[$name];
			}
			$this->logWrite("aktpage ".$out,"filledform.txt");
			$row["title"]="action";
			$row["content"]="Díky za odeslání. Budeme vás brzy kontaktovat.";
		}
		return $row;
	}
	function showRows($rows,$delayBetweenPostsSeconds=0.5,$custTitle="",$limit=9,$aktPage=0,$miscAction="") {
		$n=0;	
		$showPosts=true;
		$totalRows=count($rows);

		if ($custTitle != "majaxcounts") {
			if ($totalRows<1)	 {
				$this->sendBlankResponse();
			}
			$pagination=$this->showPagination($totalRows,$aktPage,$limit);
			$rows=array_slice($rows,$aktPage*$limit,$limit);		
			//$rows=array_slice($rows,0,10);		
			$this->logWrite("aktpage ".$aktPage);
		}
		
		foreach ($rows as $row) {
			//if ($limit>0 && $n>$limit) break;
			if ($custTitle=="majaxcounts") { 
				$row["title"]=$custTitle;
				$this->logWrite("countitem ".json_encode($row));
				echo json_encode($row).PHP_EOL;								
			}
			else {
				 if ($n==0) {
					 //first row
					echo json_encode($this->buildInit()).PHP_EOL;	
					if ($miscAction) echo json_encode($this->getMiscAction($miscAction,$row["post_title"])).PHP_EOL;	
					if ($miscAction=="contactFilled") $showPosts=false;
				 }
				 if ($showPosts) {
					if (count($rows)==1) echo $this->buildItem($row,"single","yes").PHP_EOL;
					else echo $this->buildItem($row).PHP_EOL;
				 }
				 
				 if ($n==count($rows)-1) { 
					 //last row
					echo json_encode($pagination).PHP_EOL;						
				 }
				 
			} 
			flush();
			ob_flush();
			session_write_close();
			if ($delayBetweenPostsSeconds>0) usleep($delayBetweenPostsSeconds*1000000);	
			$n++;
		}	
		//exit;	
	}
	private function createResponse() {
		$response=new StdClass();
		return $response;
	}

	function sendBlankResponse() {
		$response=$this->createResponse();
		$response->title="empty";	
		$response->content="Sorry, no results.";
		echo json_encode($response).PHP_EOL;
		flush();
		ob_flush();		  
		exit;
	}

	function logWrite($val,$file="log.txt") {
	 file_put_contents(plugin_dir_path( __FILE__ ) . $file,date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
	}
}