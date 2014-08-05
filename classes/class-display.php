<?php
class Genesis_Club_Display {
	const BEFORE_ARCHIVE_SIDEBAR_ID = 'genesis-before-archive';
	const AFTER_ARCHIVE_SIDEBAR_ID = 'genesis-after-archive';
	const BEFORE_ENTRY_SIDEBAR_ID = 'genesis-before-entry-content';
	const AFTER_ENTRY_SIDEBAR_ID = 'genesis-after-entry-content';
    const PAGE_HIDER_METAKEY = '_genesis_page_not_on_404';
    const HIDE_TITLE_METAKEY = '_genesis_club_hide_title';
	const BGCOLOR_KEY = 'facebook_likebox_bgcolor';
	const BORDER_KEY = 'facebook_likebox_border';
	
	protected static $defaults  = array(
		'remove_blog_title' => false,
		'logo' => '',
		'read_more_text' => '',
		'comment_invitation' => '',
		'breadcrumb_prefix' => 'You are here: ',
		'breadcrumb_archive' => 'Archives for ',
		'postinfo_shortcodes' => '',
		'postmeta_shortcodes' => '',
		'no_page_postmeta' => false,
		'no_archive_postmeta' => false,
		'before_archive' => false,
		'after_archive' => false,
		'before_entry_content' => false,
		'after_entry_content' => false,
		'facebook_app_id' => '',
		'facebook_likebox_bgcolor' => ''
	);
	
	public static function init() {
		Genesis_Club_Options::init(array('display' => self::$defaults));		
		add_action('widgets_init', array(__CLASS__,'register_sidebars'));
		add_action('widgets_init', array(__CLASS__,'register_widgets'));			
		if (!is_admin()) {
			add_action('parse_query', array(__CLASS__,'parse_query'));
			add_action('wp', array(__CLASS__,'prepare'));
			add_action('wp_enqueue_scripts', array(__CLASS__,'enqueue_styles'));
		}
	}	

