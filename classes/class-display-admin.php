<?php
class Genesis_Club_Display_Admin extends Genesis_Club_Admin {
    const INDICATOR = 'genesis_club_hiding';
    const HIDE_FROM_SEARCH = 'genesis_club_hide_from_search';
    const HIDE_TITLE = 'genesis_club_hide_title';
    const HIDE_BEFORE_CONTENT = 'genesis_club_hide_before_content';
    const HIDE_BEFORE_ENTRY = 'genesis_club_hide_before_entry';
    const HIDE_AFTER_ENTRY = 'genesis_club_hide_after_entry';
    const HIDE_BEFORE_ENTRY_CONTENT = 'genesis_club_hide_before_entry_content';
    const HIDE_AFTER_ENTRY_CONTENT = 'genesis_club_hide_after_entry_content';
    const HIDE_AFTER_CONTENT = 'genesis_club_hide_after_content';
    const DISABLE_AUTOP = 'genesis_club_disable_autop';
    const DISABLE_BREADCRUMBS = 'genesis_club_disable_breadcrumbs';

	protected $archive_tips = array(
		'archive_sorting' => array('heading' => 'Override Sort Order', 'tip' => 'Click to override the sort order of the posts on this archive.'),
		'archive_orderby' => array('heading' => 'Order By', 'tip' => 'Select the field to sort by.'),
		'archive_order' => array('heading' => 'Order', 'tip' => 'Ascending or descending.'),
		'archive_og_title' => array('heading' => 'Facebook Title', 'tip' => 'Title to use for this archive page on Facebook.'),
		'archive_og_desc' => array('heading' => 'Facebook Description', 'tip' => 'Description to use for this archive page on Facebook.'),
		'archive_og_image' => array('heading' => 'Facebook Image', 'tip' => 'URL of image to use on Facebook. It is recommended you provide an image of size 470 by 246px.'),
		'archive_excerpt_image' => array('heading' => 'Excerpt Image', 'tip' => 'URL of image to use as the archive excerpt image for all posts in this archive. The image is used as is, so you need to provide the image at the exact size you want to display it.'),
		'archive_excerpt_images_on_front_page' => array('heading' => 'Use On Home Page', 'tip' => 'Use category image rather than individual featured images in post excepts on the home page.'),
		'archive_disable_breadcrumbs' => array('heading' => 'Disable Breadcrumbs', 'tip' => 'Click to disable breadcrumbs on this archive.'),
		'archive_postinfo_shortcodes' => array('heading' => 'Post Info Shortcodes', 'tip' => 'Here you can set Post Info of this specific term which will override the global setting. Use [] to remove Post Info completely.'),
		'archive_postmeta_shortcodes' => array('heading' => 'Post Meta Shortcodes', 'tip' => 'Here you can set Post Meta of this specific term which will override the global setting. Use [] to remove Post Meta completely.'),
	);
                
