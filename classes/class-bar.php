<?php
class Genesis_Club_Bar {
	const HIDE_BAR_METAKEY = '_genesis_club_bar_hide';
	const SHOW_BAR_METAKEY = '_genesis_club_bar_show';

   protected static $bar = false;

   protected static $defaults  = array(
			'enabled' => false,
			'full_message' => '',
			'laptop_message' => '',
			'tablet_message' => '',
			'short_message' => '',
			'font_color' => '#FFFFFF',
			'background' => '#CDEEEE',
			'show_timeout'=> 0.5,
			'hide_timeout' => 0,
			'bounce' => false,
			'shadow' => false,
			'opener' => false,
			'location' => 'body',
			'position' => 'top'
	);

	static function init() {
		Genesis_Club_Options::init(array('bar' => self::$defaults));	
		add_action('widgets_init',array(__CLASS__,'register_widgets'));
		if (!is_admin()) add_action('wp',array(__CLASS__,'prepare'));
	}

	static function register_widgets() {
		if (class_exists('Genesis_Club_Bar_Widget')) register_widget( 'Genesis_Club_Bar_Widget' );
	}

	static function prepare() {
		if (self::is_bar_page()) {
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
			if (self::get_option('enabled')) self::add_bar(self::get_options()); 
		}
	}

    static function add_bar($bar) {
        self::$bar = wp_parse_args( $bar, self::get_defaults());
    }

	static function get_defaults() {
    	return self::$defaults;
    }

	static function enqueue_scripts() {
    	wp_enqueue_style('jquery-bar', plugins_url('styles/bar.css', dirname(__FILE__)),
    		array(), GENESIS_CLUB_VERSION);
    	wp_enqueue_script('jquery-bar', plugins_url('scripts/jquery.bar.js', dirname(__FILE__)),
    		array('jquery','jquery-effects-bounce'), GENESIS_CLUB_VERSION, true);
		add_action('wp_print_footer_scripts', array(__CLASS__, 'init_bar'),10);
	}

	static function save_options($bar) {
    	return Genesis_Club_Options::save_options(array('bar' => $bar));
    }


	static function get_options() {
    	return Genesis_Club_Options::get_option('bar');
    }
	
	static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;
    }

	static function is_bar_key($key) {
		return array_key_exists($key, array_keys(self::$defaults));
	}

	static function get_toggle_meta_key($post_type) {
		return 'post'==$post_type ? self::HIDE_BAR_METAKEY : self::SHOW_BAR_METAKEY;
	}
	
	static function is_bar_page() {
	    global $wp_widget_factory;
        if (self::get_option('enabled')) {
		    if (is_front_page() || is_category() || is_tag())
			    return true;
			elseif (is_singular('post'))
			    return ! get_post_meta(get_queried_object_id(), self::HIDE_BAR_METAKEY,true);
			else
			    return is_singular() && get_post_meta(get_queried_object_id(),self::SHOW_BAR_METAKEY,true);
		} else
				return is_active_widget( false, false, 'genesis-club-bar', true ) ;
	}

	static function init_bar() {
		$bar = self::$bar;
        if (!is_array($bar)) return false;
		$font_color = $bar['font_color'];
		$background = $bar['background'] ;
		$opener = $bar['opener'] ? 'true' : 'false';
		$bounce = $bar['bounce'] ? 'true' : 'false';
		$shadow = $bar['shadow'] ? 'true' : 'false';
		$full_message = str_replace('"','\"',html_entity_decode($bar['full_message']));
		$laptop_message = str_replace('"','\"',html_entity_decode($bar['laptop_message']));
		$tablet_message = str_replace('"','\"',html_entity_decode($bar['tablet_message']));
		$short_message = str_replace('"','\"',html_entity_decode($bar['short_message']));
		$show_timeout = is_numeric($bar['show_timeout'])?1000.0*$bar['show_timeout'] : 0;
		$hide_timeout = is_numeric($bar['hide_timeout'])?1000.0*$bar['hide_timeout'] : 0;
		$location = $bar['location'] ;
		if (empty($location)) $location = 'body';
		$position = $bar['position'] ;
		if (empty($position)) $position = 'top';
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	jQuery('{$location}').bar({
		full_message	: "{$full_message}",
		laptop_message	: "{$laptop_message}",
		tablet_message	: "{$tablet_message}",
		short_message	: "{$short_message}",
		font_color : "{$font_color}",
		background : "{$background}",
		show_timeout : {$show_timeout},
		hide_timeout : {$hide_timeout},
		bounce : {$bounce},
		shadow : {$shadow},
		opener : {$opener},
		position : "{$position}"
	});
});
//]]>
</script>	
SCRIPT;
	}

}