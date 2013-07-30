<?php
class GenesisClubOptions {
	protected static $options_name  = 'genesis_club_options';
	protected static $options  = array();
	protected static $defaults = array();	

	static function init($more = array()) {
		self::$defaults = array_merge(self::$defaults, $more); 
	}	

	static function save_options($new_options) {
		$options = self::get_options(false);
		$new_options = shortcode_atts( $options, $new_options);
		$updated = update_option(self::$options_name,$new_options);
		if ($updated) self::get_options(false);
		return $updated;
	}	

	static function get_defaults($cache = true) {
		return self::$defaults;
	}

	static function get_options($cache = true) {
	   if ($cache && (count(self::$options) > 0)) return self::$options;
	   $the_options = get_option(self::$options_name);
	   self::$options = empty($the_options) ? self::$defaults : shortcode_atts( self::get_defaults(), $the_options);
	   return self::$options;
	}
	
	static function get_option($option_name) {
    	$options = self::get_options();
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
    
}
?>