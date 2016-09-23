<?php

class usquare_functions {

	static public function get_filename_from_filepath($file) {
		$file_info=pathinfo($file);
		return $file_info['dirname'];
	}
	static public function get_directory_from_filepath($file) {
		$file_info=pathinfo($file);
		return $file_info['basename'];
	}
	static public function get_filename_from_url($url) {
		$pos=strrpos($url, "/");
		if ($pos!==FALSE) return substr($url, $pos+1);
		return $url;
	}
	static public function get_root_of_uploads_dir($with_slash=0) {	// return full filepath without / on end
		$upload_dir = wp_upload_dir();
		$path="";
		if (isset($upload_dir['basedir'])) $path = $upload_dir['basedir'];
		if ($path=="" && defined('ABSPATH'))
		{
			$slash="/";
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
			$path=ABSPATH."wp-content".$slash."uploads";
		}
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $path=str_replace("/", "\\", $path);
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		if ($with_slash) $path.=$slash;
		return $path;
	}
	static public function get_current_upload_dir($with_slash=0) {	// return full filepath without / on end
		$path=self::get_root_of_uploads_dir();
		$upload_dir = wp_upload_dir();
		if (isset($upload_dir['path'])) $path = $upload_dir['path'];
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $path=str_replace("/", "\\", $path);
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		if ($with_slash) $path.=$slash;
		return $path;
	}
	static public function get_full_urlpath_of_uploads_dir($with_slash=0) {
		$upload_dir = wp_upload_dir();
		$url=$upload_dir['baseurl'];
		if ($with_slash) $url.='/';
		return $url;	
	}
	static public function get_relative_urlpath_for_wordpress_folder() {	// return relative to webroot URL
		$wp_url=get_site_url()."/";
		if (substr($wp_url,0,7)=="http://") $wp_url=substr($wp_url,7);
		if (substr($wp_url,0,8)=="https://") $wp_url=substr($wp_url,8);
		$pos=strpos($wp_url, "/");
		$folder=substr($wp_url, $pos+1);
		if ($folder=='') $folder='/';
		return $folder;
	}
	static public function get_full_urlpath_for_wordpress_folder() {	// return relative URL
		return get_site_url()."/";
	}
	static public function get_full_urlpath_of_domain($with_slash=1) {	// return full URL with /
		$wp_url=get_site_url()."/";
		$pos=strpos($wp_url, "/", 8);
		return substr($wp_url, 0, $pos+$with_slash);
	}
	static public function get_webroot_filepath() {	// return full folderpath, ended with /
		$wp_path=ABSPATH;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("\\", "/", $wp_path);
		$wp_path_length=strlen($wp_path);
		$wordpress_folder=self::get_relative_urlpath_for_wordpress_folder();
		if ($wordpress_folder!='/') {
			$wordpress_folder_length=strlen($wordpress_folder);
			$ret = substr($wp_path, 0, $wp_path_length-$wordpress_folder_length);
		} else $ret = $wp_path;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $ret=str_replace("/", "\\", $ret);
		return $ret;
	}
	
