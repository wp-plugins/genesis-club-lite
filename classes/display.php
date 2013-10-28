<?php
class GenesisClubDisplay {
    const CLASSNAME = 'GenesisClubDisplay'; //this class
	const DOMAIN = 'GenesisClub';
	const BEFORE_ARCHIVE_SIDEBAR_ID = 'genesis-before-archive';
	const AFTER_ARCHIVE_SIDEBAR_ID = 'genesis-after-archive';
	const BEFORE_ENTRY_SIDEBAR_ID = 'genesis-before-entry-content';
	const AFTER_ENTRY_SIDEBAR_ID = 'genesis-after-entry-content';
    const PAGE_HIDER_METAKEY = '_genesis_page_not_on_404';
	const BGCOLOR_KEY = 'facebook_likebox_bgcolor';
	const BORDER_KEY = 'facebook_likebox_border';
	
	protected static $defaults  = array(
		'remove_blog_title' => false,
		'logo' => '',
		'read_more_text' => '',
		'comment_invitation' => '',
		'breadcrumb_prefix' => 'You are here: ',
		'breadcrumb_archive' => 'Archives for ',
		'before_archive' => false,
		'after_archive' => false,
		'before_entry_content' => false,
		'after_entry_content' => false,
		'facebook_likebox_bgcolor' => '',
		'responsive_menu_threshold' => '',
		'responsive_menu_icon_color' => '',
		'postinfo_shortcodes' => '',
		'postmeta_shortcodes' => ''	
	);
	
	static function init() {
		GenesisClubOptions::init(self::$defaults);
		add_action('widgets_init', array(self::CLASSNAME,'register_sidebars'));
		if (!is_admin())  add_action('wp',array(self::CLASSNAME,'prepare'));
	}	

    static function register_sidebars() {
    	if (GenesisClubOptions::get_option('before_archive'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ARCHIVE_SIDEBAR_ID,
				'name'	=> __( 'Before Archive', self::DOMAIN ),
				'description' => __( 'Area at the top of the archive for adding an introductory slider', self::DOMAIN)
			) );
    	if (GenesisClubOptions::get_option('after_archive'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ARCHIVE_SIDEBAR_ID,
				'name'	=> __( 'After Archive', self::DOMAIN ),
				'description' => __( 'Area at the end of the archive for adding things like call to actions or ads', self::DOMAIN),	
			) );
    	if (GenesisClubOptions::get_option('before_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ENTRY_SIDEBAR_ID,
				'name'	=> __( 'Before Entry Content', self::DOMAIN ),
				'description' => __( 'Area before the entry content for things like adding social media icons for likes and shares', self::DOMAIN)
			) );
    	if (GenesisClubOptions::get_option('after_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ENTRY_SIDEBAR_ID,
				'name'	=> __( 'After Entry Content', self::DOMAIN ),
				'description' => __( 'Area below the entry content for adding things like social media icons for likes and shares', self::DOMAIN),	
			) );
    }

	static function prepare() {
			if (GenesisClubOptions::get_option('remove_blog_title')) {
				remove_all_actions('genesis_site_description');
				add_filter ('genesis_seo_title', array(self::CLASSNAME,'blog_title_notext'), 11, 3);
			}
			
		 	if (GenesisClubOptions::get_option('read_more_text')) { 
				add_filter('excerpt_more', array(self::CLASSNAME,'read_more_link') );
				add_filter('get_the_content_more_link', array(self::CLASSNAME,'read_more_link' ));
				add_filter('the_content_more_link', array(self::CLASSNAME,'read_more_link') );			
				add_filter('genesis_grid_loop_args', array(self::CLASSNAME,'set_read_more_text'));
				//Add a Read More for hand crafted excerpts
				if (is_archive()) add_filter('the_excerpt', array(self::CLASSNAME,'add_read_more_link'),30); 
			}

		 	if (GenesisClubOptions::get_option('comment_invitation')) 
		 		add_filter('genesis_comment_form_args', array(self::CLASSNAME,'comment_form_args') );	

		 	if (is_archive() || is_singular()) 
				add_filter( 'genesis_breadcrumb_args', array(self::CLASSNAME, 'filter_breadcrumb_args' ) );

		 	if (GenesisClubOptions::get_option(self::BGCOLOR_KEY) || GenesisClubOptions::get_option(self::BORDER_KEY)) 
				add_action ('wp_print_footer_scripts', array(self::CLASSNAME, 'change_likebox_bgcolor' ),100 );  				

			//insert post sidebars
			if (is_single()) {
				if (GenesisClubOptions::get_option('before_entry_content'))  
					add_action( GenesisClubOptions::is_html5() ? 'genesis_before_entry_content' :'genesis_after_post_title', 
						array(self::CLASSNAME, 'show_before_entry_sidebar')); 
				if (GenesisClubOptions::get_option('after_entry_content'))  
					add_action( GenesisClubOptions::is_html5() ? 'genesis_after_entry_content' :'genesis_after_post_content', 
						array(self::CLASSNAME, 'show_after_entry_sidebar'));
			}
			if (is_archive()) {
				if (GenesisClubOptions::get_option('before_archive'))  
					add_action(  'genesis_before_loop' , 
						array(self::CLASSNAME, 'show_before_archive_sidebar')); 
				if (GenesisClubOptions::get_option('after_archive'))  
					add_action('genesis_after_loop', 
						array(self::CLASSNAME, 'show_after_archive_sidebar'));
			}

		 	if (is_singular() || is_home() || is_archive()) { 
		 		if ($postinfo = GenesisClubOptions::get_option('postinfo_shortcodes')) 
					add_filter ('genesis_post_info', array(self::CLASSNAME,'filter_postinfo'));  				
		 		if ($postmeta = GenesisClubOptions::get_option('postmeta_shortcodes')) 
					add_filter ('genesis_post_meta', array(self::CLASSNAME,'filter_postmeta')); 
			}

			if (is_404()) {
				add_filter('wp_list_pages_excludes', array(self::CLASSNAME,'excluded_pages'));	
				add_filter('getarchives_where', array(self::CLASSNAME,'excluded_posts'),10,2);	
			}
			
			if (defined('AUTHORSURE')) { //disable Genesis authorship as AuthorSure gives better control
				remove_filter( 'user_contactmethods', 'genesis_user_contactmethods' ); //avoid collisions 
				//kill off link in the head and rel="author" link in the byline 
				remove_action( 'wp_head', 'genesis_rel_author' ); 
				add_filter('genesis_post_author_posts_link_shortcode', array(self::CLASSNAME, 'strip_rel_author'),10,2); 
			}
												
			if (class_exists('Simple_Social_Icons_Widget'))
				self::simple_social_icons_init();		
				
			if (GenesisClubOptions::get_option('responsive_menu_threshold')) {
				add_action('wp_print_styles', array(self::CLASSNAME, 'print_responsive_menu_styles'));
				add_action('wp_print_footer_scripts', array(self::CLASSNAME, 'print_responsive_menu_scripts'));
			}						
	}
 
