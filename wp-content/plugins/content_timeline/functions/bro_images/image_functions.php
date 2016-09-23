<?php

abstract class all_around_image_class {

	protected $orig_image, $orig_file, $orig_w, $orig_h, $orig_type, $dest_image, $dest_file, $dest_jpeg_file, $dest_w, $dest_h, $dest_ext, $dest_dir, $dest_name, $dest_suffix, $dest_path, $error_counter, $add_suffix;

	function __construct ($orig_file='') {
		$this->error_counter=0;
		$this->add_suffix='';
		if ($orig_file!='') $this->load_image($orig_file);
	}
	
	
	function error_msg($msg, $important=true){
		if ($important) echo $msg."<br >\n";
		$this->error_counter++;
	}
	
	function is_error () {
		return $this->error_counter;
	}
	
	static function create_object($file, $engine='auto') {
		$gd=self::test_gd();
		$imagick=self::test_imagick();
		if ($engine=='auto') {
			if ($gd) return new fol_gd($file);
			if ($imagick) return new fol_imagick($file);
		} else {
			if ($gd && $engine=='gd') return new fol_gd($file);
			if ($gd && $engine=='imagick') return new fol_imagick($file);
		}
		return false;
	}

	static function test_gd ( $args=array() ) {
		if ( ! extension_loaded('gd') || ! function_exists('gd_info') )
			return false;

		if ( isset( $args['methods'] ) && in_array( 'rotate', $args['methods'] ) && !function_exists('imagerotate') )
				return false;

        return true;
	}

	static function test_imagick() {
		// First, test Imagick's extension and classes.
		if ( ! extension_loaded( 'imagick' ) || ! class_exists( 'Imagick' ) ) // || ! class_exists( 'ImagickPixel' ) )
			return false;
	
		//if ( version_compare( phpversion( 'imagick' ), '2.2.0', '<' ) ) return false;
	
		$required_methods = array(
			'clear',
			'destroy',
			'valid',
			'getimage',
			'writeimage',
			'getimageblob',
			'getimagegeometry',
			'getimageformat',
			'setimageformat',
			'setimagecompression',
			'setimagecompressionquality',
			'setimagepage',
			'scaleimage',
			'cropimage',
			'rotateimage',
			'flipimage',
			'flopimage'
		);
	
		// Now, test for deep requirements within Imagick.
		if ( ! defined( 'imagick::COMPRESSION_JPEG' ) )
			return false;
	
		if ( array_diff( $required_methods, get_class_methods( 'Imagick' ) ) )
			return false;

		return true;
	}

	function image_resize_dimensions($orig_w, $orig_h, $dest_w, $dest_h, $crop = false, $zoom_if_need=true) {
		if ($orig_w <= 0 || $orig_h <= 0)
			return false;
		// at least one of dest_w or dest_h must be specific
		if ($dest_w <= 0 && $dest_h <= 0)
			return false;

		// plugins can use this to provide custom resize dimensions
		//$output = apply_filters( 'image_resize_dimensions', null, $orig_w, $orig_h, $dest_w, $dest_h, $crop );
		//if ( null !== $output )
			//return $output;

		$enlarge=0;
		if ( $crop ) {
		
			if ($zoom_if_need) {
				if ($dest_w>$orig_w || $dest_h>$orig_h) {
					$backup_w=$dest_w;
					$backup_h=$dest_h;
					if ($orig_w<$dest_w) {
						$ar=$orig_w/$dest_w;
						$dest_w=$dest_w*$ar;
						$dest_h=$dest_h*$ar;
					}
					if ($orig_h<$dest_h) {
						$ar=$orig_h/$dest_h;
						$dest_w=$dest_w*$ar;
						$dest_h=$dest_h*$ar;
					}
					$enlarge=$backup_w/$dest_w;				
				}
			}
			// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
			$aspect_ratio = $orig_w / $orig_h;
			$new_w = min($dest_w, $orig_w);
			$new_h = min($dest_h, $orig_h);

			if ( !$new_w ) {
				$new_w = intval($new_h * $aspect_ratio);
			}

			if ( !$new_h ) {
				$new_h = intval($new_w / $aspect_ratio);
			}

			$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

			$crop_w = round($new_w / $size_ratio);
			$crop_h = round($new_h / $size_ratio);

			$s_x = floor( ($orig_w - $crop_w) / 2 );
			$s_y = floor( ($orig_h - $crop_h) / 2 );
		} else {
			// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
			$crop_w = $orig_w;
			$crop_h = $orig_h;

			$s_x = 0;
			$s_y = 0;

			list( $new_w, $new_h ) = $this -> constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
		}

		// if the resulting image would be the same size or larger we don't want to resize it
		if ( $new_w >= $orig_w && $new_h >= $orig_h ) return false;
		
		if ($enlarge>0) {
			$new_w=$new_w*$enlarge;
			$new_h=$new_h*$enlarge;
		}

		// the return array matches the parameters to imagecopyresampled()
		// int dest_x, int dest_y, int src_x, int src_y, int dest_w, int dest_h, int src_w, int src_h
		return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

	}

