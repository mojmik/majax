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

		$this->ajaxRender=new MajaxRender();
		
		$this->ajaxRender->regShortCodes();				

        add_action('wp_loaded', [$this, 'register_script']);
	}
	
	public function register_script()    {	      		
		wp_register_script('majax-script', MAJAX_PLUGIN_URL . 'ajaxshort.js', array( 'jquery' ) );		
		wp_localize_script('majax-script', 'majax', $this->get_ajax_data());
		wp_enqueue_script('majax-script');
	}
	
	private function get_ajax_data() {
        return array(
			'ajax_url' =>  MAJAX_PLUGIN_URL . 'ajaxshort.php',
            'action' => self::ACTION,
            'nonce' => wp_create_nonce(MajaxHandlerShort::NONCE)
        );
	}
	
	function logWrite($val) {
	 file_put_contents(plugin_dir_path( __FILE__ ) . "log.txt",date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
	}
}