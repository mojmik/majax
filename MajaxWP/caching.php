<?php
namespace MajaxWP;

use stdClass;

Class Caching {	    
    public static $cacheMap = array();
    //private static $cachePath=plugin_dir_path( __FILE__ ) . "cache/";
    private static $cachePath;    
    private static $compressJson=0;    
  
    static function getCachePath() {
        if (!Caching::$cachePath) Caching::$cachePath=plugin_dir_path( __FILE__ ) ."cache/";
        return Caching::$cachePath;
    }
    static function pruneCache() {
        $files = glob(Caching::getCachePath() . "*");
        $now   = time();
      
        foreach ($files as $file) {
          if (is_file($file)) {
            if ($now - filemtime($file) >= 60 * 60 * 24 * 1) { // 1 days
              unlink($file);
            }
          }
        }
        Caching::logWrite("cache pruned");
    }

    static function cacheWrite($name,$rows) {
        if (Caching::$compressJson) 
         file_put_contents(Caching::getCachePath() . "$name.json",gzcompress(json_encode($rows)));
        else 
         file_put_contents(Caching::getCachePath() . "$name.json",json_encode($rows));
    }
    static function cacheRead($name) {
       if (Caching::$compressJson) 
        $rows=json_decode(gzuncompress(file_get_contents(Caching::getCachePath() . "$name.json")),1);
       else 
        $rows=json_decode(file_get_contents(Caching::getCachePath() . "$name.json"),1);
       return $rows;
    }
    static function getCachedFn($query) {
        if (empty(Caching::$cacheMap)) {
            Caching::loadCacheMap();
        }         
        foreach (Caching::$cacheMap as $row) {
            if ($row["query"] == $query) return $row["fnId"];
        }
        return false;
    }
    static function getCachedRows($query) {
       $fnName=Caching::getCachedFn($query);
       if ($fnName == false) {
        $rows=MikDb::getRows($query);
        Caching::addCache($query,$rows);
        Caching::logWrite("$query added to cache");
        return $rows;
       }
       Caching::logWrite("$query loaded from cache");
       return Caching::cacheRead($fnName);
    }
    static function getCachedJson($query) {
        $fnName=Caching::getCachedFn($query);  
        if ($fnName == false) {
         Caching::logWrite("-$query json not exist in cache-");
         return false;
        }
        Caching::logWrite("$query json loaded from cache");        
        return Caching::cacheRead($fnName);
    }
    static function addCache($query,$rows) {
        $fnId=date("d-m-y-h-i-s").rand(10000,99999).".txt";
        $cacheMap[] = ["query" => $query, "fnId" => $fnId];
        file_put_contents(Caching::getCachePath() . "cachemap.txt",$query."|".$fnId."^",FILE_APPEND | LOCK_EX);
        Caching::cacheWrite($fnId,$rows);
    } 
    static function loadCacheMap() {
        Caching::$cacheMap=array();
        $txt=file_get_contents(Caching::getCachePath() . "cachemap.txt");
        $rows=explode("^",$txt);
        foreach ($rows as $row) {
            $ex=explode("|",$row);
            Caching::$cacheMap[]=["query" => $ex[0], "fnId" => $ex[1]];
        }
    }
    static function logWrite($val,$fn="caching.txt") {
        file_put_contents(Caching::getCachePath() . $fn,date("d-m-Y h:i:s")." ".$val."\n",FILE_APPEND | LOCK_EX);
    }
}