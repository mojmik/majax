<?php
   /*
   Plugin Name: Majax plugin
   Plugin URI: http://ttj.cz
   description: >-
  majax plugin
   Version: 1.2
   Author: Mik
   Author URI: http://ttj.cz
   License: GPL2
   */




$majax=new Majax();
   
Class Majax {
	function __construct() {
		register_activation_hook( __FILE__, [$this,'majax_plugin_install'] );
		
		$pluginRender=new MajaxRender();
		
		//init actions
		add_action( 'wp_enqueue_scripts', [$this,'mAjaxEnqueue'] );		
		add_action('wp_ajax_filter_projects', [$pluginRender,'filter_projects_continuous'] );
		add_action('wp_ajax_nopriv_filter_projects', [$pluginRender,'filter_projects_continuous'] );

		//add shortcode
		add_shortcode('majaxfilter', [$pluginRender,'majax_print_filter'] );
		add_shortcode('majaxcontent', [$pluginRender,'majax_print_content'] );

		//add style
		/* this would enque style too soon- before theme style.css
		wp_register_style( 'majax', plugin_dir_url( __FILE__ ) . 'majax.css' );
		wp_enqueue_style('majax');
		*/
		add_action( 'wp_enqueue_scripts', [$this,'majaxEnqueueStyle'], 11);
		
		//add select2
		wp_register_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css' );
		wp_enqueue_style('select2');
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js',array('jquery'),'',true );
		
		//add slider
		
		wp_enqueue_script( 'jquery-ui-slider',array('jquery'),'',true );
		
		$wp_scripts = wp_scripts();
 
		wp_enqueue_style('plugin_name-admin-ui-css',
						'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css',
						false,
						false,
						false);
		
	}
	function majaxEnqueueStyle() {
		wp_register_style( 'majax', plugin_dir_url( __FILE__ ) . 'majax.css' );
		wp_enqueue_style('majax');
	}

	function mAjaxEnqueue() {
		//vlozi js
		wp_enqueue_script( 'ajax-script', plugin_dir_url( __FILE__ ) . 'majax.js', array('jquery') );
		wp_localize_script( 'ajax-script', 'majax',
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
	
	function majax_plugin_install() {
		global $wpdb;			
		$table_name = $wpdb->prefix . "majax_fields"; 	
		$charset_collate = $wpdb->get_charset_collate();

		$query = "DROP TABLE `$table_name`";   	
		$result = mysqli_query($wpdb->dbh,$query);
		
		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,	
		  name tinytext,
		  value text,
		  title text,
		  type tinytext,
		  compare tinytext,
		  valMin text,
		  valMax text,
		  postType tinytext,
		  sortorder smallint,
		  PRIMARY KEY  (id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
	
	
	


Class MajaxRender {	
	function __construct() {
		include_once "custom-field.php";
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
	  //tohle natahuje data pro ajax jeden post po jednom, vraci json
	  	    
	  $ajaxposts = new WP_Query($this->buildQuery());
		  
	  if($ajaxposts->have_posts()) {
		$this->logWrite("posts found");
		while($ajaxposts->have_posts()) {
		  $ajaxposts->the_post();	  
		  $ajaxPost->title=get_the_title();
		  $ajaxPost->content=get_the_title();
		  $ajaxPost->url=get_the_permalink();	
		  $ajaxPost->meta=get_post_meta(get_the_id(),"hp_vendor",true);	
		  echo json_encode($ajaxPost);
		  flush();
		  ob_flush();
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
	function logWrite($val) {
	 file_put_contents(plugin_dir_path( __FILE__ ) . "log.txt",date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
	}
}
?>