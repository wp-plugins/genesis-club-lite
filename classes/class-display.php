<?php
class Genesis_Club_Display {
	const BEFORE_CONTENT_SIDEBAR_ID = 'genesis-before-content-sidebar-wrap';
	const BEFORE_ARCHIVE_SIDEBAR_ID = 'genesis-before-archive';
	const BEFORE_ENTRY_SIDEBAR_ID = 'genesis-before-entry';
	const BEFORE_ENTRY_CONTENT_SIDEBAR_ID = 'genesis-before-entry-content';
	const AFTER_ENTRY_CONTENT_SIDEBAR_ID = 'genesis-after-entry-content';
	const AFTER_ENTRY_SIDEBAR_ID = 'genesis-after-entry';
	const AFTER_ARCHIVE_SIDEBAR_ID = 'genesis-after-archive';
	const AFTER_CONTENT_SIDEBAR_ID = 'genesis-after-content-sidebar-wrap';
	const HIDE_FROM_SEARCH_METAKEY = '_genesis_club_hide_from_search';
	const HIDE_TITLE_METAKEY = '_genesis_club_hide_title';
	const HIDE_AFTER_CONTENT_METAKEY = '_genesis_club_hide_after_content';
	const HIDE_AFTER_ENTRY_METAKEY = '_genesis_club_hide_after_entry';
	const HIDE_AFTER_ENTRY_CONTENT_METAKEY = '_genesis_club_hide_after_entry_content';
	const HIDE_BEFORE_CONTENT_METAKEY = '_genesis_club_hide_before_content';
	const HIDE_BEFORE_ENTRY_METAKEY = '_genesis_club_hide_before_entry';
	const HIDE_BEFORE_ENTRY_CONTENT_METAKEY = '_genesis_club_hide_before_entry_content';
    const DISABLE_AUTOP_METAKEY = '_genesis_club_disable_autop';
    const DISABLE_BREADCRUMBS = '_genesis_club_disable_breadcrumbs';
	const BGCOLOR_KEY = 'facebook_likebox_bgcolor';
	const BORDER_KEY = 'facebook_likebox_border';

	const FACEBOOK_IMAGE_SCALE_FACTOR = 1.91;
	const FACEBOOK_FEATURED_IMAGE = 'fb-featured-image';
	const FACEBOOK_ARCHIVE_IMAGE = 'fb-archive-image';
	
	protected static $defaults  = array(
		'remove_blog_title' => false,
		'logo' => '',
		'logo_alt' => 'Logo',
		'read_more_text' => '',
		'comment_invitation' => '',
		'comment_notes_hide' => 0,
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
		'before_entry' => false,
		'after_entry' => false,
		'before_content' => false,
		'after_content' => false,
		'facebook_app_id' => '',
		'facebook_likebox_bgcolor' => '',
		'alt_404_page' => 0,
		'alt_404_status' => 404,
		'css_hacks' => false,
		'disable_emojis' => false,
		'facebook_featured_images' => false,
		'facebook_sized_images' => false,
		'custom_login_enabled' => false, 
		'custom_login_background' => '', 
		'custom_login_logo' => '', 
		'custom_login_button_color' => '', 
		'custom_login_user_label' => 'User Login',
		'excerpt_images_on_front_page' => false,
		'archives' => false,
	);
	
	protected static $is_html5 = false;
	protected static $is_landing = false;
	protected static $post_id = false;
	protected static $og_title = false;
	protected static $og_desc = false;
	protected static $og_image = false;	
	protected static $term_featured_image = false;	
	protected static $postinfo_shortcodes = false;	
	protected static $postmeta_shortcodes = false;	
	
	public static function init() {
		Genesis_Club_Options::init(array('display' => self::$defaults));		
		add_action('widgets_init', array(__CLASS__,'register_sidebars'));
		add_action('widgets_init', array(__CLASS__,'register_widgets'));			
		add_filter('status_header', array(__CLASS__,'status_header'),10,4);	
    	if (self::get_option('facebook_featured_images')) {
         self::set_facebook_featured_image_size();           
    	}
				
		if (!is_admin()) {
			add_action('parse_query', array(__CLASS__,'parse_query'));
			add_action('pre_get_posts', array(__CLASS__,'customize_archive'), 15 );			
			add_action('wp', array(__CLASS__,'prepare'));
		}
		self::custom_login();
	}	

