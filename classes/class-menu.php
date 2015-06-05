<?php
class Genesis_Club_Menu {
	
	protected static $defaults  = array(
		'threshold' => '',
		'icon_size' => '',
		'icon_color' => '',
		'primary' => 'below',
		'secondary' => 'below',
		'header' => 'none',
		'search_menu' => 'none',
		'search_text' => 'Search',
		'search_button' => true
	);
	protected static $side_menu_left = '';
	protected static $side_menu_right = '';
	protected static $below_menu = false;	
	protected static $search_text;
	protected static $is_html5;
	
	public static function init() {
		Genesis_Club_Options::init(array('menu' => self::$defaults));
		self::$is_html5 = Genesis_Club_Utils::is_html5();
		if (!is_admin()) add_action('wp',array(__CLASS__,'prepare'));
	}

	public static function prepare() {
		if (self::get_option('threshold')) {
			if ($primary = self::get_option('primary')) 
				add_filter('genesis_do_nav', array(__CLASS__,'add_responsive_menu'),100,3);
			if ($secondary = self::get_option('secondary')) 
				add_filter('genesis_do_subnav', array(__CLASS__,'add_responsive_menu'),100,3);
			if ($header = self::get_option('header')) 
				add_filter('wp_nav_menu', array(__CLASS__,'add_responsive_widget_menu'),100,2);
			if ($primary || $secondary || $header) {				
				add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue_dashicons'));
				if (in_array('left',array($primary,$secondary,$header)) || in_array('right',array($primary,$secondary,$header))) {
					add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue_sidr_styles'));
					add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue_sidr_scripts'));
				}
				add_action('wp_print_styles', array(__CLASS__, 'print_styles'));
				add_action('wp_print_footer_scripts', array(__CLASS__, 'print_scripts'));
			}				
		}
	 	if (($search = self::get_option('search_menu')) && ('none' != $search)) {
	 		add_filter('wp_nav_menu_items',  array(__CLASS__,'maybe_add_search_form'),10,2 );	
	 		add_action('wp_enqueue_scripts', array(__CLASS__,'enqueue_search_styles'));	 		
	 	}

	}

	public static function enqueue_dashicons() {
		wp_enqueue_style('dashicons');
	}

	public static function enqueue_search_styles() {
		wp_enqueue_style('search-menu', plugins_url('styles/menu-search.css',dirname(__FILE__)), array(), '1.0');
	}


	public static function enqueue_sidr_styles() {
		wp_enqueue_style('jquery-sidr', plugins_url('styles/jquery.sidr.dark.css',dirname(__FILE__)), array(), '1.2.1');
	}

	public static function enqueue_sidr_scripts() {
		wp_enqueue_script('jquery-sidr', plugins_url('scripts/jquery.sidr.min.js',dirname(__FILE__)), array('jquery'), '1.2.1', true);
	}

	public static function save_options($options) {
   			return Genesis_Club_Options::save_options(array('menu' => $options)) ;
	}

	public static function get_options() {
    	return Genesis_Club_Options::get_option('menu');
    }
	
