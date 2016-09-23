<?php
class bro_images {
	static public function init() {
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'bro_images';
	
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$royal_sliders_sql = "CREATE TABLE " . $table_name ." (
						  id mediumint(9) NOT NULL AUTO_INCREMENT,
						  native_path text NOT NULL COLLATE utf8_general_ci,
						  native_url text NOT NULL COLLATE utf8_general_ci,
						  path text NOT NULL COLLATE utf8_general_ci,
						  url text NOT NULL COLLATE utf8_general_ci,
						  width mediumint(9) NOT NULL,
						  height mediumint(9) NOT NULL,
						  PRIMARY KEY (id)
						);";	
	
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($royal_sliders_sql);			
		}
	}
	
	
	static public function get_image($url, $width=false, $height=false, $path=false) {
		global $wpdb;
		$query = 'SELECT * FROM ' . $wpdb->prefix . 'bro_images WHERE native_url=\''.$url.'\'';
		$query .= ' AND width='.($width ? $width : 0);
		$query .= ' AND height='.($height ? $height : 0);
		$rows = $wpdb->get_results($query);
		
		if(count($rows) != 0) {
			if($path)
				return $rows[0]->path;
			else
				return $rows[0]->url;
		}
		else {
			return bro_images::create_image($url, $width, $height, $path);
		}
	}
	
	static public function create_image($url, $w = false, $h = false, $path = false) {
		$dir = dirname( __FILE__ );
		
		require_once($dir.'/image_functions.php');
		require_once($dir.'/url_to_filepath.php');
		
		$dest_path=usquare_functions::get_current_upload_dir();
		$suffix="_resized";

		$file = usquare_functions::get_filepath_from_url_smart($url);

		$img = all_around_image_class::create_object($file);
		if ($img && !$img->is_error()) {
			if($w && $h)	
				$img->resize($w,$h, true);
			$file2 = $img->save($suffix, $dest_path);
			$url2 = usquare_functions::get_full_urlpath_from_full_filepath($file2);
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'bro_images';
			$wpdb->insert(
				$table_name,
				array(
					'native_path'=>$file,
					'native_url'=>$url,
					'path'=>$file2,
					'url'=>$url2,
					'width'=>($w ? $w : 0),
					'height'=>($h ? $h : 0)),	
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%d')						
				
			);
		}
		unset($img);
		if($path) 
			return $file2;
		else 
			return $url2;
	}
}