    public static function register_sidebars() {
    	if (self::get_option('before_content'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_CONTENT_SIDEBAR_ID,
				'name'	=> __( 'Before Content After Header', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Full width area below the header and above the content and any primary and secondary sidebars.', GENESIS_CLUB_DOMAIN)
			) );
    	if (self::get_option('before_archive'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ARCHIVE_SIDEBAR_ID,
				'name'	=> __( 'Before Archive', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area at the top of the archive for adding an introductory slider', GENESIS_CLUB_DOMAIN)
			) );
    	if (self::get_option('before_entry'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ENTRY_SIDEBAR_ID,
				'name'	=> __( 'Before Entry', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area before the entry for adding calls to action or ads', GENESIS_CLUB_DOMAIN)
			) );
    	if (self::get_option('before_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::BEFORE_ENTRY_CONTENT_SIDEBAR_ID,
				'name'	=> __( 'Before Entry Content', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area before the post content for things like adding social media icons for likes and shares', GENESIS_CLUB_DOMAIN)
			) );
    	if (self::get_option('after_entry_content'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ENTRY_CONTENT_SIDEBAR_ID,
				'name'	=> __( 'After Entry Content', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area after the post content for adding things like social media icons for likes and shares', GENESIS_CLUB_DOMAIN),	
			) );
    	if (self::get_option('after_entry'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ENTRY_SIDEBAR_ID,
				'name'	=> __( 'After Entry', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area after the entry for adding calls to action or ads', GENESIS_CLUB_DOMAIN),	
			) );
    	if (self::get_option('after_archive'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_ARCHIVE_SIDEBAR_ID,
				'name'	=> __( 'After Archive', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Area at the end of the archive for adding things like call to actions or ads', GENESIS_CLUB_DOMAIN),	
			) );
    	if (self::get_option('after_content'))
			genesis_register_sidebar( array(
				'id' => self::AFTER_CONTENT_SIDEBAR_ID,
				'name'	=> __( 'After Content Before Footer', GENESIS_CLUB_DOMAIN ),
				'description' => __( 'Full width area just above the footer and below the content and any primary and secondary sidebars.', GENESIS_CLUB_DOMAIN)
			) );

    }

	public static function register_widgets() {
		register_widget( 'Genesis_Club_Text_Widget' );		
		register_widget( 'Genesis_Club_Facebook_Likebox_Widget' );		
	}	

	public static function enqueue_styles() {
		wp_enqueue_style('dashicons');
		if (self::get_option('css_hacks')) 
			wp_enqueue_style('genesis-club-display', plugins_url('styles/display.css', dirname(__FILE__)), array(), GENESIS_CLUB_VERSION);
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
		self::$is_html5 = Genesis_Club_Utils::is_html5();
		self::$post_id = Genesis_Club_Utils::get_post_id(); //get post/page id
		self::$is_landing = Genesis_Club_Utils::is_landing_page();

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
	 		add_filter(self::$is_html5 ? 'comment_form_defaults' :'genesis_comment_form_args', 
	 			array(__CLASS__,'comment_form_args'), 20 );	
		}

	 	if (self::get_option('comment_notes_hide')) {
	 		add_filter(self::$is_html5 ? 'comment_form_defaults' :'genesis_comment_form_args', 
	 			array(__CLASS__,'comment_notes_hide'), 20 );	
		}
			
		if (is_archive() || is_singular() || is_front_page() || is_home()) {
			add_filter( 'genesis_breadcrumb_args', array(__CLASS__, 'filter_breadcrumb_args' ) );
		}

	 	if (self::get_option(self::BGCOLOR_KEY) || self::get_option(self::BORDER_KEY))  {
			add_action ('wp_print_footer_scripts', array(__CLASS__, 'change_likebox_bgcolor' ),100 );  				
		}

		if (is_singular()) {  //remove or hide stuff
			if (get_post_meta(self::$post_id, self::HIDE_TITLE_METAKEY, true))
				add_filter('genesis_post_title_text', '__return_empty_string', 100);

			if (get_post_meta(self::$post_id, self::DISABLE_AUTOP_METAKEY, true))
				remove_filter('the_content', 'wpautop');

			if (get_post_meta(self::$post_id, self::DISABLE_BREADCRUMBS, true))
				add_filter( 'genesis_pre_get_option_breadcrumb_' .(is_page() ? 'page':'single'),  '__return_false', 10, 2);
		}

		if (self::get_option('before_content'))
			add_action( 'genesis_before_content_sidebar_wrap', array(__CLASS__, 'show_before_content_sidebar')); 

		if (self::get_option('after_content'))
			add_action( 'genesis_after_content_sidebar_wrap', array(__CLASS__, 'show_after_content_sidebar')); 
								
		if (is_single()) {  //insert widgets before and after entries or entry content 

		if (self::should_show_sidebar('before_entry'))  
			add_action( self::$is_html5 ? 'genesis_before_entry' :'genesis_before_post', array(__CLASS__, 'show_before_entry_sidebar')); 

		if (self::should_show_sidebar('after_entry'))  
			add_action( self::$is_html5 ? 'genesis_after_entry' :'genesis_after_post',  array(__CLASS__, 'show_after_entry_sidebar'));

		if (self::should_show_sidebar('before_entry_content'))  
			add_action( self::$is_html5 ? 'genesis_before_entry_content' :'genesis_after_post_title',  array(__CLASS__, 'show_before_entry_content_sidebar')); 

		if (self::should_show_sidebar('after_entry_content'))  
			add_action( self::$is_html5 ? 'genesis_after_entry_content' :'genesis_after_post_content', array(__CLASS__, 'show_after_entry_content_sidebar'));
		}
			
		if (is_archive()) { //insert widgets before and after entry archives 
			add_filter ('genesis_term_intro_text_output','do_shortcode',11); //convert shortcode in toppers
			if (self::get_option('before_archive'))  
				add_action(  'genesis_before_loop' , 
					array(__CLASS__, 'show_before_archive_sidebar')); 
			if (self::get_option('after_archive'))  
				add_action('genesis_after_loop', 
					array(__CLASS__, 'show_after_archive_sidebar'));
		}

		if (is_front_page()) {
			add_action( self::$is_html5 ? 'genesis_before_entry' :'genesis_before_post', array(__CLASS__, 'maybe_replace_category_images')); 
		}

		if (self::get_option('no_page_postmeta') && (is_page() || is_front_page())) {  //remove postinfo and postmeta on pages
			self::replace_postinfo(false);
			self::replace_postmeta(false);
		} elseif (($postinfo = self::get_option('postinfo_shortcodes')) && (is_single() || ( is_page() && !self::$is_landing)))  {//replace postinfo 
			self::replace_postinfo($postinfo);
		}
		 	
		if (($postmeta = self::get_option('postmeta_shortcodes')) && is_single()) { //replace postmeta on posts 
			self::replace_postmeta($postmeta);
		}

		if (is_single() && is_active_widget( false, false, 'genesis-club-post-image-gallery', false )) {
			add_thickbox();
		}

		if (is_active_widget( false, false, 'genesis-club-likebox', false )) {
			add_action('genesis_before', array(__CLASS__,'add_fb_root') );			
		}
		
		if ( self::$is_landing )  {//disable breadcrumbs on landing pages
			add_filter('genesis_pre_get_option_breadcrumb_page', '__return_false');
		}

		if (self::get_option('alt_404_page')) {
			add_filter('template_redirect', array(__CLASS__,'maybe_redirect_404'),20);
		}

    	if (self::get_option('disable_emojis')) self::disable_emojis();

		add_action('wp_enqueue_scripts', array(__CLASS__,'enqueue_styles'));
	}

	public static function replace_postinfo($post_info = false) {
		if ($post_info && ($post_info != '[]')) {
			self::$postinfo_shortcodes = $post_info;
			add_filter ('genesis_post_info', array(__CLASS__,'post_info')); 
		} else {
			add_action('loop_start', array(__CLASS__, 'delete_postinfo'));     
		}
	}

	public static function delete_postinfo($query) {
		if (self::$is_html5) 
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
		else 
			remove_action( 'genesis_before_post_content', 'genesis_post_info' );		 			
	}

	public static function replace_postmeta($post_meta = false) {
		if ($post_meta && ($post_meta != '[]')) {
         self::$postmeta_shortcodes = $post_meta;
 			add_filter ('genesis_post_meta', array(__CLASS__,'post_meta')); 
      } else {
         add_action('loop_start', array(__CLASS__, 'delete_postmeta'));         
      }
	}
	
	public static function delete_postmeta($query) {
			if (self::$is_html5) 
            	remove_action( 'genesis_entry_footer', 'genesis_post_meta');
 			else 
				remove_action( 'genesis_after_post_content', 'genesis_post_meta' );		 			 			
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

	public static function get_options($cache=true) {
    	return Genesis_Club_Options::get_option('display', $cache);
    }
	
	public static function save_options($options) {
   			return Genesis_Club_Options::save_options(array('display' => $options)) ;
	}

	public static function blog_title_notext($title, $inside, $wrap) {
        $logo_alt = self::get_option('logo_alt');
        $alt = empty( $logo_alt) ? '' :  sprintf(' alt="%1$s"', $logo_alt);
		$logo = self::get_option('logo');
		$url = ($logo && (substr($logo,0,2) == '//')  ? 'http:' : '') . $logo;          
		$logo = filter_var($url, FILTER_VALIDATE_URL) ? sprintf('<img src="%1$s" %2$s/>', $logo, $alt) : $logo;
		if ($logo)
			if (strpos($logo, '[') === FALSE) /* Logo image URL gets wrapped as a clickable link */
				$inside = sprintf( '<a href="%1$s" title="%2$s" style="text-indent:0;background:none">%3$s</a>',
					trailingslashit( home_url() ), esc_attr( get_bloginfo('name')), $logo ) ;
			else  
				$inside = do_shortcode($logo); /* alternatively specify a slider shortcode for a dynamic logo */
		else
			$inside = '';
		$xtml = sprintf( '<div id="title">%1$s</div>', $inside);
		$html5 = sprintf( '<div class="site-title">%1$s</div>', $inside);
		return function_exists('genesis_html5') ?
			genesis_markup( array(
				'html5'   => $html5,
				'xhtml'   => $xtml,
				'context' => '',
				'echo'    => false) ) :
			genesis_markup($html5, $xtml, false ) ;
	}  
	public static function strip_rel_author ($content, $args) { return str_replace(' rel="author"','',$content); }
	public static function show_before_content_sidebar() { self::show_sidebar(self::BEFORE_CONTENT_SIDEBAR_ID); }
	public static function show_before_archive_sidebar() { self::show_sidebar(self::BEFORE_ARCHIVE_SIDEBAR_ID); }
	public static function show_before_entry_sidebar() { self::show_sidebar(self::BEFORE_ENTRY_SIDEBAR_ID); }
	public static function show_before_entry_content_sidebar() { self::show_sidebar(self::BEFORE_ENTRY_CONTENT_SIDEBAR_ID); }
	public static function show_after_entry_content_sidebar() { self::show_sidebar(self::AFTER_ENTRY_CONTENT_SIDEBAR_ID); }
	public static function show_after_entry_sidebar() { self::show_sidebar(self::AFTER_ENTRY_SIDEBAR_ID); }
	public static function show_after_archive_sidebar() { self::show_sidebar(self::AFTER_ARCHIVE_SIDEBAR_ID); }
	public static function show_after_content_sidebar() { self::show_sidebar(self::AFTER_CONTENT_SIDEBAR_ID); }
	
	private static function should_show_sidebar($sidebar) {
		if (is_singular() && (! self::$is_landing) && self::get_option($sidebar)) 
  			if (is_singular('post'))	
			     return ! get_post_meta(get_queried_object_id(), '_genesis_club_hide_'.$sidebar, true);
			else
			    return get_post_meta(get_queried_object_id(),'_genesis_club_show_'.$sidebar, true);
		else
			return false;
	}

	private static function show_sidebar($sidebar) {
		if ( is_active_sidebar( $sidebar) ) {
			$tag = self::$is_html5 ? 'aside' : 'div';
			printf ('<%1$s class="widget-area custom-post-sidebar %2$s">',$tag,$sidebar);
			dynamic_sidebar( $sidebar );
			printf ('</%1$s>',$tag);
		}
	}

	public static function excluded_pages($content) {
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = '1'",self::HIDE_FROM_SEARCH_METAKEY));
        if ($post_ids && is_array($post_ids)) return (array)$content + $post_ids; 
		return $content;
	} 
	
	public static function excluded_posts($content, $args) {
		global $wpdb;
        $post_ids = $wpdb->get_col( $wpdb->prepare(
        	"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = '1'",self::HIDE_FROM_SEARCH_METAKEY));
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
	
	public static function comment_notes_hide($args) {
		$hide = self::get_option('comment_notes_hide');
		if (($hide == 'before') || ($hide == 'both')) $args['comment_notes_before'] = '';
		if (($hide == 'after') || ($hide == 'both')) $args['comment_notes_after'] = '';
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
 		return do_shortcode(stripslashes(self::$postinfo_shortcodes));
 	}

 	public static function post_meta() {
 		return do_shortcode(stripslashes(self::$postmeta_shortcodes));
 	}

	public static function filter_breadcrumb_args( $args ) {
		$prefix = trim(self::get_option('breadcrumb_prefix'));
		if (!empty($prefix)) $prefix .= '&nbsp;';
		$label = trim(self::get_option('breadcrumb_archive'));
		if (!empty($label)) $label .= '&nbsp;';
		$args['labels']['author']        = $label;
	   $args['labels']['category']      = $label;
	   $args['labels']['tag']           = $label;
	   $args['labels']['date']          = $label;
	   $args['labels']['tax']           = $label;
	   $args['labels']['post_type']     = $label; 
	   $args['labels']['prefix']        = $prefix;
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
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4{$app_id}";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
SCRIPT;
	}

	public static function maybe_redirect_404() {
		if (is_404()
		&& ! is_robots() 
		&& ! is_feed() 
		&& ! is_trackback() 
		&& ( $page_id = self::get_option('alt_404_page'))
		&& ( $page_id != get_query_var( 'page')) 
		&& ( get_post_status($page_id ) == 'publish'))  {
         $status = self::get_option('alt_404_status') ;
         if (!$status) $status = 404;
			if (($status==301) || ($status==302)) 
			   wp_redirect	(get_permalink($page_id), $status);
         else
			   wp_redirect	(get_permalink($page_id).'?404stat='.$status, 302);               
			exit;
		}
	}

   public static function status_header($status_header, $code, $description, $protocol) {
      $qs = $_SERVER['QUERY_STRING'];
      if ($qs
      && (strpos($qs, '404stat=') !== FALSE)
      && ($stat = substr($qs,8,3) )
      && (($stat == '404') || ($stat == '410'))) {
         $code = intval($stat);
         $status_header = "$protocol $code $description";         
      }
      return $status_header ;
   }

	private static function empty_archive($archive) {
		return ! ($archive && is_array($archive) 		
		&& (array_key_exists('sorting',$archive) 
		|| (array_key_exists('orderby',$archive) && !empty($archive['orderby'])) 
		|| (array_key_exists('order',$archive) && !empty($archive['order'])) ));
	}

	private static function get_archives() {
 		return self::get_option('archives');
	}

	private static function save_archives($archives) {
      $display_options = self::get_options(false);
      $display_options['archives'] = $archives;
 		return self::save_options($display_options);
	}

	public static function save_archive($term_id, $new_archive) {
 		$archives = self::get_archives();
		if (self::empty_archive($new_archive)) {
			if (is_array($archives)
			&& array_key_exists($term_id,$archives))
				unset($archives[$term_id]); //delete it if it is present
		} else {
				$archives[$term_id] = $new_archive ;
		}
		return self::save_archives($archives);
	}

 	public static function get_archive($term_id, $archives = false ) {
		if (!$archives) $archives = self::get_archives();
		if ($term_id
		&& is_array($archives) 
		&& array_key_exists($term_id, $archives))
			return $archives[$term_id];
		else
			return array();	
 	}

	private static function get_current_archive() {
		if ($term = Genesis_Club_Utils::get_current_term())
         return self::get_archive($term->term_id) ;
			else
         return false;
	}

	public static function customize_archive( $query ) {
      if ($query->is_archive && ($archive = self::get_current_archive())) {
         self::maybe_sort_archive( $query, $archive);   
         self::maybe_disable_breadcrumbs( $archive);   
         self::maybe_override_opengraph_terms($archive); 
         self::maybe_override_terms_archive_image($archive);
         self::maybe_override_post_info($archive);
         self::maybe_override_post_meta($archive);
      }
 	}


	public static function maybe_disable_breadcrumbs($archive ) {
      if (array_key_exists('disable_breadcrumbs', $archive)
      && $archive['disable_breadcrumbs']) {
         add_filter( 'genesis_pre_get_option_breadcrumb_archive', '__return_false', 10, 2);  
	    }    		 
	}

	public static function maybe_override_post_info($archive ) {
      if (array_key_exists('postinfo_shortcodes', $archive)
      && ($postinfo = $archive['postinfo_shortcodes'])) {
  			self::replace_postinfo($postinfo);
	   } elseif (self::get_option('no_archive_postmeta'))  {
         self::replace_postinfo();
	   }   		 
	}

	public static function maybe_override_post_meta($archive ) {
      if (array_key_exists('postmeta_shortcodes', $archive)
      && ($postmeta = $archive['postmeta_shortcodes'])) {
  			self::replace_postmeta($postmeta);
	   } elseif (self::get_option('no_archive_postmeta'))  {
         self::replace_postmeta();
	   }    		 
	}

	public static function maybe_sort_archive( $query, $archive ) {
      if (array_key_exists('sorting', $archive)
      && array_key_exists('orderby', $archive)
      && array_key_exists('order', $archive)
      && $archive['sorting']
      && $query->is_main_query()) {
         $query->set( 'orderby', $archive['orderby'] );
         $query->set( 'order', $archive['order']);          
	    }    		 
	}

   public static function maybe_replace_category_images() {
		if (($post_id = Genesis_Club_Utils::get_post_id())
		&& ($terms = wp_get_post_terms( $post_id, 'category'))
		&& is_array($terms)
		&& (count($terms) > 0)
		&& ($term = $terms[0])
		&& ($archive = self::get_archive($term->term_id))) {
			self::maybe_override_terms_archive_image($archive, true);
		}
   }

   /* This function can be called on the home page or on archive pages */
   public static function maybe_override_terms_archive_image($archive, $is_front_page = false) {
 		if (isset($archive['excerpt_image']) 
 		&& ( ! $is_front_page  ||   
 			(isset($archive['excerpt_images_on_front_page']) && $archive['excerpt_images_on_front_page'])))
         	self::$term_featured_image = $archive['excerpt_image'];
		else 
         	self::$term_featured_image = '';
		add_filter('genesis_pre_get_image', array( __CLASS__,'get_featured_thumbnail'), 20, 3);         
   }

   public static function get_featured_thumbnail($content, $args, $post) {
      if (self::$term_featured_image) {
         $defaults= array('folder' => 'thumbnails','size' => 'thumbnail', 'attr' => array(), 'format' => 'html');
         $args = wp_parse_args ($args, $defaults); 
         return 'url'==$args['format'] ? self::$term_featured_image:
            sprintf('<img src="%1$s" alt="" %2$s/>' ,
               self::$term_featured_image, 
               (is_array($args['attr']) && array_key_exists('class',$args['attr'])) ? sprintf('class="%1$s"',$args['attr']['class']) : '');        
      } else {
         return $content; //otherwise use post featured image  
      }
   } 

	public static function set_facebook_featured_image_size() {
      /* set up up Facebook friendly image sizes for your featured image, and your archive image */
	
      $facebook_image_scale_factor = 1.91;
         
      $image_width = apply_filters('genesis-club-fb-featured-image-width', 470, self::FACEBOOK_FEATURED_IMAGE); //available to override if you want to
      $image_height = apply_filters('genesis-club-fb-featured-image-height',round($image_width / self::FACEBOOK_IMAGE_SCALE_FACTOR), self::FACEBOOK_FEATURED_IMAGE);
      add_image_size( self::FACEBOOK_FEATURED_IMAGE, $image_width, $image_height, true );

      $image_width = apply_filters('genesis-club-fb-archive-image-width', 240, self::FACEBOOK_ARCHIVE_IMAGE); //thumbnail size for archive pages and widgets
      $image_height = apply_filters('genesis-club-fb-archive-image-height', round($image_width / self::FACEBOOK_IMAGE_SCALE_FACTOR), self::FACEBOOK_ARCHIVE_IMAGE);
      add_image_size(self::FACEBOOK_ARCHIVE_IMAGE, $image_width, $image_height, true ); //in proportion to Facebook image

      if (defined('WPSEO_FILE')) { //Yoast WordPress SEO plugin sets up the featured image for Facebook - so get it to use the correct image size
         add_filter('wpseo_opengraph_image_size', array(__CLASS__, 'set_opengraph_image_size') ) ;  
      }      
	}

   public static function set_opengraph_image_size ($size) {
      return self::FACEBOOK_FEATURED_IMAGE;
   }

	private static function maybe_override_opengraph_terms( $archive ) {
      if ((array_key_exists('og_title', $archive) && (self::$og_title = $archive['og_title']))
         || (array_key_exists('og_desc', $archive) && (self::$og_desc = $archive['og_desc']))
         || (array_key_exists('og_image', $archive) && (self::$og_image = $archive['og_image']))) {
         add_action('wpseo_opengraph', array(__CLASS__, 'override_opengraph_terms') , 5);
	    }    		 
	}

	public static function override_opengraph_terms() {
      if (self::$og_title) add_filter('wpseo_opengraph_title', array(__CLASS__, 'override_opengraph_title'));    		 
      if (self::$og_desc) add_filter('wpseo_opengraph_desc', array(__CLASS__, 'override_opengraph_desc'));    
      if (self::$og_image) add_filter('wpseo_opengraph_image', array(__CLASS__, 'override_opengraph_image'));    
	}

	public static function override_opengraph_title( $title ) {
      return self::$og_title ? self::$og_title : $title; 
   }

	public static function override_opengraph_desc ( $desc ) {
      return self::$og_desc ? self::$og_desc : $desc; 
   }

	public static function override_opengraph_image( $image ) {
      return self::$og_image ? self::$og_image : $image; 
   }

	public static function disable_emojis() {
	  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	  remove_action( 'wp_print_styles', 'print_emoji_styles' );
	  remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	  add_filter( 'tiny_mce_plugins', array(__CLASS__,'disable_emojis_tinymce') );
   }

	public static function disable_emojis_tinymce( $plugins ) {
	  return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	}

   public static function custom_login() {
 	  if (self::get_option('custom_login_enabled')) {      
      add_action('login_head', array(__CLASS__,'custom_login_header'));
      add_action('login_footer', array(__CLASS__, 'custom_login_footer'));
 	  }     
   }

   public static function custom_login_header() {
      printf ('<link rel="stylesheet" href="%1$s" type="text/css" media="screen" />', plugins_url('styles/login.css', dirname(__FILE__)));
   }

	public static function custom_login_footer() {
	  $url = site_url();
	  $login = self::get_option('login');
	  $login_background = self::get_option('custom_login_background');
	  if (!empty($login_background)) $login_background = sprintf('url(\"%1$s\")', $login_background) ;  
	  $login_logo = self::get_option('custom_login_logo');
	  $login_logo = empty($login_logo) ? 'none' :  sprintf('url(\"%1$s\")', $login_logo) ;  
	  $login_button = self::get_option('custom_login_button_color');	 
	  $login_reminder = __('Enter your email address. You will receive a password reminder via e-mail.');   
	  $login_user_label = self::get_option('custom_login_user_label');
	  $jquery = site_url('wp-includes/js/jquery/jquery.js'); 
	  print <<< SCRIPT
<script type="text/javascript" src="{$jquery}"></script>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function(){
   var lf = jQuery('form#loginform');
   lf.prepend("<h2>Login</h2>");
   jQuery("h1 a").attr("id","logo");
   jQuery("h1 a").attr("href","{$url}");
   jQuery("h1 a").attr("title","Home Page");
   jQuery("form#loginform p:first").replaceWith(
   '<p><label>{$login_user_label}</label><input type="text" name="log" id="user_login" class="input" value="" size="20" tabindex="10"></p>');
   jQuery('#nav').appendTo(lf);
   jQuery("body").css("background-image","{$login_background}");
   jQuery("#logo").css("background-image","{$login_logo}");
   jQuery("#wp-submit").css("background-color","{$login_button}");
   jQuery("form#lostpasswordform").prepend("<h2>{$login_reminder}</h2>");
   jQuery("p.message,#login_error").filter(function() { return jQuery.trim(jQuery(this).text()).length == 0;}).remove(); 
});
//]]>
</script>
SCRIPT;
   }

}