	function constrain_dimensions( $current_width, $current_height, $max_width=0, $max_height=0 ) {
		if ( !$max_width and !$max_height )
			return array( $current_width, $current_height );

		$width_ratio = $height_ratio = 1.0;
		$did_width = $did_height = false;

		if ( $max_width > 0 && $current_width > 0 && $current_width > $max_width ) {
			$width_ratio = $max_width / $current_width;
			$did_width = true;
		}

		if ( $max_height > 0 && $current_height > 0 && $current_height > $max_height ) {
			$height_ratio = $max_height / $current_height;
			$did_height = true;
		}

		// Calculate the larger/smaller ratios
		$smaller_ratio = min( $width_ratio, $height_ratio );
		$larger_ratio  = max( $width_ratio, $height_ratio );

		if ( intval( $current_width * $larger_ratio ) > $max_width || intval( $current_height * $larger_ratio ) > $max_height )
			// The larger ratio is too big. It would result in an overflow.
			$ratio = $smaller_ratio;
		else
			// The larger ratio fits, and is likely to be a more "snug" fit.
			$ratio = $larger_ratio;

		$w = intval( $current_width  * $ratio );
		$h = intval( $current_height * $ratio );

		// Sometimes, due to rounding, we'll end up with a result like this: 465x700 in a 177x177 box is 117x176... a pixel short
		// We also have issues with recursive calls resulting in an ever-changing result. Constraining to the result of a constraint should yield the original result.
		// Thus we look for dimensions that are one pixel shy of the max value and bump them up
		if ( $did_width && $w == $max_width - 1 )
			$w = $max_width; // Round it up
		if ( $did_height && $h == $max_height - 1 )
			$h = $max_height; // Round it up

		return array ($w, $h);
	}

	function predict_final_file($file, $w, $h, $opt, $suffix = '', $dest_path = '') {
		return self::predict_final_file_static($file, $w, $h, $opt, $suffix, $dest_path);
	}
	static function predict_final_file_static($file, $w, $h, $opt, $suffix = '', $dest_path = '') {
		$add_suffix='';
		foreach ($opt as $var => $val) $add_suffix.=$var.'-';

		$res_sufix='';
		if ($w!=0 && $h!=0) $res_sufix=$w."x".$h;
		
		$dest_suffix=$suffix.$add_suffix.$res_sufix;

		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";

		$info = pathinfo($file);
		$dest_dir = $info['dirname'];
		$dest_ext = '';
		if (isset($info['extension'])) $dest_ext = strtolower($info['extension']);
		if (isset($GLOBALS['wp'])) $dest_name = wp_basename($file, ".".$dest_ext);
		else $dest_name = basename($file, ".".$dest_ext);

		if ( !empty($dest_path) and $_dest_path = realpath($dest_path) )
				$dest_dir = $_dest_path;

		$dest_file=$dest_dir.$slash.$dest_name.'-'.$dest_suffix.'.'.$dest_ext;
		$dest_jpeg_file = $dest_dir.$slash.$dest_name."-".$dest_suffix.".jpg";
		
		if ($dest_ext!='gif' && $dest_ext!='png' && $dest_ext!='jpg' && $dest_ext!='jpeg') return $dest_jpeg_file;
		return $dest_file;	
	}
	
