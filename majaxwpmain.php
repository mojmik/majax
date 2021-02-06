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


  namespace MajaxWP;


define('MAJAX_PLUGIN_PREFIX','majax-');
define( 'MAJAX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAJAX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('MAJAX_SHORT',true);

require_once MAJAX_PLUGIN_PATH . '/MajaxWP/majax.php';
$majax=new Majax();
$majax->initWP();


/*
add_shortcode('majaxshort', 'MajaxWP\majaxshortfce');
function majaxshortfce() {    
    //add_action( 'wp_enqueue_scripts', 'MajaxWP\cyb_hits_enqueue_scripts' );    
    cyb_hits_enqueue_scripts();
}

function cyb_hits_enqueue_scripts() {
    echo "!!!Hi";
    wp_register_script('cyb-hits', plugins_url( 'ajaxshort.js', __FILE__ ), array( 'jquery' ) );

    wp_enqueue_script('jquery');
    wp_enqueue_script('cyb-hits');

    $theID = 0;
    if(is_single()) {
        $theID = get_the_ID();
    }

    $scriptData = array(
                  'ajax_url' => plugins_url( '/ajaxshort.php', __FILE__ ),
                  'postID'  => $theID
                );
    wp_localize_script('cyb-hits','cyb_hits_data',$scriptData);
}
*/