	public static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;
    }


	public static function maybe_add_search_form($items, $args) {
      $search_menu = self::get_option('search_menu');
      if (($args->theme_location == $search_menu)
      || (('header' == $search_menu) && has_filter('wp_nav_menu', 'genesis_header_menu_wrap')))  {
			 			add_filter( 'genesis_search_text',  array(__CLASS__, 'set_search_placeholder'));
         return $items . sprintf('<li class="searchbox%2$s">%1$s</li>', get_search_form( false ), self::get_option('search_button') ? '' : ' nobutton' ) ;	         
      } else {
  		return $items;
	}
	}


	public static function set_search_placeholder($content) {
		return self::get_option('search_text');
	} 

	public static function add_responsive_menu($content, $menu, $args) {
		if (strpos($content, self::$is_html5 ? '<nav class="nav-primary' : '<div id="nav') !== FALSE) 
			return self::maybe_prefix_responsive_menu($content, $menu, 'primary') ;
		elseif (strpos($content, self::$is_html5 ? '<nav class="nav-secondary'  : '<div id="subnav')  !== FALSE)  
			return self::maybe_prefix_responsive_menu($content, $menu, 'secondary') ;	
		else 
			return $content;
	}

	public static function add_responsive_widget_menu($content, $args) {
		if (has_filter('wp_nav_menu', 'genesis_header_menu_wrap'))
			return self::maybe_prefix_responsive_menu($content, $content, 'header') ;		
		else
			return $content;
	}

	private static function maybe_prefix_responsive_menu($content, $menu, $option) {
		$resp_menu  = self::get_option($option);
		$hamburger = sprintf('<div class="gc-responsive-menu-icon gcm-resp-%1$s"><div class="dashicons dashicons-menu"></div></div>', $resp_menu);
      $strip_menu = preg_replace('#\s(id|class)="[^"]+"#', '',  strip_tags($menu,'<ul><li><a><span>'));
		switch ($resp_menu) {
			case 'left':
				self::$side_menu_left .= $strip_menu; 
				$prefix = $hamburger;
				break;
			case 'right': 
				self::$side_menu_right .= $strip_menu; 
				$prefix = $hamburger;
				break;
			case 'below': 
				self::$below_menu = true;
				$prefix = $hamburger;
				break;
			default: $prefix ='';
		}
		return $prefix . $content;
	}

	private static function check_color($color) {
		return preg_match('/^#?[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $color) ? $color : '#888' ;
	}

	private static function check_size($item, $default) {
		return (is_numeric($item) && ($item >= 1)) ? $item : $default;		
	}

	private static function check_unit($item) {
		$str = str_replace(' ','',trim($item));
		$suffix='';
		if (empty($str) || ('auto'==$str)
		|| (strstr($str, 'px') !== false) 
		|| (strstr($str, '%') !== false)) 
			return $str;
		 else
			return $str. 'px';
	}	

	public static function print_styles() { 
		$minimum_device_width = self::check_unit(self::get_option('threshold'));
		$color = self::check_color(self::get_option('icon_color'));
		$rsize = self::check_size(self::get_option('icon_size'),2.4);
		$psize = round($rsize*10);	
    	print <<< CSS
<style type="text/css" media="screen"> 
.gc-responsive-menu-icon { display: none; text-align: center; }
.gc-responsive-menu-icon.gcm-resp-left.gcm-open { text-align: left; }
.gc-responsive-menu-icon.gcm-resp-right.gcm-open { text-align: right; }
.gc-responsive-menu-icon .dashicons { color: {$color}; font-size: {$psize}px; font-size: {$rsize}rem; height: {$psize}px; height: {$rsize}rem; width: {$psize}px;  width: {$rsize}rem;}
@media only screen and (max-width: {$minimum_device_width}) {   
.gc-responsive-menu { display: none; }
.gc-responsive-menu-icon { display: block; }
} 		
</style>

CSS;
		}
	
    public static function print_scripts () {
		if (self::$below_menu) self::print_below_scripts();
		if (self::$side_menu_left) self::print_side_scripts('left',self::$side_menu_left);
		if (self::$side_menu_right)	self::print_side_scripts('right', self::$side_menu_right);
		$icon_color = self::get_option('icon_color');
		if (empty($icon_color)) self::print_dynamic_color_script();			
	}

    private static function print_side_scripts($side, $menu) {
			$minimum_device_width = self::get_option('threshold');
			printf('<div id="sidr-%1$s"><nav class="nav-sidr">%2$s</nav></div>', $side, $menu);
			print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($) {
	$(".gc-responsive-menu-icon.gcm-resp-{$side}").next().addClass('gc-responsive-menu');
    $(".gc-responsive-menu-icon.gcm-resp-{$side}").sidr({
      name: "sidr-{$side}",
      source: "#sidr-{$side}",
      side: "{$side}"
    });   
	$(".gc-responsive-menu-icon.gcm-resp-{$side}" ).click(function() {
  		$(this).toggleClass("gcm-open");
	});
	$(window).resize(function(){ 
		if(window.innerWidth > {$minimum_device_width}) { 
			$.sidr("close", "sidr-{$side}");
		}
	});
});
//]]>
</script>
	
SCRIPT;
    }	

    private static function print_below_scripts() {
		$minimum_device_width = self::get_option('threshold');
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
	$(".gc-responsive-menu-icon.gcm-resp-below").next().addClass('gc-responsive-menu');
	$(".gc-responsive-menu-icon.gcm-resp-below").click(function(){ $(this).next().slideToggle();});
	$(window).resize(function(){ if(window.innerWidth > {$minimum_device_width}) { $(".genesis-nav-menu").removeAttr("style");}});
});
//]]>
</script>
	
SCRIPT;
    }	

    private static function print_dynamic_color_script() {	
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
	$(".gc-responsive-menu-icon").each( function(index) {
			var color = $(this).next().find('a:first-child').css('color');
			$(this).find('.dashicons').css("color",color);
	});
});
//]]>
</script>
	
SCRIPT;
    }	

}
