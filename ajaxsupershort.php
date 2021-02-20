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

class Db {	
	private static $dbconn;
	private static $dbsettings = array(
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		\PDO::ATTR_EMULATE_PREPARES => false,
	);
	public static function connect($host, $user, $pwd, $database) {
		if (!isset(self::$dbconn))
		{
			self::$dbconn = @new \PDO(
				"mysql:host=$host;dbname=$database",
				$user,
				$pwd,
				self::$dbsettings
			);
		}
	}
	public static function getRows($query, $params = array())	{
		$out = self::$dbconn->prepare($query);
		$out->execute($params);
		return $out->fetchAll(\PDO::FETCH_ASSOC);
	}
	public static function getRow($query, $params = array())	{
		$out = self::$dbconn->prepare($query);
		$out->execute($params);
		return $out->fetch();
	}	
}

require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/customfields.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/customfield.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/majaxrender.php');
require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/majaxitem.php');

$renderer = new MajaxRender(false);
Db::connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

$action=$_POST["action"];
if ($action=="filter_rows") {
	/*
	$query=$renderer->buildQueryCount();
    $rows=Db::getRows($query);
    $renderer->showRows($rows,0,"majaxcounts");
	*/

    $query=$renderer->buildQuerySQL();
	$rows=Db::getRows($query);
	$countsRows=$renderer->buildCounts($rows);	
	$renderer->showRows($countsRows,0,"majaxcounts",0);
	$renderer->showRows($rows);		
	exit;
}
if ($action=="filter_count_results") {
    $query=$renderer->buildQueryCount();
    $rows=Db::getRows($query);
    $renderer->showRows($rows,0,"majaxcounts");
}