	protected $tips = array(
		'remove_blog_title' => array('heading' => 'Remove Blog Title Text', 'tip' => 'Click to remove text and h1 tags from the title on the home page. This feature allows you to place h1 tags elsewhere on the home page and just use a logo image in the #title element.'),
		'logo' => array('heading' => 'Logo URL', 'tip' => 'Enter the full URL of a logo image that will appear in the title element on the left hand side of the header. Consult the theme instructions for recommendation on the logo dimensions, normally a size of around 400px by 200px is okay. The image file can be located in your media library, on Amazon S3 or a CDN.'),
		'logo_alt' => array('heading' => 'Logo Alt Text', 'tip' => 'Enter the ALT attribute for your logo.'),
		'comment_invitation' => array('heading' => 'Invitation To Comment', 'tip' => 'Enter your enticement to comment. This will replace "Leave A Reply" in HTML5 sites or "Speak Your Mind" in XHTML sites.'),
		'comment_notes_hide' => array('heading' => 'Hide Comment Notes', 'tip' => 'The commment note before the comment box refers to the email address not being displayed; the comment note after the comment box refers to the HTML tags which are permitted in the comment. <br/>Here you can decide to suppress one or both of these comment notes.'),
		'read_more_text' => array('heading' => 'Read More', 'tip' => 'Hide the text that appears before and after the comment box.'),
		'breadcrumb_prefix' => array('heading' => 'Breadcrumb Prefix', 'tip' => 'Enter the text that prefixes the breadcrumb. This will replace "You are here:".'),
		'breadcrumb_archive' => array('heading' => 'Breadcrumb Archives', 'tip' => 'Enter the text that appears at the start of the archive breadcrumb. This will replace "Archives for".'),
		'before_content' => array('heading' => 'Before Content', 'tip' => 'Click to add a wide widget area below the header which stretches above the content, and any primary and secondary sidebar sections.'),
		'before_archive' => array('heading' => 'Before Archive', 'tip' => 'Click to add a widget area before the list of entries on an archive page. This can be used to add an introductory paragraph, video or slider at the top of the archive page that both provides unique content and incites interest in the topic with the goal of reducing the bounce rate and hence improving the page ranking in the SERPs.'),
		'before_entry' => array('heading' => 'Before Entry', 'tip' => 'Click to add a widget area immediately above the post title. This is typically used for ads or calls to action.'),
		'before_entry_content' => array('heading' => 'Before Entry Content', 'tip' => 'Click to add a widget area immediately before the post content. This is typically used to add social media icons for sharing the content.'),
		'after_entry_content' => array('heading' => 'After Entry Content', 'tip' => 'Click to add a widget area immediately after the post content. If your child theme already has this area then there is no need to create another one. This area is typically used to add social media icons for sharing the content.'),
		'after_entry' => array('heading' => 'After Entry', 'tip' => 'Click to add a widget area immediately after the entry on single pages and posts. This area is typically used for ads or calls to action.'),
		'after_archive' => array('heading' => 'After Archive', 'tip' => 'Click to add a widget area after all the entries on an archive page. This can be used to add a call to action or maybe an ad.'),
		'after_content' => array('heading' => 'After Content', 'tip' => 'Click to add a widget area immediately after the content just before the footer. The widget area will be under the content area and any sidebars. This area will typically be used for ads or calls to action'),
		'facebook_app_id' => array('heading' => 'Facebook App ID', 'tip' => 'Enter your Facebook App ID (15 characters) as found at https://developers.facebook.com/apps'),
		'facebook_likebox_bgcolor' => array('heading' => 'LikeBox Bg Color', 'tip' => 'Choose the background color of the Facebook LikeBox. The Facebook Likebox widget only gives you light and dark options; this allows you to choose a background color that better suits your WordPress theme'),
		'facebook_featured_images' => array('heading' => 'Featured Image Sizes', 'tip' => 'Click to set up featured image sizes for use on Facebook. Two image sizes are created: one is 470 by 246px for use on Facebook, and the other is 200 by 105px for use alongside post excerpts on your archive pages.'),
		'facebook_sized_images' => array('heading' => 'Facebook Sizing', 'tip' => 'Click to set up the standard WordPress large, medium and thumbnail sizes to be appropriately sized for use on Facebook. Only do this when you are setting up the site and have decided that all your uploaded images will have a width to height ratio of 1.91:1.'),
		'postinfo_shortcodes' => array('heading' => 'Post Info Short Codes', 'tip' => 'Content of the byline that is placed typically below or above the post title. Leave blank to use the child theme defaults or enter here to override. <br/>For example: <br/><code>[post_date format=\'M j, Y\'] by [post_author_posts_link] [post_comments] [post_edit]</code><br/>or to hide Post Info entirely use <code>[]</code>'),
		'postmeta_shortcodes' => array('heading' => 'Post Meta Short Codes', 'tip' => 'Content of the line that is placed typically after the post content. <br/> Leave blank to use the child theme defaults or enter here to override. <br/> For example: <br/><code>[post_categories before=\'More Articles About \'] [post_tags]</code><br/>or to hide Post Meta entirely use <code>[]</code>'),
		'no_page_postmeta' => array('heading' => 'Remove On Pages', 'tip' => 'Strip any post info from pages.'),
		'no_archive_postmeta' => array('heading' => 'Remove On Archives', 'tip' => 'Strip any post info and post meta from the top and bottom of post excerpts on archive pages.'),
		'alt_404_page' => array('heading' => 'Alternative 404 Page', 'tip' => 'Send the user to your own custom 404 page if the chosen page is not found.'),
		'alt_404_status' => array('heading' => 'HTTP Status', 'tip' => 'Normally you find want to return 404 however you can choose to return a 410 if say, you have just deleted a whole bunch of pages from your site, or if your site is narrowly based you might want to return a 301 providing the chosen alternative 404 page has a canonical URL.'),
		'css_hacks' => array('heading' => 'Add Helper Classes', 'tip' => 'Add useful classes such as clearfix (for clearing floats) and dropcaps (for capitalizing the first letter of the first paragraph.'),
		'disable_emojis' => array('heading' => 'Disable Emojis', 'tip' => 'Remove Emojis if you do not intend to use them.'),
		'custom_login_enabled' => array('heading' => 'Enable Custom Login', 'tip' => 'Enable Login Page Customizations.'),
		'custom_login_background' => array('heading' => 'Page Background URL', 'tip' => 'URL of image to use as the login page background.'),
		'custom_login_logo' => array('heading' => 'Logo Background URL', 'tip' => 'URL of image to use as the logo recommended size is 200px square.'),
		'custom_login_user_label' => array('heading' => 'User Login Label', 'tip' => 'Label for the user/member login / email.'),
		'custom_login_button_color' => array('heading' => 'Login Button Color', 'tip' => 'Choose color of the login button.'),
		'disable_breadcrumbs' => array('heading' => 'Disable Breadcrumbs', 'tip' => 'Click to disable breadcrumbs on this page.'),
		);

