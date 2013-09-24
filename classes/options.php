<?php
class GenesisClubOptions {
	const OPTION_NAME  = 'genesis_club_options';
	protected static $options  = array();
	protected static $defaults = array('accordion_disabled' => false,
		'bar_disabled' => false, 'page_control_disabled' => false, 'profile_disabled' => false,
		'seo_disabled' => false, 'slider_disabled' => false, 'social_disabled' => false);

	static function init($more = array()) {
		self::$defaults = array_merge(self::$defaults, $more); 
		self::$options = array(); //clear cache
	}	

	static function save_options($new_options) {
		$options = self::get_options(false);
		$new_options = shortcode_atts( self::get_defaults(), array_merge($options, $new_options));
		$updated = update_option(self::OPTION_NAME,$new_options);
		if ($updated) self::get_options(false);
		return $updated;
	}	

	static function get_defaults() {
		return self::$defaults;
	}

	static function get_options($cache = true) {
	   if ($cache && (count(self::$options) > 0)) return self::$options;
	   $the_options = get_option(self::OPTION_NAME);
	   self::$options = empty($the_options) ? self::get_defaults() : shortcode_atts( self::get_defaults(), $the_options);
	   return self::$options;
	}
	
	static function get_option($option_name, $cache = true) {
    	$options = self::get_options($cache);
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;    		
    }

	static function is_html5() {
		return function_exists('genesis_html5') && genesis_html5(); 
	}

	static function is_genesis2() {
		return function_exists('genesis_html5') ; 
	}
   
   static function json_encode($params) {
   		//fix numerics and booleans
		$pat = '/(\")([0-9]+)(\")/';	
		$rep = '\\2';
		return str_replace (array('"false"','"true"'), array('false','true'), 
			preg_replace($pat, $rep, json_encode($params)));
   } 
}
?>