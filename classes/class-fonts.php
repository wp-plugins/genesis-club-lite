<?php
class Genesis_Club_Fonts {
   const GOOGLE_FONTS_API_KEY = 'AIzaSyCU14DFkcbylw3t2kVmk89PblbmV5GPR7E';
   const OPTION_NAME = 'fonts';
   const ALL_FONTS_OPTION_NAME = 'genesis_club_fonts';

   protected static $effects = array('anaglyph','brick-sign','canvas-print','crackle','decaying','destruction','distressed','distressed-wood',
         'fire','fire-animation','fragile','grass','ice','mitosis','neon','outline','putting-green','scuffed-steel', 'shadow-multiple',
         'splintered','static','stonewash','3d', '3d-float','vintage', 'wallpaper');

   protected static $subsets = array('latin','latin-ext','menu','arabic','bengali','cyrillic','cyrillic-ext','greek','greek-ext','hindi','khmer','korean','lao','tamil','vietnamese');

	protected static $defaults  = array(
		'families' => array(),
      'subsets' => array('latin'),
      'effects' => array(),
      'fv' => array(),
      'font_awesome' => false
	);

	static function init() {
		Genesis_Club_Options::init(array(self::OPTION_NAME => self::$defaults));	
		if (!is_admin()) {
			add_action('wp', array(__CLASS__,'prepare'));
		}		
	}		

	static function get_effects() {
    	return self::$effects;		
   }

	static function get_subsets() {
    	return self::$subsets;		
   }

	static function get_all_fonts() {
    	return get_option(self::ALL_FONTS_OPTION_NAME, array());
   }

	static function save_options($new_options) {
	   $new_options = array_merge(self::get_options(false), $new_options); //overwrite old options with new options 
      $new_options = Genesis_Club_Options::validate_options(self::$defaults, $new_options); //filter out any invalid options
    	return Genesis_Club_Options::save_options( array(self::OPTION_NAME => $new_options));		
    }

	static function get_options() {
    	return Genesis_Club_Options::get_option(self::OPTION_NAME);		
    }
	
	static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options) && array_key_exists($option_name,self::$defaults))
        	return $options[$option_name];
    	else
        	return false;    		
    }

	static function get_default($option_name) {
      return array_key_exists($option_name, self::$defaults) ?  self::$defaults[$option_name] : false;
   }


   static function family_exists($font_id) {
      return array_key_exists($font_id, self::get_families());
   }
      
   static function get_families() {
      if ($families = self::get_option('families'))
         return (array)$families;
      else
         return array();
   }

   static function save_families($families) {
      $options = self::get_options(false);
      array_walk($families, array(__CLASS__, 'add_fv'));
      ksort($families);
      $options['families'] = $families;
      return self::save_options($options);
   }

   static function delete_families($font_ids) {
		$font_ids = (array)$font_ids;
		$deleted= 0;
		$families = self::get_families();
		foreach ($font_ids as $font_id) {
			$font_id = strtolower(trim($font_id));
         	if (array_key_exists($font_id, $families)) {
			   unset($families[$font_id]);
            	$deleted++;            
         	}
      }
		if($deleted)
		    self::save_families($families);

		return $deleted;
    }

	public static function add_fv(&$item, $key) {
      $variants = isset($item['variants']) ? (array)$item['variants'] : false;
      if (!$variants || ((count($variants) == 1) && ('regular' == $variants[0])))
         $item['fv'] =  urlencode($item['family']);
      else
         $item['fv'] = urlencode($item['family']) . ':' . implode(',', $variants);      
   }

	public static function prepare() {
      add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
	}

	public static function enqueue_styles() {
      if ($families = self::get_option('families')) {
         $fv = array();
         foreach ($families as $key => $values) $fv[] = $values['fv'];                 
         $args['family'] = implode('%7C', $fv);
         
         if ($subsets = self::get_option('subsets')) 
            $args['subset'] = implode('%2C', (array)$subsets);

         if ($effects = self::get_option('effects')) 
            $args['effect'] = implode('%7C', (array)$effects);
            
         $url = add_query_arg($args, sprintf('http%1$s://fonts.googleapis.com/css', is_ssl() ? 's' : '' ));

         wp_enqueue_style('genesis-club-fonts', $url, array(), null);
      }
	  if (self::get_option('font_awesome')) {
         Genesis_Club_Utils::register_icons_font();
      }
	}

}