	static function blog_title_notext($title, $inside, $wrap) {
		$logo = ($logo = GenesisClubOptions::get_option('logo'))  ? sprintf('<img src="%1$s" alt="Logo"/>',$logo) : '';
		$inside = sprintf( '<a href="%1$s" title="%2$s" style="text-indent:0;background:none">%3$s</a>', 
				trailingslashit( home_url() ), esc_attr( get_bloginfo('name')), $logo ) ;
		$xtml = sprintf( '<div id="title">%1$s</div>', $inside);
		$html5 = sprintf( '<div class="site-title">%1$s</div>', $inside);
		return GenesisClubOptions::is_genesis2() ?
			genesis_markup( array(
				'html5'   => $html5,
				'xhtml'   => $xtml,
				'context' => '',
				'echo'    => false) ) :
			genesis_markup($html5, $xtml, false ) ;
	}
  
	static function strip_rel_author ($content, $args) { return str_replace(' rel="author"','',$content); }
	static function show_before_entry_sidebar() { self::show_sidebar(self::BEFORE_ENTRY_SIDEBAR_ID); }
	static function show_after_entry_sidebar() { self::show_sidebar(self::AFTER_ENTRY_SIDEBAR_ID); }
	static function show_before_archive_sidebar() { self::show_sidebar(self::BEFORE_ARCHIVE_SIDEBAR_ID); }
	static function show_after_archive_sidebar() { self::show_sidebar(self::AFTER_ARCHIVE_SIDEBAR_ID); }

	static function show_sidebar($sidebar) {
		if ( is_active_sidebar( $sidebar) ) {
			$tag = GenesisClubOptions::is_html5() ? 'aside' : 'div';
			printf ('<%1$s class="widget-area custom-post-sidebar %2$s">',$tag,$sidebar);
			dynamic_sidebar( $sidebar );
			printf ('</%1$s>',$tag);
		}
	}

	static function get_page_hider_metakey() {
		return self::PAGE_HIDER_METAKEY;
	} 

	static function excluded_pages($content) {
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = '1'",self::PAGE_HIDER_METAKEY));
        if ($post_ids && is_array($post_ids)) return (array)$content + $post_ids; 
		return $content;
	} 
	
	static function excluded_posts($content, $args) {
		global $wpdb;
        $post_ids = $wpdb->get_col( $wpdb->prepare(
        	"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = '1'",self::PAGE_HIDER_METAKEY));
        if ($post_ids && is_array($post_ids)) $content .= sprintf (' AND ID NOT IN (%1$s)', implode(',',$post_ids));
		return $content;
	} 
	
	static function add_read_more_link($content) {
		if (strpos($content, 'more-link') === FALSE) $content .=  self::read_more_link();
		return $content; 
 	}

	static function read_more_link() {
 		return sprintf('&nbsp;<a class="more-link" href="%1$s">%2$s</a>',  get_permalink(),  GenesisClubOptions::get_option('read_more_text'));
 	}

	static function set_read_more_text($args) {
		$args['more'] = GenesisClubOptions::get_option('read_more_text');
		return $args;
	}