	function pre_save () {	// get: $this->orig_file, $this->dest_suffix (optional), $this->dest_path (optional), $this->dest_w (optional), $this->dest_h (optional)
							// define: $this->dest_suffix, $this->dest_dir, $this->dest_ext, $this->dest_name, $this->dest_file, $this->dest_jpeg_file
		// $suffix will be appended to the destination filename, just before the extension
		//if ( !$this->dest_suffix )
		
		$res_sufix='';
		if ($this->dest_w!=0 && $this->dest_h!=0) $res_sufix = $this->dest_w."x".$this->dest_h;
		
		$this->dest_suffix=$this->dest_suffix.$this->add_suffix.$res_sufix;
		//if (substr($this->dest_suffix,0,1)=='-') $this->dest_suffix=substr($this->dest_suffix,1);

		$slash="/";
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $slash="\\";

		$info = pathinfo($this->orig_file);
		$this->dest_dir = $info['dirname'];
		$this->dest_ext = '';
		if (isset($info['extension'])) $this->dest_ext = strtolower($info['extension']);
		if (isset($GLOBALS['wp'])) $this->dest_name = wp_basename($this->orig_file, ".".$this->dest_ext);
		else $this->dest_name = basename($this->orig_file, ".".$this->dest_ext);

		if ( !empty($this->dest_path) and $_dest_path = realpath($this->dest_path) )
				$this->dest_dir = $_dest_path;

		$this->dest_file=$this->dest_dir.$slash.$this->dest_name.'-'.$this->dest_suffix.'.'.$this->dest_ext;

		$this->dest_jpeg_file = $this->dest_dir.$slash.$this->dest_name."-".$this->dest_suffix.".jpg";
		
		
		//echo 'predicted_file = '.$this->dest_file; exit;
	}
	
	function post_save($dest_file) {	// chmod for saved file
		// Set correct file permissions
		$stat = stat( dirname( $dest_file ));
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@chmod( $dest_file, $perms );
	}

	function pre_load(&$orig_file) {	// check if file exists, define $this->orig_file
		if (isset($GLOBALS['wp']))
		{
			if ( is_numeric( $orig_file ) )
					$orig_file = get_attached_file( $orig_file );
		}
		
		$this->orig_file = $orig_file;

		if ( ! file_exists( $orig_file ) ) {
			$this->error_msg('File '.$orig_file.' does not exist?');
			return FALSE;
		}

		if (isset($GLOBALS['wp']))
		{
			// Set artificially high because GD uses uncompressed images in memory
			@ini_set( 'memory_limit', apply_filters( 'image_memory_limit', WP_MAX_MEMORY_LIMIT ) );
		}
		return TRUE;
	}

	function post_load ($orig_w, $orig_h, $orig_type) {	// define $this->orig_w, $this->orig_h, $this->orig_type, $this->dest_w, $this->dest_h
		$this->orig_w = $orig_w;
		$this->orig_h = $orig_h;
		$this->orig_type = $orig_type;
		$this->dest_w = $this->orig_w;
		$this->dest_h = $this->orig_h;	
		//echo $this->orig_type; exit;
	}

	abstract function load_image( $orig_file );
	abstract function resize( $max_w, $max_h, $crop = false);
	abstract function gray ($what=0);
	abstract function save( $suffix = null, $dest_path = null, $jpeg_quality = 95 );
}