    public static function register_sidebars() {
    	if (self::get_option('before_archive'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ARCHIVE_SIDEBAR_ID,
				'name'	=> __( 'Before Archive', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area at the top of the archive for adding an introductory slider', GENESIS_CLUB_DOMAIN)
			) );
    	if (self::get_option('after_archive'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ARCHIVE_SIDEBAR_ID,
				'name'	=> __( 'After Archive', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area at the end of the archive for adding things like call to actions or ads', GENESIS_CLUB_DOMAIN),	
			) );
    	if (self::get_option('before_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ENTRY_SIDEBAR_ID,
				'name'	=> __( 'Before Entry Content', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area before the entry content for things like adding social media icons for likes and shares', GENESIS_CLUB_DOMAIN)
			) );
    	if (self::get_option('after_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ENTRY_SIDEBAR_ID,
				'name'	=> __( 'After Entry Content', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area below the entry content for adding things like social media icons for likes and shares', GENESIS_CLUB_DOMAIN),	
			) );
    }

	public static function register_widgets() {
		register_widget( 'Genesis_Club_Post_Image_Gallery_Widget' );
		register_widget( 'Genesis_Club_Facebook_Likebox_Widget' );		
	}	

	public static function enqueue_styles() {
		wp_enqueue_style('dashicons');
 	}

	public static function parse_query() {

			if (is_404()) {
				add_filter('wp_list_pages_excludes', array(__CLASS__,'excluded_pages'));	
				add_filter('getarchives_where', array(__CLASS__,'excluded_posts'),10,2);	
			}

			if (is_search()) {
				add_filter( 'posts_where' , array(__CLASS__,'excluded_posts'),10,2 );
			}
												
	}

	public static function prepare() {
		$post_id = Genesis_Club_Options::get_post_id(); //get post/page id
		$is_landing = Genesis_Club_Options::is_landing_page();

		if (self::get_option('remove_blog_title')) {
			remove_all_actions('genesis_site_description');
			add_filter ('genesis_seo_title', array(__CLASS__,'blog_title_notext'), 11, 3);
		}
			
		if (self::get_option('read_more_text')) { 
			add_filter('excerpt_more', array(__CLASS__,'read_more_link') );
			add_filter('get_the_content_more_link', array(__CLASS__,'read_more_link' ));
			add_filter('the_content_more_link', array(__CLASS__,'read_more_link') );			
			add_filter('genesis_grid_loop_args', array(__CLASS__,'set_read_more_text'));
			//Add a Read More for hand crafted excerpts
			if (is_archive()) add_filter('the_excerpt', array(__CLASS__,'add_read_more_link'),30); 
		}

	 	if (self::get_option('comment_invitation')) {
	 		add_filter(Genesis_Club_Options::is_html5()  ? 'comment_form_defaults' :'genesis_comment_form_args', 
	 			array(__CLASS__,'comment_form_args') );	
		}
			
		if (is_archive() || is_singular()) {
			add_filter( 'genesis_breadcrumb_args', array(__CLASS__, 'filter_breadcrumb_args' ) );
		}

	 	if (self::get_option(self::BGCOLOR_KEY) || self::get_option(self::BORDER_KEY))  {
			add_action ('wp_print_footer_scripts', array(__CLASS__, 'change_likebox_bgcolor' ),100 );  				
		}
			
		if (is_single()) { //insert widgets before and after entry content on posts
			if (self::get_option('before_entry_content'))  
				add_action( Genesis_Club_Options::is_html5() ? 'genesis_before_entry_content' :'genesis_after_post_title', 
					array(__CLASS__, 'show_before_entry_sidebar')); 
			if (self::get_option('after_entry_content'))  
				add_action( Genesis_Club_Options::is_html5() ? 'genesis_after_entry_content' :'genesis_after_post_content', 
					array(__CLASS__, 'show_after_entry_sidebar'));
		}
			
		if (is_archive()) { //insert widgets before and after entry archives 
			add_filter ('genesis_term_intro_text_output','do_shortcode',11); //convert shortcode in toppers
			if (self::get_option('before_archive'))  
				add_action(  'genesis_before_loop' , 
					array(__CLASS__, 'show_before_archive_sidebar')); 
			if (self::get_option('after_archive'))  
				add_action('genesis_after_loop', 
					array(__CLASS__, 'show_after_archive_sidebar'));

		 	if (self::get_option('no_archive_postmeta')) { //remove postinfo and postmeta on archives
		 		if (Genesis_Club_Options::is_html5()) {
					remove_action( 'genesis_entry_header', 'genesis_post_info', 12 ); 
					remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
				} else {
					remove_action( 'genesis_before_post_content', 'genesis_post_info' );
					remove_action( 'genesis_after_post_content', 'genesis_post_meta' );	 			
				}
			}
		}

		if (is_singular()) {  //remove title
			if (get_post_meta($post_id, Genesis_Club_Display::HIDE_TITLE_METAKEY, true))
				add_filter('genesis_post_title_text', '__return_empty_string', 100);
		}
		
		if (is_singular()) {  //remove or replace post info 
		 	if (is_page() && self::get_option('no_page_postmeta')) { //remove postinfo on pages
		 		if (Genesis_Club_Options::is_html5()) 
					remove_action( 'genesis_entry_header', 'genesis_post_info', 12 ); 
				else  
					remove_action( 'genesis_before_post_content', 'genesis_post_info' );		 			
			}
			elseif ( ! $is_landing && ($postinfo = self::get_option('postinfo_shortcodes'))) { //replace shortcodes
		 		if (Genesis_Club_Options::is_html5()) {
					remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
					add_action ('genesis_entry_header', array(__CLASS__,'post_info'),12); 
				} else { 
					remove_action( 'genesis_before_post_content', 'genesis_post_info' );		 			
					add_action( 'genesis_before_post_content', array(__CLASS__,'post_info') );	
				}
	 		}
	 	}
		 	
		if (is_single()) {  
		 	if ($postmeta = self::get_option('postmeta_shortcodes')) { //replace postmeta on posts 
				$hook = Genesis_Club_Options::is_html5() ?  'genesis_entry_footer' : 'genesis_after_post_content';
				remove_action( $hook, 'genesis_post_meta');
				add_action ($hook, array(__CLASS__,'post_meta')); 
			}
			if (is_active_widget( false, false, 'genesis-club-post-image-gallery', false )) add_thickbox();				
		}

		if (is_active_widget( false, false, 'genesis-club-likebox', false )) {
			add_action( 'genesis_before', array(__CLASS__,'add_fb_root') );			
		}
		
		if ( $is_landing)  {//disable breadcrumbs on landing pages
			add_filter('genesis_pre_get_option_breadcrumb_page', '__return_false');	
		}
	}

	public static function get_defaults() {
    	return self::$defaults;
    }

	public static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;
    }

	public static function get_options() {
    	return Genesis_Club_Options::get_option('display');
    }
	
	public static function save_options($options) {
   			return Genesis_Club_Options::save_options(array('display' => $options)) ;
	}

	public static function blog_title_notext($title, $inside, $wrap) {
		$logo = ($logo = self::get_option('logo'))  ? sprintf('<img src="%1$s" alt="Logo"/>',$logo) : '';
		$inside = sprintf( '<a href="%1$s" title="%2$s" style="text-indent:0;background:none">%3$s</a>', 
				trailingslashit( home_url() ), esc_attr( get_bloginfo('name')), $logo ) ;
		$xtml = sprintf( '<div id="title">%1$s</div>', $inside);
		$html5 = sprintf( '<div class="site-title">%1$s</div>', $inside);
		return Genesis_Club_Options::is_genesis2() ?
			genesis_markup( array(
				'html5'   => $html5,
				'xhtml'   => $xtml,
				'context' => '',
				'echo'    => false) ) :
			genesis_markup($html5, $xtml, false ) ;
	}  
	public static function strip_rel_author ($content, $args) { return str_replace(' rel="author"','',$content); }
	public static function show_before_entry_sidebar() { self::show_sidebar(self::BEFORE_ENTRY_SIDEBAR_ID); }
	public static function show_after_entry_sidebar() { self::show_sidebar(self::AFTER_ENTRY_SIDEBAR_ID); }
	public static function show_before_archive_sidebar() { self::show_sidebar(self::BEFORE_ARCHIVE_SIDEBAR_ID); }
	public static function show_after_archive_sidebar() { self::show_sidebar(self::AFTER_ARCHIVE_SIDEBAR_ID); }

	private static function show_sidebar($sidebar) {
		if ( is_active_sidebar( $sidebar) ) {
			$tag = Genesis_Club_Options::is_html5() ? 'aside' : 'div';
			printf ('<%1$s class="widget-area custom-post-sidebar %2$s">',$tag,$sidebar);
			dynamic_sidebar( $sidebar );
			printf ('</%1$s>',$tag);
		}
	}

	public static function excluded_pages($content) {
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = '1'",self::PAGE_HIDER_METAKEY));
        if ($post_ids && is_array($post_ids)) return (array)$content + $post_ids; 
		return $content;
	} 
	
	public static function excluded_posts($content, $args) {
		global $wpdb;
        $post_ids = $wpdb->get_col( $wpdb->prepare(
        	"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = '1'",self::PAGE_HIDER_METAKEY));
        if ($post_ids && is_array($post_ids)) $content .= sprintf (' AND ID NOT IN (%1$s)', implode(',',$post_ids));
		return $content;
	} 	
	
	public static function add_read_more_link($content) {
		if (strpos($content, 'more-link') === FALSE) $content .=  self::read_more_link();
		return $content; 
 	}

	public static function read_more_link() {
 		return sprintf('&nbsp;<a class="more-link" href="%1$s">%2$s</a>',  get_permalink(),  self::get_option('read_more_text'));
 	}

	public static function set_read_more_text($args) {
		$args['more'] = self::get_option('read_more_text');
		return $args;
	}

	public static function comment_form_args($args) {
		$args['title_reply'] = self::get_option('comment_invitation');
		return $args;
	}	

	public static function change_likebox_bgcolor(){
		$bgcolor = self::get_option('facebook_likebox_bgcolor');
		if (!$bgcolor 
		|| (!is_active_widget(false, false, 'facebook-likebox') 
		   && !is_active_widget(false, false, 'genesis-club-likebox'))) 
			return false;
		print <<< LIKEBOXBGCOLOR
<script type="text/javascript">
     jQuery(document).ready(function(){
     	jQuery('.fb-like-box').css({"background-color":"{$bgcolor}"});
     });
</script>
LIKEBOXBGCOLOR;
 	}
 	
 	public static function post_info() {
 		$post_info = do_shortcode(str_replace('[]','',self::get_option('postinfo_shortcodes')));
		if ($post_info) 
 			genesis_markup( array(
				'html5' => sprintf( '<p class="entry-meta">%s</p>', $post_info ),
				'xhtml' => sprintf( '<div class="post-info">%s</div>', $post_info ),
			) );
 	}

 	public static function post_meta() {
 		$post_meta = do_shortcode(str_replace('[]','',self::get_option('postmeta_shortcodes')));
		if ($post_meta) 
 			genesis_markup( array(
				'html5' => sprintf( '<p class="entry-meta">%s</p>', $post_meta ),
				'xhtml' => sprintf( '<div class="post-meta">%s</div>', $post_meta ),
			) );
 	}

	public static function filter_breadcrumb_args( $args ) {
		$prefix = self::get_option('breadcrumb_prefix');
		$label = self::get_option('breadcrumb_archive');
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

	public static function add_fb_root() {
		$app_id = self::get_option('facebook_app_id');
		if ($app_id) $app_id = sprintf('&appId=%1$s',$app_id);
		print <<< SCRIPT
			<div id="fb-root"></div>
<script type="text/javascript">(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&status=0{$app_id}";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
SCRIPT;
	}
		
}