	static public function get_relative_to_wordpress_urlpath_from_full_urlpath($url) {	// return relative URL without /
		if (substr($url,0,4)!='http') {
			if (substr($url,0,1)!='/') $url="/".$url;
			return $url;
		}
		$wp_path=ABSPATH;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("/", "\\", $wp_path);
		$wp_url=get_site_url()."/";
		$wp_url_length=strlen($wp_url);
		if (substr($url, 0, $wp_url_length)==$wp_url) {
			$piece=substr($url, $wp_url_length);
			return $piece;
		}
		return '';
	}
	static public function get_relative_to_webroot_urlpath_from_full_urlpath($url) {	// return relative URL with /
		$pos=strpos($url, '/', 8);
		return substr($url, $pos);
	}
	static public function get_full_urlpath_from_relative_urlpath($url) {	// return full URL
		if (self::is_http_link($url)) return $url;
		if (self::link_begin_with_slash($url)) {
			return self::get_full_urlpath_of_domain(0).$url;
		} else {
			return self::get_full_urlpath_for_wordpress_folder().$url;
		}
	}
	static public function get_relative_to_wordpress_urlpath_from_full_filepath($file) {	// return relative URL without /
		$wp_path=ABSPATH;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("/", "\\", $wp_path);
		$wp_path_length=strlen($wp_path);
		if (substr($file, 0, $wp_path_length)==$wp_path) {
			$piece=substr($file, $wp_path_length);
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("\\", "/", $piece);
			return $piece;
		}
		return '';
	}
	static public function get_relative_to_webroot_urlpath_from_full_filepath($file) {	// return relative URL with	/
		$webroot_folderpath=self::get_webroot_filepath();
		//echo $webroot_folderpath; exit;
		$webroot_folderpath_length=strlen($webroot_folderpath);
		$ret = '/'.substr($file, $webroot_folderpath_length);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $ret=str_replace("\\", "/", $ret);
		return $ret;
	}
	static public function get_full_urlpath_from_full_filepath($file) {	// return full URL
		$wp_path=ABSPATH;
		$wp_url=get_site_url()."/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("/", "\\", $wp_path);
		$wp_path_length=strlen($wp_path);
		if (substr($file, 0, $wp_path_length)==$wp_path) {
			$piece=substr($file, $wp_path_length);
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("\\", "/", $piece);
			return $wp_url.$piece;
		}
		return '';
	}
	static public function get_full_urlpath_from_relative_to_wordpress_filepath($file) {	// return full URL
		$wp_url=get_site_url();
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $file=str_replace("\\", "/", $file);
		if (substr($file,0,1)!='/') $file="/".$file;
		return $wp_url.$file;
	}
	static public function get_full_urlpath_from_relative_to_webroot_filepath($file) {	// return full URL
		$wp_url=get_site_url()."/";
		$wordpress=self::get_relative_urlpath_for_wordpress_folder();
		$wordpress_length=strlen($wordpress);
		$wp_url_length=strlen($wp_url);
		$piece=substr($wp_url, $wp_url_length-$wordpress_length);
		if ($piece==$wordpress) {
			$n=0;
			if ($wordpress=='/') $n=1;
			$root=substr($wp_url, 0, $wp_url_length-$wordpress_length+$n);
			//echo 'root='.$root;exit;
			if (substr($file,0,1)=='/' || substr($file,0,1)=='\\') $file=substr($file,1);
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $file=str_replace("\\", "/", $file);
			return $root.$file;
		}
		return '';
	}
	static public function get_full_filepath_from_relative_filepath($file) {	// return full filepath
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		$wp_path=ABSPATH;
		$wp_path_length=strlen($wp_path);
		if (substr($wp_path, $wp_path_length-1, 1)=='/') $wp_path=substr($wp_path, 0, $wp_path_length-1);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') if (substr($wp_path, $wp_path_length-1, 1)=='\\') $wp_path=substr($wp_path, 0, $wp_path_length-1);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $file=str_replace("/", "\\", $file);
		if (substr($file,0,1)!=$slash) $file=$slash.$file;
		return $wp_path.$file;
	}
	static public function get_relative_filepath_from_full_filepath($file) {	// return relative filepath
		$wp_path=ABSPATH;
		$wp_path_length=strlen($wp_path);
		if (substr($wp_path, $wp_path_length-1, 1)=='/') $wp_path=substr($wp_path, 0, $wp_path_length-1);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $file=str_replace("/", "\\", $file);
		$wp_path_length=strlen($wp_path);
		if (substr($file, 0, $wp_path_length)==$wp_path) {
			$piece=substr($file,$wp_path_length);
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("/", "\\", $piece);
			if (substr($piece,0,1)=='/' || substr($piece,0,1)=='\\') return substr($piece, 1);
			return $piece;
		}
		return '';	
	}
	static public function get_full_filepath_from_full_urlpath($url) {	// return full filepath
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		$wp_path=ABSPATH;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("/", "\\", $wp_path);
		$wp_url=get_site_url()."/";
		$wp_url_length=strlen($wp_url);
		if (substr($url, 0, $wp_url_length)==$wp_url) {
			$piece=substr($url, $wp_url_length);
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("/", "\\", $piece);
			$file=$wp_path.$piece;
			if (is_file($file)) return $file;
			//echo $piece."<br />"; //exit;
		}
		return '';
	}
	static public function get_full_filepath_from_relative_to_wordpres_urlpath ($url) {	// return full filepath
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		$wp_path=ABSPATH;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("/", "\\", $wp_path);

		if (substr($url,0,1)=='/') $url=substr($url, 1);
		$piece=$url;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("/", "\\", $piece);
		//echo "PIECE: ".$piece."<br />"; exit;
		$file=$wp_path.$piece;
		if (is_file($file)) return $file;
		return '';
	}
	static public function get_full_filepath_from_relative_to_webroot_urlpath ($url) {// return full filepath
		if (substr($url,0,1)=='/') $url=substr($url, 1);
		$domain=self::get_full_urlpath_of_domain();
		$url=$domain.$url;
		return self::get_full_filepath_from_full_urlpath($url);
	}

/*
	static public function get_filepath_from_url_last_hope($url) {
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		$wp_path=ABSPATH;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $wp_path=str_replace("/", "\\", $wp_path);
		$wp_url=get_site_url()."/";
		$wp_url_length=strlen($wp_url);
		if (substr($url, 0, $wp_url_length)==$wp_url) {
			$piece=substr($url, $wp_url_length);
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("/", "\\", $piece);
			$file=$wp_path.$piece;
			if (is_file($file)) return $file;
			//echo $piece."<br />"; //exit;
		}
		if (substr($url,0,1)=='/') $url=substr($url, 1);
		$piece=$url;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $piece=str_replace("/", "\\", $piece);
		//echo "PIECE: ".$piece."<br />"; exit;
		$file=$wp_path.$piece;
		if (is_file($file)) return $file;
		
		//echo $wp_path."<br />";echo $wp_url."<br />";	exit;
		return '';
	}*/
	
