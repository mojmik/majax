<?php
namespace MajaxWP;

use stdClass;

Class ImageCache {
    public static $imageCache;    
    public static function loadImageCache() {
        ImageCache::$imageCache=array();
        ImageCache::$imageCache=Caching::getCachedJson("allimages");
        if (!ImageCache::$imageCache) {     
            ImageCache::$imageCache=ImageCache::loadFromSQL();
            Caching::addCache("allimages",ImageCache::$imageCache);                         
        }
    }

    public static function getImageUrlFromId($id) {
        $url="";
        foreach (ImageCache::$imageCache as $image) {
            if ($image["thumbId"]==$id) return $image["thumbPath"];
        }		
		return $url;
    }
    public static function loadFromSQL() {   
        global $wpdb;  
        $load=false;   
        $rows=array();        
        $query = "
        SELECT wpm.meta_value as thumbId,wpm2.meta_value as thumbPath 
        FROM `".$wpdb->prefix."postmeta` wpm 
        INNER JOIN ".$wpdb->prefix."postmeta wpm2
            ON (wpm.meta_value=wpm2.post_id AND wpm2.meta_key = '_wp_attached_file' AND wpm.meta_key = '_thumbnail_id')";    
        foreach( $wpdb->get_results($query) as $key => $row) {	
            $rows[] = ["thumbId" => $row->thumbId, "thumbPath" => "/wp-content/uploads/".$row->thumbPath];
            $load=true;
        }	
        return $rows;                
      }
}