	function init() {
		add_action('load-post.php', array($this, 'load_post_page'));	
		add_action('load-post-new.php', array($this, 'load_post_page'));	
		add_action('save_post', array($this, 'save_postmeta'));
		add_action('do_meta_boxes', array($this, 'do_meta_boxes'), 30, 2 );
		add_action('load-edit-tags.php', array($this, 'load_archive_page'));	
		add_action('edit_term', array($this, 'save_archive'), 10, 2 );	
		add_action('admin_menu',array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Display'), __('Display'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
	}

	function page_content() {
		$title = $this->admin_heading('Genesis Club Display Settings');		
		$this->print_admin_form($title, __CLASS__, $this->get_keys()); 		
	}  
	
	function load_page() {
 		if (isset($_POST['options_update']) ) $this->save_display();
		$callback_params = array ('options' => Genesis_Club_Display::get_options(false));
		$this->add_meta_box('intro', 'Intro',  'intro_panel');
		$this->add_meta_box('display', 'Display Settings', 'display_panel', $callback_params);
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel', null, 'advanced');
		$this->set_tooltips($this->tips);
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}
 		
 
	function load_post_page() {
		$this->set_tooltips($this->tips);
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
	}
	 
 	function load_archive_page() {
      if (isset($_GET['post_type']) && Genesis_Club_Plugin::is_post_type_enabled($_GET['post_type'])) {
		$this->set_tooltips($this->archive_tips);
		add_action( $_REQUEST['taxonomy'] . '_edit_form', array($this, 'archive_panel'), 10, 2 );	
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
	}
	}

	function save_archive($term_id, $tt_id) {
		return isset( $_POST['archive'] ) ?
			Genesis_Club_Display::save_archive($term_id, (array) $_POST['archive']) : false;
	}	

	function save_display() {
		check_admin_referer(__CLASS__);
      self::maybe_make_standard_image_sizes_facebook_ready();
		return $this->save_options('Genesis_Club_Display', __('Display',GENESIS_CLUB_DOMAIN ));
	}
    
	function save_postmeta($post_id) {
		if (array_key_exists(self::INDICATOR, $_POST)) {
			$post_type = get_post_type( $post_id);
			$keys = array();
			$keys[self::HIDE_FROM_SEARCH] = Genesis_Club_Display::HIDE_FROM_SEARCH_METAKEY;
			$keys[self::HIDE_TITLE] = Genesis_Club_Display::HIDE_TITLE_METAKEY;
			$keys[self::HIDE_BEFORE_CONTENT] = Genesis_Club_Display::HIDE_BEFORE_CONTENT_METAKEY;
			$keys[self::HIDE_BEFORE_ENTRY] = Genesis_Club_Display::HIDE_BEFORE_ENTRY_METAKEY;
			$keys[self::HIDE_BEFORE_ENTRY_CONTENT] = Genesis_Club_Display::HIDE_BEFORE_ENTRY_CONTENT_METAKEY;
			$keys[self::HIDE_AFTER_ENTRY_CONTENT] = Genesis_Club_Display::HIDE_AFTER_ENTRY_CONTENT_METAKEY;
			$keys[self::HIDE_AFTER_ENTRY] = Genesis_Club_Display::HIDE_AFTER_ENTRY_METAKEY;
			$keys[self::HIDE_AFTER_CONTENT] = Genesis_Club_Display::HIDE_AFTER_CONTENT_METAKEY;
			$keys[self::DISABLE_AUTOP] = Genesis_Club_Display::DISABLE_AUTOP_METAKEY;
			$keys[self::DISABLE_BREADCRUMBS] = Genesis_Club_Display::DISABLE_BREADCRUMBS;

			foreach ($keys as $key => $metakey)
				update_post_meta( $post_id, $metakey, array_key_exists($key, $_POST) ? $_POST[$key] : false);			
			do_action('genesis_club_hiding_settings_save',$post_id);
		}
	}

	function do_meta_boxes( $post_type, $context) {
		if ($this->is_metabox_active($post_type, $context)) {
         add_filter( 'genesis_club_post_settings', array($this, 'add_post_panel'), 9, 2);	//add to plugin metabox
			$current_screen = get_current_screen();
			if (method_exists($current_screen,'add_help_tab'))
	    		$current_screen->add_help_tab( array(
			        'id'	=> 'genesis_club_help_tab',
    			    'title'	=> __('Genesis Club'),
        			'content'	=> __('
<p>In the <b>Genesis Club Posts Settings - Hiding</b> section below you can choose NOT to show this page in site search page results, and control whether certain other elements shoudl apear on this page.</p>')) );
		}
	}

	function widget_area_visibility_checkbox($option) {
		global $post;
		$hide = 'post'==$post->post_type; /* Hide posts, show pages on custom post types */
		$label = '%1$s the %2$s widget area on this page';
		return $this->visibility_checkbox($option, $hide, $label);
    } 

	function disable_checkbox($option, $disable, $label_format) {
		global $post;
		$action = $disable ? 'disable' : 'enable'; 
		$key = sprintf('genesis_club_%1$s_%2$s',$action, $option);
		$value = get_post_meta($post->ID, '_'.$key, true);
		$checked = $value ?'checked="checked" ':'';		
		$label =  __(sprintf($label_format, $disable ? 'Disable' : 'Enable', ucwords(str_replace('_',' ', $option))));
		return sprintf('<label><input class="valinp" type="checkbox" name="%1$s" id="%1$s" %2$svalue="1" />%3$s</label><br/>',
			$key, $checked, $label);
    } 

	function visibility_checkbox($option, $hide, $label_format) {
		global $post;
		$action = $hide ? 'hide' : 'show'; 
		$key = sprintf('genesis_club_%1$s_%2$s',$action, $option);
		$value = get_post_meta($post->ID, '_'.$key, true);
		$checked = $value ?'checked="checked" ':'';		
		$label =  __(sprintf($label_format, $hide ? 'Do not show' : 'Show', ucwords(str_replace('_',' ', $option))));
		return sprintf('<label><input class="valinp" type="checkbox" name="%1$s" id="%1$s" %2$svalue="1" />%3$s</label><br/>',
			$key, $checked, $label);
    }   


	function add_post_panel($content, $post) {
		return $content + array ('Display' => $this->hiding_panel($post));
   }

	function hiding_panel($post) {
      	$s = '';
		$s .= $this->form_field(self::INDICATOR, self::INDICATOR, '', 1, 'hidden'); 
		$s .= $this->visibility_checkbox('from_search', true, '%1$s this page on the site search results page');
		$s .= $this->visibility_checkbox('title', true, '%1$s the title on this page');
		$options = Genesis_Club_Display::get_options();
		if ($options['before_content']) $s .= $this->widget_area_visibility_checkbox('before_content');		
		if ($options['before_entry']) $s .= $this->widget_area_visibility_checkbox('before_entry');		
		if ($options['before_entry_content']) $s .= $this->widget_area_visibility_checkbox('before_entry_content');
		if ($options['after_entry_content']) $s .= $this->widget_area_visibility_checkbox('after_entry_content');
		if ($options['after_entry']) $s .= $this->widget_area_visibility_checkbox('after_entry');
		if ($options['after_content']) $s .= $this->widget_area_visibility_checkbox('after_content');
		$s .= $this->disable_checkbox('breadcrumbs', true, '%1$s breadcrumbs on this page');		
		$s .= $this->disable_checkbox('autop', true, '%1$s auto-paragraphing of the page content');											
		$s = apply_filters('genesis_club_hiding_settings_show', $s, $post);
		return $s;
    }
 
 	function intro_panel(){		
		print('<p>The following sections allow you to tweak some Genesis settings you want to change on most sites without having to delve into PHP.</p>');
	}

	function display_panel($post, $metabox) {
		$options = $metabox['args']['options'];	 	
      $this->display_metabox( array (
         'Logo' => $this->logo_panel($options),
         'Labels' => $this->labelling_panel($options),
         'PostInfo/Meta' => $this->meta_panel($options),
         'Extra Widget Areas' => $this->extras_panel($options),
         'Facebook' => $this->facebook_panel($options),
         'Alternate 404 Page' => $this->alt_404_panel($options),
         'Custom Login' => $this->custom_login_panel($options),
         'Misc' => $this->misc_panel($options),
      ));
   }	

	function logo_panel($options){
	  return	 	
         $this->fetch_form_field('remove_blog_title', $options['remove_blog_title'], 'checkbox') .
         $this->fetch_text_field('logo', $options['logo'], array('size' => 55)) .
         $this->fetch_text_field('logo_alt', $options['logo_alt'], array('size' => 55));
	}
 
	function facebook_panel($options){
      return	 
         $this->fetch_text_field('facebook_app_id', $options['facebook_app_id'],  array('size' => 20)) .
         $this->fetch_text_field('facebook_likebox_bgcolor', $options['facebook_likebox_bgcolor'], array('size' => 8, 'class' => 'color-picker')) .
         $this->fetch_form_field('facebook_featured_images', $options['facebook_featured_images'], 'checkbox') .
         $this->fetch_form_field('facebook_sized_images', $options['facebook_sized_images'], 'checkbox');
	}
	
	function extras_panel($options){
      return
         $this->fetch_form_field('before_content', $options['before_content'], 'checkbox') .
         $this->fetch_form_field('before_archive', $options['before_archive'], 'checkbox') .
         $this->fetch_form_field('before_entry', $options['before_entry'], 'checkbox') .
         $this->fetch_form_field('before_entry_content', $options['before_entry_content'], 'checkbox') .
         $this->fetch_form_field('after_entry_content', $options['after_entry_content'], 'checkbox') .
         $this->fetch_form_field('after_entry', $options['after_entry'], 'checkbox') .
         $this->fetch_form_field('after_archive', $options['after_archive'], 'checkbox') .
         $this->fetch_form_field('after_content', $options['after_content'], 'checkbox');
	}	

	function meta_panel($options){
      return
         $this->fetch_form_field('no_archive_postmeta', $options['no_archive_postmeta'],  'checkbox') .
         $this->fetch_form_field('no_page_postmeta', $options['no_page_postmeta'],  'checkbox') .
         $this->fetch_form_field('postinfo_shortcodes', $options['postinfo_shortcodes'], 'textarea', array(), array('cols' => 30, 'rows' => 3)) .
         $this->fetch_form_field('postmeta_shortcodes', $options['postmeta_shortcodes'], 'textarea', array(), array('cols' => 30, 'rows' => 3));
	}

	function labelling_panel($options){		 	
      return
         $this->fetch_text_field('read_more_text', $options['read_more_text'], array('size' => 40)) .
         $this->fetch_text_field('comment_invitation', $options['comment_invitation'], array('size' => 40)) .
         $this->fetch_form_field('comment_notes_hide', $options['comment_notes_hide'], 'radio', 
			array(0 => 'hide neither', 'before' => 'hide note before', 'after' => 'hide note after', 'both' => 'hide both' )) .
         $this->fetch_text_field('breadcrumb_prefix', $options['breadcrumb_prefix'],  array('size' => 40)) .
         $this->fetch_text_field('breadcrumb_archive', $options['breadcrumb_archive'], array('size' => 40));
	}	

	function alt_404_panel($options) {
      if ( ! ($status = $options['alt_404_status'])) $status = '404';
      return
         $this->fetch_form_field('alt_404_page', 
			wp_dropdown_pages( array( 'name' => 'alt_404_page', 'selected' => $options['alt_404_page'], 'echo' => false, 
				'depth' => 1, 'option_none_value' => 0, 'show_option_none' => 'Use default 404 page')),
			   'fixed') .
         $this->fetch_form_field('alt_404_status', $status,  'radio', 
            array('404' => '404 - Not Found', '410' => '410 - Gone Away', '301' => '301 - Moved Permanently', '307' => '307 - Moved Temporarily'));
	}

	function misc_panel($options){
	  return
         $this->fetch_form_field('css_hacks', $options['css_hacks'], 'checkbox') .
         $this->fetch_form_field('disable_emojis', $options['disable_emojis'], 'checkbox');
	}	  

	function custom_login_panel($options){	
      return	 	
         $this->fetch_form_field('custom_login_enabled', $options['custom_login_enabled'], 'checkbox') .
         $this->fetch_text_field('custom_login_background', $options['custom_login_background'], array('size' => 50)) .
         $this->fetch_text_field('custom_login_logo', $options['custom_login_logo'], array('size' => 50)) .
         $this->fetch_text_field('custom_login_user_label', $options['custom_login_user_label'], array('size' => 50)) .
         $this->fetch_text_field('custom_login_button_color', $options['custom_login_button_color'], array('size' => 8, 'class' => 'color-picker'));
	}	  

	function archive_panel($term, $tt_id) {
		$archive = Genesis_Club_Display::get_archive($term->term_id) ;
 		$defaults = array('sorting' => false, 'orderby' => 'date', 'order' => 'DESC');
		$archive = is_array($archive) ?  array_merge($defaults,$archive) : $defaults;

      printf('<h3>%1$s</h3>', __('Genesis Club Archive Settings', GENESIS_CLUB_DOMAIN));
      $this->display_metabox( apply_filters( 'genesis_club_archive_settings', array(
         'Sort Order' => $this->archive_sort_panel($archive),
         'Facebook' => $this->archive_facebook_panel($archive),
         'Excerpt Image' => $this->archive_excerpt_panel($archive), 
         'Breadcrumbs' => $this->archive_breadcrumbs_panel($archive),
         'Post Info/Meta' => $this->archive_postmeta_panel($archive)), 
         $term, $tt_id));
   }

	private function archive_sort_panel($archive) {
      $sort_options = array( '' => 'Select order', 'post_date' => 'Date first published', 'post_modified_gmt' => 'Date last updated', 
         'comment_count' => 'Number of comments', 'post_author' => 'Post Author Name',  'ID' => 'Post ID',
         'post_title' => 'Post Title', 'rand' => 'Random'  );

		return sprintf('<table class="form-table">%1$s%2$s%3$s</table>',	
         $this->archive_form_field($archive, 'sorting', 'checkbox') ,
         $this->archive_form_field($archive, 'orderby', 'select', $sort_options) ,
		   $this->archive_form_field($archive, 'order', 'radio', array('ASC' => 'Ascending', 'DESC' => 'Descending')) );
	}

	private function archive_facebook_panel($archive) {
		return sprintf('<table class="form-table">%1$s%2$s%3$s</table>',	
         $this->archive_form_field($archive, 'og_title', 'text', array(), array('size' => 50)) ,
         $this->archive_form_field($archive, 'og_desc', 'textarea', array(), array('cols' => 50, 'rows' => 3)) ,
		   $this->archive_form_field($archive, 'og_image', 'textarea', array(), array('cols' => 50, 'rows' => 2)) );
	}

	private function archive_excerpt_panel($archive) {
		return sprintf('<table class="form-table">%1$s%2$s</table>',	
		   $this->archive_form_field($archive, 'excerpt_image', 'textarea', array(), array('cols' => 50, 'rows' => 2)),
		   $this->archive_form_field($archive, 'excerpt_images_on_front_page', 'checkbox')
			);
	}
	
	private function archive_breadcrumbs_panel($archive) {
		return sprintf('<table class="form-table">%1$s</table>',	
		   $this->archive_form_field($archive, 'disable_breadcrumbs', 'checkbox')
			);
	}

	private function archive_postmeta_panel($archive) {
		return sprintf('<table class="form-table">%1$s%2$s</table>',	
		   $this->archive_form_field($archive, 'postinfo_shortcodes', 'textarea', array(), array('cols' => 30, 'rows' => 3)),
		   $this->archive_form_field($archive, 'postmeta_shortcodes', 'textarea', array(), array('cols' => 30, 'rows' => 3))
			);
	}

	private function archive_form_field($archive, $fld, $type, $options = array(), $args = array()) {
		$id = 'archive_'.$fld;
		$name = 'archive['.$fld.']';	
		$value = isset($archive[$fld]) ? $archive[$fld] : '';
		return $this->form_field($id, $name, false, $value, $type, $options, $args, 'tr');
	}
	
	private function maybe_make_standard_image_sizes_facebook_ready() {
      $old_value = Genesis_Club_Display::get_option('facebook_sized_images');
		if (!$old_value && array_key_exists('facebook_sized_images', $_POST)) {
         $image_width = apply_filters('genesis-club-large-image-width', 960); //large size for post images
         $image_height = apply_filters('genesis-club-large-image-height',round($image_width / Genesis_Club_Display::FACEBOOK_IMAGE_SCALE_FACTOR));
         update_option( 'large_size_w', $image_width);
         update_option( 'large_size_h', $image_height); 
         $image_width = apply_filters('genesis-club-medium-image-width', 320); //medium size for post images
         $image_height = apply_filters('genesis-club-medium-image-height',round($image_width / Genesis_Club_Display::FACEBOOK_IMAGE_SCALE_FACTOR));
         update_option( 'medium_size_w', $image_width);
         update_option( 'medium_size_h', $image_height); 
         $image_width = apply_filters('genesis-club-thumbnail-image-width', 160); //thumbnail size for archives
         $image_height = apply_filters('genesis-club-thumbnail-image-height',round($image_width / Genesis_Club_Display::FACEBOOK_IMAGE_SCALE_FACTOR));
         update_option( 'thumbnail_size_w', $image_width);
         update_option( 'thumbnail_size_h', $image_height);
      }
	}
}

