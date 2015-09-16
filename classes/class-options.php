<?php
class Genesis_Club_Options {
	const OPTIONS_NAME  = 'genesis_club_options';
	protected static $defaults = array( 'display_disabled' => false, 
				'accordion_disabled' => false, 'background_disabled' => false, 'bar_disabled' => false, 
				'calendar_disabled' => false, 'footer_disabled' => false, 'fonts_disabled' => false, 'icons_disabled' => false, 
				'landing_disabled' => false, 'media_disabled' => false, 'menu_disabled' => false, 'post_disabled' => false, 
				'seo_disabled' => false, 'signature_disabled' => false, 'slider_disabled' => false, 
				'social_disabled' => false, 'api_disabled' => false, 'custom_post_types' => array(), 
	); 		

    protected static $options = null;	

    public static function init($more = array()) {
        if (self::$options === null) self::$options = new Genesis_Club_DIY_Options(self::OPTIONS_NAME, self::$defaults);
		if (count($more) > 0) self::$options->add_defaults($more);
    }

	public static function get_options ($cache = true) {
		return self::$options->get_options($cache = true); 
	}

	public static function get_option($option_name, $cache = true) {
	    return self::$options->get_option($option_name, $cache); 
	}

	public static function save_options ($options) {
		return self::$options->save_options($options);
	}

	public static function validate_options ($defaults, $options) {
		return self::$options->validate_options((array)$defaults, (array)$options);
	}	

	public static function upgrade_options () {
		return self::$options->upgrade_options();
	}	

}