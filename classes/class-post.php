<?php
class Genesis_Club_Post { 
	
	static protected $css='';
 
   static function init() {
		add_shortcode('genesis_club_post_dates', array(__CLASS__,'post_dates'));
		add_shortcode('genesis_club_recent_update', array(__CLASS__,'recent_update'));
		add_action('widgets_init', array(__CLASS__,'register_widgets'));	
	}	

	public static function post_dates($attr) {
		$defaults = array('published' => __('First published on '), 'updated' => __('Last updated on '),'before' => '<span class="postmeta-date">', 'after' => '</span>', 'format' => get_option( 'date_format' ), 'separator' => '&nbsp;&middot&nbsp;', 'interval' => 3);
   		$params = shortcode_atts( $defaults, $attr ); 
		$updated = genesis_post_modified_date_shortcode (array ('format' => $params['format'], 'label' => $params['updated'], 'before' => $params['before'],  'after' => $params['after']  ));
		$pub_date =  new DateTime(get_the_time( 'c' ));
		$mod_date = new DateTime(get_the_modified_time( 'c' ));
		$interval = round(($mod_date->format('U') - $pub_date->format('U')) / (60*60*24));
		if ($interval > $params['interval']) {
			$published = genesis_post_date_shortcode (array ('format' => $params['format'], 'label' => $params['published'], 'before' => $params['before'], 'after' => $params['after']  ));
			return $published . $params['separator'] . $updated;   
		} else {
			return $updated;
		}
	}

	public static function recent_update($attr) {
		$defaults = array('before' => '<span class="postmeta-date">', 'after' => '</span>', 'label' => __('Last updated on '), 'format' => get_option( 'date_format' ), 'interval' => 90);
		$params = shortcode_atts( $defaults, $attr ); 
		$now_date =  new DateTime();
		$mod_date = new DateTime(get_the_modified_time( 'c' ));
		$interval = round(($now_date->format('U') - $mod_date->format('U')) / (60*60*24));
		if ($interval <= $params['interval']) { //it is recent
			unset($params['interval']);
			return genesis_post_modified_date_shortcode ( $params );  
		} 
	}	

	static function register_widgets() {
		register_widget( 'Genesis_Club_Posts_Widget' );
		register_widget( 'Genesis_Club_Post_Specific_Widget' );
		register_widget( 'Genesis_Club_Post_Image_Gallery_Widget' );
		add_action('wp', array(__CLASS__,'prepare'));			
	}

	static function prepare() {
		global $wp_widget_factory;
		if ($obj = $wp_widget_factory->widgets['Genesis_Club_Post_Specific_Widget']) {
			add_action('wp_print_styles', array($obj,'add_custom_css'), 10);	//add any custom CSS	
			add_action('wp_print_styles', array(__CLASS__,'print_css'), 20); //output the CSS		
		}
	}

	static function add_css($css) {     
         self::$css .=  $css; //append CSS for output later
	}

	static function print_css() {
		if (!empty(self::$css)) printf ('<style type="text/css">%1$s</style>',self::$css);
		self::$css = ''; //clear
	}

}