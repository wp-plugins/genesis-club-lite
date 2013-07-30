<?php
class GenesisClubDisplay {
    const CLASSNAME = 'GenesisClubDisplay'; //this class
	const DOMAIN = 'GenesisClub';
	const TOP_POST_SIDEBAR_ID = 'genesis-before-entry-content';
	const BOTTOM_POST_SIDEBAR_ID = 'genesis-after-entry-content';
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
		'before_entry_content' => false,
		'after_entry_content' => false,
		'facebook_likebox_bgcolor' => ''
	);
	
	protected static $accordion = false; 

	static function init() {
		GenesisClubOptions::init(self::$defaults);
		add_action('widgets_init', array(self::CLASSNAME,'register_sidebars'));
		if (!is_admin())  add_action('wp',array(self::CLASSNAME,'prepare'));
	}	

    static function register_sidebars() {
    	if (GenesisClubOptions::get_option('before_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::TOP_POST_SIDEBAR_ID,
				'name'	=> __( 'Before Entry Content', self::DOMAIN ),
				'description' => __( 'Area before the entry content for things like adding social media icons for likes and shares', self::DOMAIN)
			) );
    	if (GenesisClubOptions::get_option('after_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::BOTTOM_POST_SIDEBAR_ID,
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
						array(self::CLASSNAME, 'show_top_sidebar')); 
				if (GenesisClubOptions::get_option('after_entry_content'))  
					add_action( GenesisClubOptions::is_html5() ? 'genesis_after_entry_content' :'genesis_after_post_content', 
						array(self::CLASSNAME, 'show_bottom_sidebar'));
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

	static function show_top_sidebar() { self::show_sidebar(self::TOP_POST_SIDEBAR_ID); }

	static function show_bottom_sidebar() { self::show_sidebar(self::BOTTOM_POST_SIDEBAR_ID); }

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
     });
</script>
SETBGCOLOR;
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
}
?>