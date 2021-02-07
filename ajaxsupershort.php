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
		return $out->fetchAll();
	}
	public static function getRow($query, $params = array())	{
		$out = self::$dbconn->prepare($query);
		$out->execute($params);
		return $out->fetch();
	}	
}

require_once(plugin_dir_path( __FILE__ ) . '/MajaxWP/majaxrender.php');

Db::connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
$rows=Db::getRows("SELECT * FROM wp_posts WHERE post_type like 'mauta' LIMIT 10");

$renderer = new MajaxRender(false);
$renderer->showRows($rows);
/*
check_ajax_referer(MajaxHandlerShort::NONCE,'security');
$action=$_POST["action"];
if ($action=="count") $renderer->filter_count_results();
else $renderer->filter_projects_continuous();
*/

