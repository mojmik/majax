<?php
namespace MajaxWP;

use stdClass;

Class MajaxRender {	


	function __construct($loadFields=true) {		
		
			//init custom fields
			$this->fields=new CustomFields();
						
			//kdyz uz mam nacteny pole
			$forceReload=false;
			$loadValues=false;
			if (!$loadFields || $forceReload) {	
				//preloading hardcoded fields
				$this->fields->addField(new CustomField("mauta_kategorie","vetsi;mensi;dodavky","select","Kategorie","=",false,false,"mauta"));
				$this->fields->addField(new CustomField("mauta_znacka","---;Škoda;VW;Mercedes Benz;Hyundai;FIAT;Opel;Renault","select","Znacka","=",false,false,"mauta"));
				$this->fields->addField(new CustomField("mauta_cenaden","","NUMERIC","Cena - den",">",false,false,"mauta"));
				$this->fields->addField(new CustomField("mauta_automat","","bool","Automat","=",false,false,"mauta"));
				if ($forceReload) $this->fields->saveToSQL();
				if ($loadValues) echo $this->fields->readValues();
			}
			else {
				//loading meta fields from db
				$this->fields->loadFromSQL();
			}					
	}

	function regShortCodes() {		
		add_shortcode('majaxfilter', [$this,'majax_print_filter'] );
		add_shortcode('majaxcontent', [$this,'majax_print_content'] );
	}
	
	function initFields() {
		echo $this->fields->initFields();
	}
	function majax_print_filter($atts = []) {
		 $atts = array_change_key_case( (array) $atts, CASE_LOWER );
		 if (isset($atts["type"])) $type=$atts["type"]; //we load postType from shortcode attribute		
		//prints filter, run by shortcode majaxfilter	
		ob_start();		
		?>
		<form>
			<div class='majaxfiltercontainer'>			
					<input type='hidden' name='type' value='<?= $type?>' />
				<?php		
				foreach ($this->fields->getList() as $fields) {
				  ?> <div class='majaxfilterbox'> <?php  
							echo $fields->outFieldFilter();	
				  ?> </div> <?php
				}
				?>			
			</div>
		</form>
		<?php
		 return ob_get_clean();
	}	
	function majax_print_content($atts = []) {	
		//prints content, run by shortcode majaxcontent		
		ob_start();
		?>
		<div id="majaxmain" class="majaxmain">
		 <?php
		  //ajax content comes here
		 ?>
		</div> <?php
		 return ob_get_clean();
	}
	function buildQuery() {  
	  $catSlug = $_POST['category'];
	  $mType = filter_var($_POST['type'], FILTER_SANITIZE_STRING); 	
	  $hivePress=false;
	  if ($hivePress) {
		$postTypeDefault="hp_listing";  
		$taxonomy="hp_listing_category";
	  }	  
	  
	  $metaQuery["relation"] = 'AND';
	  
	  foreach ($this->fields->getList() as $field) {
		  $filter = $field->getFieldFilter();			  
		  if ($filter) { 		
		    $metaQuery[] = $filter;
		    $this->logWrite("name: {$field->name} filter: ".$filter." - ".$_POST[$field->name]);   		   
		  } 
	  }
	
	  $wpQuery=[	
		'posts_per_page' => 8,
		'orderby' => 'menu_order', 
		'order' => 'desc',
	  ];
	  if ($catSlug) { 
	   $wpQuery["taxonomy_terms"]=$catSlug;  	   	  
	   $wpQuery["taxonomy"]=$taxonomy;  	   
	  }
	  if ($mType) { 
	    $wpQuery["post_type"]=$mType;  
	  }
	  else if ($postTypeDefault) {		
		$wpQuery["post_type"]=$postTypeDefault;    
	  }	 
	  $wpQuery["meta_query"]=$metaQuery;  
	  $this->logWrite("query: ".json_encode($wpQuery));
	  return $wpQuery;
	}
	function buildQuerySQL() {	
		//get all posts and their metas			
		$limit=" LIMIT 10";		
		$mType = filter_var($_POST['type'], FILTER_SANITIZE_STRING); 
		$col="";
		$filters="";
		$colSelect="";
		foreach ($this->fields->getList() as $field) {			
			$fieldName=$field->outName();		
			$col.=",MAX(CASE WHEN pm1.meta_key = '$fieldName' then pm1.meta_value ELSE NULL END) as $fieldName";			
			$colSelect.=",PM1.$fieldName";
			$filter=$field->getFieldFilterSQL();
			if ($filter) {
				if ($filters) $filters.=" AND ";
				$filters.=$filter;
			}

		}
		if ($filters) $filters=" WHERE $filters";
		$query=
		"
		SELECT post_title,post_content{$colSelect}  FROM
		(SELECT post_title,post_content 
			$col
			FROM wp_posts LEFT JOIN wp_postmeta pm1 ON ( pm1.post_id = ID) 
			WHERE post_id=id 
			AND post_status like 'publish' 
			AND post_type like '$mType'			
			GROUP BY ID, post_title
			) AS PM1
			$filters
			$limit
		";
		$this->logWrite("countposts {$query}");

		return $query;
	}	
	function showRows($rows) {
		$delayBetweenPostsSeconds=0.5;
		$ajaxPost=new StdClass();
		foreach ($rows as $row) {
			$ajaxPost->title=$row["post_title"]."id:".$row["ID"];
			$ajaxPost->content=$row["post_content"];
			$ajaxPost->url="url";	
			$ajaxPost->meta="transmission:";
			echo json_encode($ajaxPost).PHP_EOL;
			flush();
			ob_flush();
		
			
			/*
			wp_send_json($ajaxPost);
			flush();
			ob_flush();
			*/
			session_write_close();
			usleep($delayBetweenPostsSeconds*1000000);	
		}	
		exit;	
	}
	private function createResponse() {
		$response=new StdClass();
		return $response;
	}
	function filter_projects_continuous() {
	  $delayBetweenPostsSeconds=0.5;		  
	  $response=$this->createResponse();
	  //read posts count
	  //$this->filter_count_results(true);	    

	  //tohle natahuje data pro ajax jeden post po jednom, vraci json
	  $ajaxposts = new \WP_Query($this->buildQuery());
	  //todo seskupovani podle typu/modelu, pokud bude vic vysledku	  
	  //ukazani kolik je vysledku kazde option	  
	  if($ajaxposts->have_posts()) {		
		$this->logWrite("posts found");
		while($ajaxposts->have_posts()) {
		  $ajaxposts->the_post();	  
		  $response->title=get_the_title()."id:".get_the_id();
		  $response->content=get_the_title();
		  $response->url=get_the_permalink();	
		  $response->meta="transmission:".get_post_meta(get_the_id(),"mauta_automat",true)."".get_post_meta(get_the_id(),"hp_vendor",true);	
		
		  echo json_encode($response).PHP_EOL;
		  flush();
		  ob_flush();
		 
		  
		  /*
		  wp_send_json($ajaxPost);
		  flush();
		  ob_flush();
		  */
		  session_write_close();
		  usleep($delayBetweenPostsSeconds*1000000);
		}
		exit;
	  } else {	
		$response->title="majaxnone";
		$response->content="no results";
		$this->logWrite("no response");
		echo json_encode($response);
		flush();
		ob_flush();
	    exit;
	  }
	}
	function sendBlankResponse() {
		$response=$this->createResponse();
		$response->title="neco2342";	
		$response->content="neco345643";
		echo json_encode($response);
		flush();
		ob_flush();		  
		exit;
	}
	function filter_count_results($doExit=false) {
		//send blank - debug
		$response=$this->createResponse();
		$this->sendBlankResponse();
		//<-
		global $wpdb;
		$delayBetweenPostsSeconds=0.5;	
		$n=0;
		$filter="";

		$response->title="postcounts";	
		$response->content="postcounts";	

		foreach ($this->fields->getList() as $field) {
			if ($n>0) $filter .= " OR ";
			$filter .= "meta_key = '{$field->outName()}'";			 			
			$n++;
		}		
		$query="SELECT meta_key,meta_value,post_title, COUNT(post_id) AS count FROM `wp_postmeta`,`wp_posts` 
		WHERE ($filter) 
		AND post_id=id AND post_status like 'publish' 
		GROUP BY meta_value ORDER BY meta_value ASC";
		$this->logWrite("countposts {$query}");

		//ukazani kolik je vysledku kazde option	
		/*
		foreach( $wpdb->get_results($query) as $key => $row) {			
			$response->content=$row->meta_key;	
		  }
		*/
		$response->postcounts=$wpdb->get_results($query); 
		//$response->content=$wpdb->get_results($query); 
		
		 
		  echo json_encode($response);
		  flush();
		  ob_flush();		  
		  if (!$doExit) exit;	//load counts only
	}

	function logWrite($val) {
	 file_put_contents(plugin_dir_path( __FILE__ ) . "log.txt",date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
	}
}