class fol_gd extends all_around_image_class {
	function __destruct () {
		if ( isset($this->orig_image)) if (is_resource( $this->orig_image ) ) imagedestroy( $this->orig_image );
		if ( isset($this->dest_image)) if (is_resource( $this->dest_image ) ) imagedestroy( $this->dest_image );
	}
	function load_image( $orig_file ) {

		$r = $this->pre_load ($orig_file);
		if ($r===FALSE) return FALSE;

		if ( ! function_exists('imagecreatefromstring') ) {
			$this->error_msg('The GD image library is not installed.');
			return FALSE;
		}

		$this->orig_image = imagecreatefromstring( file_get_contents( $orig_file ) );

		if ( !is_resource( $this->orig_image ) ) {
			$this->error_msg('File '.$orig_file.' is not an image.');
			return FALSE;
		}

		$orig_size = @getimagesize( $orig_file );
		if ( !$orig_size ) {
			$this->error_msg('invalid_image - Could not read image size - '. $orig_file);
			return FALSE;
		}

		$this->post_load($orig_size[0], $orig_size[1], $orig_size[2]);

		return TRUE;
	}


	/**
	 * Scale down an image to fit a particular size and save a new copy of the image.
	 *
	 * The PNG transparency will be preserved using the function, as well as the
	 * image type. If the file going in is PNG, then the resized image is going to
	 * be PNG. The only supported image types are PNG, GIF, and JPEG.
	 *
	 * Some functionality requires API to exist, so some PHP version may lose out
	 * support. This is not the fault of WordPress (where functionality is
	 * downgraded, not actual defects), but of your PHP version.
	 *
	 * @since 2.5.0
	 *
	 * @param string $file Image file path.
	 * @param int $max_w Maximum width to resize to.
	 * @param int $max_h Maximum height to resize to.
	 * @param bool $crop Optional. Whether to crop image or resize.
	 * @param string $suffix Optional. File suffix.
	 * @param string $dest_path Optional. New image file path.
	 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
	 * @return mixed WP_Error on failure. String with new destination path.
	 */
	function resize( $max_w, $max_h, $crop = false, $zoom_if_need=true) {

		if ( !is_resource( $this->orig_image ) ) {
			$this -> error_msg ('Error: orig_image not loaded');
			return FALSE;
		}

		//list($orig_w, $orig_h, $orig_type) = $this->size;
		$orig_w=$this->orig_w;
		$orig_h=$this->orig_h;
		$orig_type=$this->orig_type;
		if ( $orig_w == $max_w && $orig_h == $max_h ) return TRUE;
		
		//echo $orig_w.' x '.$orig_h.'<br />';echo $max_w.' x '.$max_h.'<br />';

		$dims = $this->image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop, $zoom_if_need);
		if ( !$dims ) {
				$this -> error_msg ( 'error_getting_dimensions - Could not calculate resized image dimensions', false );
				return FALSE;
			}
		list($dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h) = $dims;

		if (isset($GLOBALS['wp'])) $this->dest_image = wp_imagecreatetruecolor( $dest_w, $dest_h );
		else $this->dest_image = imagecreatetruecolor( $dest_w, $dest_h );

		imagecopyresampled( $this->dest_image, $this->orig_image, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);

		$this->dest_w=$dest_w;
		$this->dest_h=$dest_h;

		// convert from full colors to index colors, like original PNG.
		if ( IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor( $this->orig_image ) )
				imagetruecolortopalette( $this->dest_image, false, imagecolorstotal( $this->orig_image ) );

