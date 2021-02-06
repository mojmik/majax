<?php
namespace MajaxWP;

define('SHORTINIT', true);
define('DOING_AJAX', true);

//IMPORTANT: Change with the correct path to wp-load.php in your installation
require_once( '../../../wp-load.php' );

// l10n
// ———————————————————————–
require_once( ABSPATH . WPINC . '/l10n.php' );
//require_once( ABSPATH . WPINC . '/class-wp-locale.php' );
//require_once( ABSPATH . WPINC . '/class-wp-locale-switcher.php' );

require_once( ABSPATH . WPINC . '/formatting.php' );
require_once( ABSPATH . WPINC . '/meta.php' );
require_once( ABSPATH . WPINC . '/pluggable.php' );


// User
// ———————————————————————–
require_once( ABSPATH . WPINC . '/user.php' );
require_once( ABSPATH . WPINC . '/capabilities.php' );
require_once( ABSPATH . WPINC . '/class-wp-user.php' );
require_once( ABSPATH . WPINC . '/class-wp-user-query.php' );
require_once( ABSPATH . WPINC . '/class-wp-roles.php' );
require_once( ABSPATH . WPINC . '/class-wp-role.php' );


// Posts
// ———————————————————————–
require_once( ABSPATH . WPINC . '/class-wp-query.php' );
require_once( ABSPATH . WPINC . '/class-wp-rewrite.php' );
require_once( ABSPATH . WPINC . '/class-wp-tax-query.php' );

require_once( ABSPATH . WPINC . '/class-wp-post-type.php' );
require_once( ABSPATH . WPINC . '/class-wp-post.php' );
require_once( ABSPATH . WPINC . '/link-template.php' );
require_once( ABSPATH . WPINC . '/author-template.php' );
require_once( ABSPATH . WPINC . '/post.php' );
require_once( ABSPATH . WPINC . '/taxonomy.php' );
require_once( ABSPATH . WPINC . '/post-template.php' );

//pokus
require_once( ABSPATH . WPINC . '/class-wp-ajax-response.php' );
require_once( ABSPATH . WPINC . '/query.php' );
require_once( ABSPATH . WPINC . '/comment.php' );
require_once( ABSPATH . WPINC . '/class-wp-comment.php' );

$GLOBALS['wp_the_query'] = new \WP_Query();
$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];

require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/majaxrender.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/customfields.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/customfield.php');
$renderer = new MajaxRender();
//check_ajax_referer(MajaxHandler::NONCE,'security');
$action=$_POST["action"];
if ($action=="count") $renderer->filter_count_results();
else $renderer->filter_projects_continuous();
