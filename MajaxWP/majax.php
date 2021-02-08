<?php
namespace MajaxWP;
   
Class Majax {
	public $thisPluginName="majax";
	private $ajaxRender;
	private $ajaxHandler;
	function __construct() {
		spl_autoload_register([$this,"mLoadClass"]);
		if (MAJAX_FAST > 1) $this->ajaxHandler=new MajaxHandlerShort(); //shortinit lightweight version
		else $this->ajaxHandler=new MajaxHandler(); //ajax-admin version
		$this->ajaxHandler->register();						
		
	}
	
	function mLoadClass($class) {	
		if (strpos($class,"MajaxWP")!==0) return;
		$path=MAJAX_PLUGIN_PATH.str_replace("\\","/",strtolower("$class.php"));		
        require($path);
	}
	
	function initWP() {
		register_activation_hook( __FILE__, [$this,'majax_plugin_install'] );
		//init actions		
	
		add_action( 'wp_enqueue_scripts', [$this,'mAjaxEnqueueScripts'] );			
		add_action( 'wp_enqueue_scripts', [$this,'majaxEnqueueStyle'], 11);

		
	}

	function majaxEnqueueStyle() {		
		$wp_scripts = wp_scripts();	
		$mStyles=[
			 'majax' => ['src' => MAJAX_PLUGIN_URL . 'majax.css'],
			 'select2' => ['src' => MAJAX_PLUGIN_URL .'select2.min.css', 'srcCdn'=>'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css'],
			 'admin-ui' => [ 'src' => MAJAX_PLUGIN_URL . "jquery-ui.min.css",
				 			'srcCdn' => 'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/redmond/jquery-ui.css']
		];
		
		foreach ($mStyles as $key => $value) {
			$src = (isset($value["src"])) ? $value["src"] : $value["srcCdn"];
			$key = MAJAX_PLUGIN_PREFIX . $key;
			wp_register_style($key, $src);
			wp_enqueue_style($key);
		}
	}

	function mAjaxEnqueueScripts() {	
		$mScripts=[			
			'select2' => [ 'src' => MAJAX_PLUGIN_URL .'select2.min.js',
						   'srcCdn' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js',
						   'depends' => array('jquery'),
						   'inFooter' => true

			],
			'jquery-ui-slider' => ['src' => array('jquery'),
								   'inFotter' => true
			]
		];
		
		
		foreach ($mScripts as $key => $value) {
			$src = (isset($value["src"])) ? $value["src"] : $value["srcCdn"];
			$version= (isset($value["version"])) ? $value["version"] : '';
			$inFooter= (isset($value["inFooter"])) ? $value["inFooter"] : false;
			wp_enqueue_script($key,$src,$value["depends"],$version,$inFooter);
			if (isset($value["localizeObj"])) {
				wp_localize_script( $key, $value["localizeObj"],$value["localizeArray"]);		
			}
		}
		
	}
	
	function majax_plugin_install() {
		global $wpdb;			
		$table_name = $wpdb->prefix . "majax_fields"; 	
		$charset_collate = $wpdb->get_charset_collate();

		$query = "DROP TABLE `$table_name`";   	
		mysqli_query($wpdb->dbh,$query);
		
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
		  filterorder smallint,
		  PRIMARY KEY  (id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
	