	static public function get_filepath_from_url_smart($url) {	// MAIN FUNCTION FOR GETTING FILEPATH FROM URL
		//echo 'looking for: '.$url.'<br />';

		if (self::does_file_have_resolution($url)) {
			$burl=self::remove_resolution_from_file($url);
			$file=self::get_filepath_from_url($burl);
			if ($file!='' && is_file($file)) return $file;
		}

		$file=self::get_filepath_from_url($url);
		if ($file!='' && is_file($file)) return $file;


		$id=self::get_attachment_id_from_url_maybe_with_resolution ($url);
		if ($id!=NULL) {
			$file = self::get_attachment_file_from_id($id);
			if (is_file($file)) return $file;
		}

		if (self::is_http_link($url)) {
			if (self::does_file_have_resolution($url)) {
				$burl=self::remove_resolution_from_file($url);
				$file=self::get_full_filepath_from_full_urlpath($burl);
				if ($file!='' && is_file($file)) return $file;
			}
			$file=self::get_full_filepath_from_full_urlpath($url);
			if ($file!='' && is_file($file)) return $file;
		} else {
			if (self::does_file_have_resolution($url)) {
				$burl=self::remove_resolution_from_file($url);
				$file=self::get_full_filepath_from_relative_to_wordpres_urlpath ($burl);
				if ($file!='' && is_file($file)) return $file;
				$file=self::get_full_filepath_from_relative_to_webroot_urlpath ($burl);
				if ($file!='' && is_file($file)) return $file;
			}
			$file=self::get_full_filepath_from_relative_to_wordpres_urlpath ($url);
			if ($file!='' && is_file($file)) return $file;
			$file=self::get_full_filepath_from_relative_to_webroot_urlpath ($url);
			if ($file!='' && is_file($file)) return $file;
		}


		if (self::is_http_link($url)==FALSE) 
				$url=self::get_full_urlpath_from_relative_urlpath($url);
		
		if (self::does_file_have_resolution($url)) {
			$url2=self::remove_resolution_from_file($url);
			$ret = self::get_remote_and_upload($url2);
			if ($ret) return $ret;
		}
		
		$ret = self::get_remote_and_upload($url);
		if ($ret) return $ret;

		return '';
	}
	static public function get_filepath_from_url($url) {	// shortcut for getting filepath from database via guid
		$id=self::get_attachment_id_from_url ($url);
		if ($id!=NULL) return self::get_attachment_file_from_id($id);
		return '';
	}
	static public function get_attachment_id_from_url ($url) {
		global $wpdb;
		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='".$url."'";
		$id = $wpdb->get_var($query);
		return $id;
	}
	static public function get_attachment_id_from_url_without_resolution ($url) {
		global $wpdb;
		$file=self::get_relative_to_wordpress_urlpath_from_full_urlpath($url);
		$upload_dir=self::get_full_urlpath_of_uploads_dir(1);
		$upload_dir_length=strlen($upload_dir);
		if (substr($url,0, $upload_dir_length)==$upload_dir) {
			$file=substr($url, $upload_dir_length);
			$query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%".$file."%'";
			$id = $wpdb->get_var($query);
			if ($id==NULL) return NULL;
			return $id;
		}
		return NULL;
	}
	static public function get_attachment_id_from_url_maybe_with_resolution ($url) {
		if (self::does_file_have_resolution($url))
			$url=self::remove_resolution_from_file($url);
		
		return self::get_attachment_id_from_url_without_resolution ($url);	
	}
	static public function get_attachment_id_from_url_with_resolution_unsafe ($url) {
		global $wpdb;
		$file=self::get_filename_from_url($url);
		$query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%".$file."%'";
		$id = $wpdb->get_var($query);
		if ($id==NULL) return NULL;
		return $id;
	}
	static public function get_attachment_file_from_id($id) {
		$arr=wp_get_attachment_metadata($id);
		if ($arr===FALSE) return '';
		if (!is_array($arr)) return '';
		if (!isset($arr['file'])) return '';
		$file=$arr['file'];
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $file=str_replace("/", "\\", $file);
		$upload_dir=self::get_root_of_uploads_dir();
		if ($upload_dir=='') return '';
		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";
		return $upload_dir.$slash.$file;
	}
	static public function remove_resolution_from_file($url) {
		$pos1=strrpos($url, "/");
		$pos11=strrpos($url, "\\");
		$pos1=max($pos1, $pos11);
		$pos2=strrpos($url, "-");
		$pos3=strrpos($url, "x");
		$pos4=strrpos($url, ".");
		$r=$pos4-$pos2;
		if ($pos1===FALSE || $pos2===FALSE || $pos3===FALSE || $pos4===FALSE) return FALSE;
		if ($pos1<$pos2 && $pos2<$pos3 && $pos3<$pos4 && $r<11) {
			$x=substr($url, $pos2+1, $pos3-$pos2-1);
			$y=substr($url, $pos3+1, $pos4-$pos3-1);
			if (is_numeric($x) && is_numeric($y)) {
				$part1=substr($url, 0, $pos2);
				$part2=substr($url, $pos4);
				return $part1.$part2;
			}
		}
		return FALSE;
	}
	static public function does_file_have_resolution($url)
	{
		//echo 'does_file_have_resolution ( '.$url.' )<br />';
		$pos1=strrpos($url, "/");
		$pos11=strrpos($url, "\\");
		$pos1=max($pos1, $pos11);
		$pos2=strrpos($url, "-");
		$pos3=strrpos($url, "x");
		$pos4=strrpos($url, ".");
		$r=$pos4-$pos2;
		if ($pos1===FALSE || $pos2===FALSE || $pos3===FALSE || $pos4===FALSE) return FALSE;
		if ($pos1<$pos2 && $pos2<$pos3 && $pos3<$pos4 && $r<11) {
			$x=substr($url, $pos2+1, $pos3-$pos2-1);
			$y=substr($url, $pos3+1, $pos4-$pos3-1);
			if (is_numeric($x) && is_numeric($y)) {
				$part1=substr($url, 0, $pos2);
				$part2=substr($url, $pos4);
				return TRUE;
			}
		}
		return FALSE;
	}
	static public function is_http_link ($url) {
		if (substr($url,0,7)=="http://") return TRUE;
		if (substr($url,0,8)=="https://") return TRUE;
		if (substr($url,0,2)=="//") return TRUE;
		return FALSE;	
	}
	static public function link_begin_with_slash($url) {
		if (substr($url,0,1)=="/") return TRUE;
		return FALSE;	
	}
	static public function save_file($file, $content) {
		$fp = fopen($file, 'w');
		if (!$fp) return FALSE;
		fwrite($fp, $content);
		fclose($fp);
		$stat = stat( dirname( $file ));
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@chmod( $file, $perms );
		return TRUE;
	}
	