	static function comment_form_args($args) {
		$args['title_reply'] = GenesisClubOptions::get_option('comment_invitation');
		return $args;
	}	

	static function change_likebox_bgcolor(){
		$bgcolor = GenesisClubOptions::get_option('facebook_likebox_bgcolor');
		if (!$bgcolor || !is_active_widget(false, false, 'facebook-likebox')) return false;
		$bordercolor = GenesisClubOptions::get_option('facebook_likebox_border','#AAAAAA');
		print <<< SETBGCOLOR
<script type="text/javascript">
     jQuery(document).ready(function(){
     	jQuery('.widget_facebook_likebox iframe').css( { 'background-color':'{$bgcolor}', 'border':'1px solid {$bordercolor}'});
     	jQuery('.widget_genesis-club-likebox iframe').css( { 'background-color':'{$bgcolor}', 'border':'1px solid {$bordercolor}'});
     });
</script>
SETBGCOLOR;
 	}
 	
 	static function filter_postinfo($content) {
 		return str_replace('[]','',GenesisClubOptions::get_option('postinfo_shortcodes'));
 	}

 	static function filter_postmeta($content) {
		 return str_replace('[]','',GenesisClubOptions::get_option('postmeta_shortcodes'));
 	}

	static function filter_breadcrumb_args( $args ) {
		$prefix = GenesisClubOptions::get_option('breadcrumb_prefix');
		$label = GenesisClubOptions::get_option('breadcrumb_archive');
		$label = trim($label).'&nbsp;';
	    $args['labels']['author']        = $label;
	    $args['labels']['category']      = $label;
	    $args['labels']['tag']           = $label;
	    $args['labels']['date']          = $label;
	    $args['labels']['tax']           = $label;
	    $args['labels']['post_type']     = $label; 
	    $args['labels']['prefix']        = trim($prefix).'&nbsp;';
		return $args;
	} 

	static function simple_social_icons_init() {
		global $wp_widget_factory;	
		if ($obj = $wp_widget_factory->widgets['Simple_Social_Icons_Widget']) {
			remove_action('wp_head', array($obj,'css'));
			require_once(dirname(__FILE__).'/icons-widget.php');
			$new_obj = self::recast_object($obj,'GenesisClubIconsWidget'); //recast as an enhanced widget
			add_action('wp_print_styles', array($new_obj,'css'));	//improved widget allows multiple instances per page, each with its own CSS				
		}
	}

	static function recast_object($instance, $className) {
    	return unserialize(sprintf(
    	    'O:%d:"%s"%s',
    	    strlen($className),
    	    $className,
    	    strstr(strstr(serialize($instance), '"'), ':')
    	));
	}

	public static function check_color($color) {
		return preg_match('/^#?[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $color) ? $color : '#888' ;
	}

	public static function check_unit($item) {
		$str = str_replace(' ','',trim($item));
		$suffix='';
		if (empty($str) || ('auto'==$str)
		|| (strstr($str, 'px') !== false) 
		|| (strstr($str, '%') !== false)) 
			return $str;
		 else
			return $str. 'px';
	}	

	function print_responsive_menu_styles() { 
		$minimum_device_width = self::check_unit(GenesisClubOptions::get_option('responsive_menu_threshold'));
		$responsive_menu_icon_color = self::check_color(GenesisClubOptions::get_option('responsive_menu_icon_color'));
    	print <<< CSS
<style type="text/css" media="screen"> 
.gc-responsive-menu-icon { display: none; text-align: center;  }
.border-menu { display: inline-block;  content: ""; cursor: pointer;
  margin: 0.5em; font-size: 1.5em; width: 1em; height: 0.125em;  
  border-top: 0.375em double {$responsive_menu_icon_color}; border-bottom: 0.125em solid {$responsive_menu_icon_color};
}
@media only screen and (max-width: {$minimum_device_width}) {   
.gc-responsive-menu { display: none; }
.gc-responsive-menu-icon { display: block; }
} 		
</style>

CSS;
		}
	
    static function print_responsive_menu_scripts () {
		$minimum_device_width = GenesisClubOptions::get_option('responsive_menu_threshold');
		$responsive_menu_icon_color = GenesisClubOptions::get_option('responsive_menu_icon_color');
		$dynamic_color = empty($responsive_menu_icon_color) ? 1 : 0;
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
	$(".genesis-nav-menu").addClass("gc-responsive-menu").before('<div class="gc-responsive-menu-icon"><div class="border-menu"></div></div>');
	$(".gc-responsive-menu-icon").click(function(){ $(this).next().slideToggle();});
	$(window).resize(function(){ if(window.innerWidth > {$minimum_device_width}) { $(".genesis-nav-menu").removeAttr("style");}});
	if ({$dynamic_color}) $(".gc-responsive-menu-icon").each( function(index) {
			var color = $(this).next().find('a:first-child').css('color');
			$(this).find('.border-menu').css("border-bottom-color",color).css("border-top-color",color);
	});
});
//]]>
</script>
	
SCRIPT;
    }	
}
?>