		// we don't need the original in memory anymore - depricated
		//imagedestroy( $this->orig_image );
		return TRUE;
	}
	
	function gray ($what=0)	// 0 = auto, 1 = source, 1 = dest
	{
		if ($what==0) {
			if (isset($this->orig_image)) if ( is_resource( $this->orig_image ) ) $what=1;
			if (isset($this->dest_image)) if ( is_resource( $this->dest_image ) ) $what=2;
		}
		if ($what==1) {
			if ( !is_resource( $this->orig_image ) ) {
				$this -> error_msg ('Error: orig_image not loaded');
				return '';
			}
			$this->add_suffix.='gray-';
			if ( function_exists('imagefilter') ) imagefilter($this->orig_image, IMG_FILTER_GRAYSCALE);
		}
		if ($what==2) {
			if ( !is_resource( $this->dest_image ) ) {
				$this -> error_msg ('Error: dest_image not created');
				return '';
			}
			$this->add_suffix.='gray-';
			if ( function_exists('imagefilter') ) imagefilter($this->dest_image, IMG_FILTER_GRAYSCALE);
		}
	}

	function save( $suffix = '', $dest_path = '', $jpeg_quality = 95 ) {
		if ( !isset($this->orig_image) || !is_resource( $this->orig_image ) ) {
			$this -> error_msg ('Error: orig_image not loaded');
			return '';
		}
		if (!isset($this->dest_image)) {
			$this->dest_image = imagecreatetruecolor($this->orig_w, $this->orig_h);
			
			if ( IMAGETYPE_PNG == $this->orig_type ) imagealphablending($this->dest_image,false);
			imagecopy($this->dest_image, $this->orig_image, 0, 0, 0, 0, $this->orig_w, $this->orig_h);			
			if ( IMAGETYPE_PNG == $this->orig_type ) imagealphablending($this->dest_image,true);
			// convert from full colors to index colors, like original PNG.
			if ( IMAGETYPE_PNG == $this->orig_type && function_exists('imageistruecolor') && !imageistruecolor( $this->orig_image ) )
					imagetruecolortopalette( $this->dest_image, false, imagecolorstotal( $this->orig_image ) );
		}
		if ( !is_resource( $this->dest_image ) ) {
			$this -> error_msg ('Error: dest_image not created');
			return '';
		}

		$this->dest_suffix=$suffix;
		$this->dest_path=$dest_path;
		$this->pre_save();

		if ( IMAGETYPE_GIF == $this->orig_type ) {
			if ( !imagegif( $this->dest_image, $this->dest_file ) ) {
				$this -> error_msg ('resize_path_invalid - Resize path invalid' );
				return '';
			}
		} elseif ( IMAGETYPE_PNG == $this->orig_type ) {
			imagesavealpha($this->dest_image,true);
			if ( !imagepng( $this->dest_image, $this->dest_file ) ) {
				$this -> error_msg ('resize_path_invalid - Resize path invalid' );
				return '';
			}
		} else {
			// all other formats are converted to jpg
			if ( 'jpg' != $this->dest_ext && 'jpeg' != $this->dest_ext ) 
				$this->dest_file = $this->dest_jpeg_file;
			if ( !imagejpeg( $this->dest_image, $this->dest_file, $jpeg_quality ) ) {
				$this -> error_msg ('resize_path_invalid - Resize path invalid' );
				return '';
			}
		}

		imagedestroy( $this->dest_image );
		
		$this->post_save($this->dest_file);

		return $this->dest_file;
	}
}

