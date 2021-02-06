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

require_once MAJAX_PLUGIN_PATH . '/MajaxWP/majax.php';

$majax=new Majax();
$majax->initWP();

