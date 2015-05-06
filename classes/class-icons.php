<?php
/* Adds short code for Simple Social Icons and allows you to have multiple sets of icons at different sizes on the same page 
   Functionality is only available if Simple Social Icons plugin is also installed
*/
class Genesis_Club_Icons {
	static protected $defaults =  array(
			'title'                  => '',
			'new_window'             => 0,
			'size'                   => 36,
			'border_radius'          => 3,
			'icon_color'             => '#ffffff',
			'icon_color_hover'       => '#ffffff',
			'background_color'       => '#999999',
			'background_color_hover' => '#666666',
			'alignment'              => 'alignleft',
			'dribbble'               => '',
			'email'                  => '',
			'facebook'               => '',
			'flickr'                 => '',
			'github'                 => '',
			'gplus'                  => '',
			'instagram'              => '',
			'linkedin'               => '',
			'pinterest'              => '',
			'rss'                    => '',
			'stumbleupon'            => '',
			'tumblr'                 => '',
			'twitter'                => '',
			'vimeo'                  => '',
			'youtube'                => '',
		);
	static protected $glyphs = array(
			'dribbble'		=> '&#xe800;',
			'email'			=> '&#xe80b;',
			'facebook'		=> '&#xe802;',
			'flickr'		=> '&#xe80a;',
			'github'		=> '&#xe80c;',
			'gplus'			=> '&#xe801;',
			'instagram' 	=> '&#xe809;',
			'linkedin'		=> '&#xe806;',
			'pinterest'		=> '&#xe803;',
			'rss'			=> '&#xe805;',
			'stumbleupon'	=> '&#xe808;',
			'tumblr'		=> '&#xe807;',
			'twitter'		=> '&#xe80d;',
			'vimeo'			=> '&#xe80e;',
			'youtube'		=> '&#xe804;',
		);
	static protected $profile_labels = array(
			'dribbble' => 'Dribbble URI',
			'email' => 'Email URI',
			'facebook' =>  'Facebook URI',
			'flickr' => 'Flickr URI',
			'github' => 'GitHub URI',
			'gplus' =>  'Google+ URI',
			'instagram' =>'Instagram URI',
			'linkedin' => 'Linkedin URI',
			'pinterest' => 'Pinterest URI', 
			'rss' =>  'RSS URI',
			'stumbleupon'  => 'StumbleUpon URI', 
			'tumblr' => 'Tumblr URI', 
			'twitter' => 'Twitter URI', 
			'vimeo' =>  'Vimeo URI', 
			'youtube' =>  'YouTube URI');
	static protected $profiles;
	static protected $css='';
	static protected $initialized = false;

	static function init() {
		if (class_exists('Simple_Social_Icons_Widget'))	{
			add_shortcode('simple_social_icons', array(__CLASS__, 'display'));
			add_shortcode('simple-social-icons', array(__CLASS__, 'display'));
			add_action('wp', array(__CLASS__,'prepare'));				
		}
	}

	static function prepare() {
		global $wp_widget_factory;
		if ($obj = $wp_widget_factory->widgets['Simple_Social_Icons_Widget']) {
			remove_action('wp_head', array($obj,'css'));
			$new_obj = self::recast_object($obj,'Genesis_Club_Icons_Widget'); //recast as an enhanced widget
			add_action('wp_print_styles', array($new_obj,'css'));	//improved widget allows multiple instances per page, each with its own CSS		
		}
	}
	
	static function init_profiles() {
		if (self::$initialized) return;
		
		self::$defaults = apply_filters( 'simple_social_default_styles', self::$defaults );
		self::$glyphs = apply_filters( 'simple_social_default_glyphs', self::$glyphs );
		$profiles = array();
		foreach (self::$profile_labels as $profile => $label) 
			$profiles[$profile] =  array(
				'label'   => __( $label, 'ssiw' ),
				'pattern' => sprintf('<li class="social-%1$s"><a href="%%s" %%s>%2$s</a></li>', 
						$profile, self::$glyphs[$profile]));
		self::$profiles = apply_filters( 'simple_social_default_profiles', $profiles);
		self::$css = '';
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_css_footer' ) );
		self::$initialized = true;
	}
	
	static function display($attr) {
		self::init_profiles();
		$instance = shortcode_atts(self::$defaults, $attr) ;
		$instance['widget_id'] = 'simple-social-icons-'.rand(1000,1000000);
		$new_window = $instance['new_window'] ? 'target="_blank"' : '';
		$output ='';
		foreach ( self::$profiles as $profile => $data ) {
			if ( empty( $instance[ $profile ] ) ) continue;
			if ( is_email( $instance[ $profile ] ) )
				$output .= sprintf( $data['pattern'], 'mailto:' . esc_attr( $instance[$profile] ), $new_window );
			else
				$output .= sprintf( $data['pattern'], esc_url( $instance[$profile] ), $new_window );
		}
		if ( $output ) {
			$output = sprintf( '<div id="%1$s" class="genesis-club-icons simple-social-icons" style="visibility:hidden"><ul class="%2$s">%3$s</ul></div>', 
				$instance['widget_id'], $instance['alignment'], $output );
			self::add_css($instance);
		}
		return $output;
	}

	static function add_css($instance) {
    	$prefix = sprintf('#%1$s ul li', $instance['widget_id']);	
		$font_size = round( (int) $instance['size'] / 2 );
		$icon_padding = round ( (int) $font_size / 2 );
		$style = $prefix.' a,'. $prefix . ' a:hover {';
		if ($font_size) $style .= sprintf('font-size: %1$spx;',$font_size);
		if ($icon_padding) $style .= sprintf('padding: %1$spx;',$icon_padding);
		if ($instance['icon_color']) $style .= sprintf('color: %1$s;',$instance['icon_color']);
		if ($instance['background_color']) $style .= sprintf('background-color: %1$s;',$instance['background_color']);
		if ($instance['border_radius']) $style .= sprintf('border-radius: %1$spx;-moz-border-radius: %1$spx;-webkit-border-radius:%1$spx;',$instance['border_radius']);
		$style .= '}';
		$style .= $prefix.' a:hover {';
		if ($instance['icon_color_hover']) $style .= sprintf('color: %1$s;',$instance['icon_color_hover']);
		if ($instance['background_color_hover']) $style .= sprintf('background-color: %1$s;',$instance['background_color_hover']);
		$style .= '}';
		if (!self::$css) self::enqueue_css(); //call once
		self::$css .= $style; //append CSS for output later once all content has been parsed
	}

	static function enqueue_css() {
		$cssfile = apply_filters( 'simple_social_default_css', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		wp_enqueue_style( 'simple-social-icons-font', esc_url( $cssfile ), array(), '1.0.5', 'all' );
	}

	static function print_css_head() {
		if (!empty(self::$css))  printf ('<style type="text/css">%1$s</style>',self::$css);
		self::$css = ''; //clear
	}
	
	static function print_css_footer() {
		$css = self::$css;
		if (empty($css)) return;
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($) { 
	$('<style type="text/css">{$css}</style>').appendTo('head');
	$('.genesis-club-icons').css('visibility','visible');
});	
//]]>
</script>
	
SCRIPT;
	}

	static function recast_object($instance, $class) {
    	return unserialize(sprintf(
    	    'O:%d:"%s"%s',
    	    strlen($class),
    	    $class,
    	    strstr(strstr(serialize($instance), '"'), ':')
    	));
	}	
}