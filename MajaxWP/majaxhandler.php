<?php
namespace MajaxWP;

Class MajaxHandler {	
	function __construct() {		
			//init custom fields
			$this->fields=new CustomFields();
			
			//kdyz uz mam nacteny pole
			$forceReload=false;
			$loadValues=false;
			if (!$this->fields->loadFromSQL() && $forceReload) {
				//manual setup
				//kdyz jeste ne		
				$this->fields->addField(new CustomField("hp_price",10,"NUMERIC","Price",">"));
				$this->fields->addField(new CustomField("hp_vendor","Milanoo","","Vendor","="));		
				$this->fields->addField(new CustomField("hp_brand","Milanoo","","Brand","="));	
				$this->fields->addField(new CustomField("hp_murl","Milanoo","","URL","="));		
				if ($loadValues) echo $this->fields->readValues();
				else $this->fields->saveToSQL();
			}				
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
	  $mType = $_POST['type'];	
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

	function filter_projects_continuous() {
	  $delayBetweenPostsSeconds=0.5;	
	  
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
		  $ajaxPost->title=get_the_title()."id:".get_the_id();
		  $ajaxPost->content=get_the_title();
		  $ajaxPost->url=get_the_permalink();	
		  $ajaxPost->meta="transmission:".get_post_meta(get_the_id(),"mauta_automat",true)."".get_post_meta(get_the_id(),"hp_vendor",true);	
		  echo json_encode($ajaxPost).PHP_EOL;
		  flush();
		  ob_flush();
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
		$response->title="neco2342";	
		$response->content="neco345643";
		echo json_encode($response);
		flush();
		ob_flush();		  
		exit;
	}
	function filter_count_results($doExit=false) {
		//send blank - debug
		$this->sendBlankResponse();
		//<-
		global $wpdb;
		$delayBetweenPostsSeconds=0.5;	
		$n=0;

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