class fol_imagick extends all_around_image_class {
	function __destruct () {
		if ( isset($this->orig_image)) {
			if ( $this->orig_image ) {
				$this->orig_image->clear();
				$this->orig_image->destroy();
			}		
		}
	}
	function load_image( $orig_file ) {
		$r = $this->pre_load ($orig_file);
		if ($r===FALSE) return FALSE;

		if ( $this->orig_image )
				return FALSE;

		try {
			$this->orig_image = new Imagick( $this->orig_file );
		
			if( ! $this->orig_image->valid() ) {
				$this -> error_msg ('File '.$this->orig_file.' is not an image.');
				return FALSE;
			}
		
			// Select the first frame to handle animated images properly
			if ( is_callable( array( $this->orig_image, 'setIteratorIndex' ) ) )
				$this->orig_image->setIteratorIndex(0);
				
				$size = $this->orig_image->getImageGeometry();
				$w = $size['width'];
				$h = $size['height'];
		
				$format = $this->orig_image->getImageFormat() ;
				$this->post_load($w, $h, $format);
		}
		catch ( Exception $e ) {
				$this -> error_msg ('File '.$this->orig_file.' is not an image.');
				return FALSE;
		}

		return TRUE;
	}
	
	
	function resize( $max_w, $max_h, $crop = false, $zoom_if_need=true) {
		if ( !isset( $this->orig_image ) || !$this->orig_image ) {
			$this -> error_msg ('Error: orig_image not loaded');
			return FALSE;
		}

		$orig_w=$this->orig_w;
		$orig_h=$this->orig_h;
		$orig_type=$this->orig_type;
		if ( $orig_w == $max_w && $orig_h == $max_h ) return TRUE;

		$dims = $this->image_resize_dimensions( $orig_w, $orig_h, $max_w, $max_h, $crop, $zoom_if_need );
		if ( ! $dims ) {
			$this->error_msg ( 'Could not calculate resized image dimensions', false );
			return FALSE;
		}
		list( $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h ) = $dims;

		if ( $crop ) {
				return $this->crop( $src_x, $src_y, $src_w, $src_h, $dest_w, $dest_h );
		}
		
		try {
			/**
			* @TODO: Thumbnail is more efficient, given a newer version of Imagemagick.
			* $this->image->thumbnailImage( $dst_w, $dst_h );
			*/
			$this->orig_image->scaleImage( $dest_w, $dest_h );
		}
		catch ( Exception $e ) {
			$this->error_msg ( 'image_resize_error: ' . $e->getMessage() );
			return FALSE;
		}
		
		$this->dest_w=$dest_w;
		$this->dest_h=$dest_h;
		$this->orig_w=$dest_w;
		$this->orig_h=$dest_h;
		return TRUE;
	}

	function crop( $src_x, $src_y, $src_w, $src_h, $dest_w = null, $dest_h = null, $src_abs = false ) {
		if ( $src_abs ) {
				$src_w -= $src_x;
				$src_h -= $src_y;
		}

		try {
			$this->orig_image->cropImage( $src_w, $src_h, $src_x, $src_y );
			$this->orig_image->setImagePage( $src_w, $src_h, 0, 0);

			if ( $dest_w || $dest_h ) {
				// If destination width/height isn't specified, use same as
				// width/height from source.
				if ( ! $dest_w )
					$dest_w = $src_w;
				if ( ! $dest_h )
					$dest_h = $src_h;

				$this->orig_image->scaleImage( $dest_w, $dest_h );
				$this->dest_w=$dest_w;
				$this->dest_h=$dest_h;
				$this->orig_w=$dest_w;
				$this->orig_h=$dest_h;
				return true;
			}
		}
		catch ( Exception $e ) {
			$this->error_msg ( 'image_crop_error: ' . $e->getMessage() );
			return false;
		}
		return true;
	}

	function gray ($what=0) {
		if ( !isset( $this->orig_image ) || !$this->orig_image ) {
			$this -> error_msg ('Error: orig_image not loaded');
			return false;
		}
		$this->add_suffix.='gray-';
		return $this->orig_image->setImageColorspace(Imagick::COLORSPACE_GRAY);
	}

	function save( $suffix = '', $dest_path = '', $jpeg_quality = 95 ) {
		if ( !isset( $this->orig_image ) || !$this->orig_image ) {
			$this -> error_msg ('Error: orig_image not loaded');
			return '';
		}
		$this->dest_suffix=$suffix;
		$this->dest_path=$dest_path;
		$this->pre_save();

		try {
				// Store initial Format
				//$orig_format = $this->image->getImageFormat();
				//$this->image->setImageFormat( strtoupper( $this->dest_ext ) );
				if (strtoupper($this->orig_type)=='JPEG') {
					$this->orig_image->setImageCompressionQuality( $jpeg_quality );
					$this->orig_image->setImageCompression( imagick::COMPRESSION_JPEG );
				}
				$this->orig_image->writeImage( $this->dest_file );

				// Reset original Format
				//$this->image->setImageFormat( $orig_format );
		}
		catch ( Exception $e ) {
				$this -> error_msg( 'image_save_error: '. $e->getMessage() . " = ". $this->dest_file );
				return '';
		}

		$this->post_save($this->dest_file);
		return $this->dest_file;
	}
}