	static public function get_remote($url) {
		$response = wp_remote_get( $url );
		if( is_wp_error( $response ) ) {
		   //$error_message = $response->get_error_message();
		   //echo "Something went wrong: $error_message";
		   return FALSE;
		} else {
			if ($response['response']['code']=='404') return FALSE;
			return $response['body'];
		}
	}
	static public function get_remote_and_upload($url) {
		$content=self::get_remote($url);
		if ($content!==FALSE) {
			$filename=self::get_filename_from_url($url);

			$dir='';
			$tdir=self::get_current_upload_dir();
			if (is_writable($tdir)) {
				$dir=self::get_current_upload_dir(true);
			} else {
				$tdir=self::get_root_of_uploads_dir();
				if (is_writable($tdir)) {
					$dir=self::get_root_of_uploads_dir(true);
				}			
			}
			if ($dir=='') $dir=self::get_current_upload_dir(true);

			$md5=md5($url);
			$filepath=$dir.$filename;
			if (file_exists($filepath)) {
				$buf = file_get_contents($filepath);
				if ($buf==$content) return $filepath;
			}
			$filepath=$dir.$md5.'_'.$filename;
			if (file_exists($filepath)) return $filepath;
			$ret=self::save_file($filepath, $content);
			if (!$ret) return FALSE;
			return $filepath;
		}
		return FALSE;
	}
}