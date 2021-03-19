<?php
namespace MajaxWP;

Class MajaxHandlerShort {	
	const ACTION = 'majax';
	const NONCE =  'majax-ajax';

	public $ajaxRender;
	public $shortInit=true;

	function __construct() {		
				
	}

	public function register()  {				
		add_shortcode('majaxfilter', [$this,'printFilters'] );
		add_shortcode('majaxcontent', [$this,'printContent'] );
		add_shortcode('majaxstaticcontent', [$this,'showStaticContent'] );
		add_shortcode('majaxstaticform', [$this,'showStaticForm'] );
		add_action('wp_loaded', [$this, 'register_script']);
	}
	
	function initRender($atts) {		
		$this->ajaxRender=new MajaxRender(false,$atts);		
	}
	function setAtts($atts = []) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );		
		return $atts;	
	}

	function printFilters($atts = []) {			
		ob_start();					
		$this->initRender($this->setAtts($atts));
		$this->ajaxRender->printFilters();
		return ob_get_clean();
	}
	function printContent($atts = []) {	
		ob_start();	
		$this->initRender($this->setAtts($atts));
		$this->ajaxRender->printContent();
		return ob_get_clean();
	}
	function showStaticContent($atts = []) {	
		ob_start();	
		$this->initRender($this->setAtts($atts));
		$this->ajaxRender->showStaticContent();
		return ob_get_clean();
	}
	function showStaticForm($atts = []) {	
		ob_start();	
		$this->initRender($this->setAtts($atts));
		$this->ajaxRender->showStaticForm();
		return ob_get_clean();
	}
	
	public function register_script()    {	      		
		//recaptcha
		wp_register_script('majaxrecaptcha','https://www.google.com/recaptcha/api.js?render=6LdLk7EUAAAAAEWHuB2tabMmlxQ2-RRTLPHEGe9Y');	

		wp_register_script('majaxelements', MAJAX_PLUGIN_URL . 'majaxelements.js', array( 'jquery' ) );	
		wp_enqueue_script('majaxelements');
		wp_register_script('majaxviewcomponents', MAJAX_PLUGIN_URL . 'majaxviewcomponents.js', array( 'jquery' ) );	
		wp_enqueue_script('majaxviewcomponents');
		wp_register_script('majaxview', MAJAX_PLUGIN_URL . 'majaxview.js', array( 'jquery' ) );	
		wp_enqueue_script('majaxview');
		wp_register_script('majaxprc', MAJAX_PLUGIN_URL . 'majaxprc.js', array( 'jquery' ) );	
		wp_enqueue_script('majaxprc');				
		wp_register_script('majax-script', MAJAX_PLUGIN_URL . 'majax.js', array( 'jquery' ) );		
		wp_localize_script('majax-script', 'majax', $this->get_ajax_data());
		wp_enqueue_script('majax-script');		
		
	}
	
	private function get_ajax_data() {
		if (MAJAX_FAST==4) $ajaxPhp="ajaxsupershort.php";
		if (MAJAX_FAST==3) $ajaxPhp="ajaxshort.php";
		if (MAJAX_FAST==2) $ajaxPhp="ajaxnotsoshort.php";		
				
        return array(
			'ajax_url' =>  MAJAX_PLUGIN_URL . $ajaxPhp,
            'action' => self::ACTION,
            'nonce' => wp_create_nonce(MajaxHandlerShort::NONCE)
		);		
	}
	
	public static function logWrite($val) {
	 file_put_contents(plugin_dir_path( __FILE__ ) . "log.txt",date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
	}
}