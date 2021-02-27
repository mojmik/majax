<?php
/*
 this feeds ajax from wordpress with minimal loading
*/
namespace MajaxWP;


header('Content-Type: text/html');
header( 'X-Content-Type-Options: nosniff' );
header('Cache-Control: no-cache');
header('Pragma: no-cache');

define('SHORTINIT', true);
define('DOING_AJAX', true);


require_once( '../../../wp-config.php' );



require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/customfields.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/customfield.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/majaxrender.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/majaxitem.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/caching.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/mikdb.php');


$action=$_POST["action"];
if ($action=="filter_rows") {
	$renderer = new MajaxRender(); //use false pro preloading hardcoded fields (save one sql query)
	MikDb::connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);	
	/*
	$query=$renderer->buildQueryCount();
    $rows=Db::getRows($query);
    $renderer->showRows($rows,0,"majaxcounts");
	*/

    $query=$renderer->buildQuerySQL();
	$rows=Caching::getCachedRows($query);
	$countsJson=Caching::getCachedJson("json_$query");
	$countsRows=$renderer->buildCounts($rows,$countsJson);	
	if (!$countJson) {
		Caching::addCache("json_$query",$countsRows);
	}
	$renderer->showRows($countsRows,0,"majaxcounts",0);
	$renderer->showRows($rows,0,"",10);		
	exit;
}


