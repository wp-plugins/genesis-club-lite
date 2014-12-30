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
		'facebook_likebox_bgcolor' => array('heading' => 'LikeBox Background Color', 'tip' => 'Choose the background color of the Facebook LikeBox. The Facebook Likebox widget only gives you light and dark options; this allows you to choose a background color that better suits your WordPress theme'),
		'postinfo_shortcodes' => array('heading' => 'Post Info Short Codes', 'tip' => 'Content of the byline that is placed typically below or above the post title. Leave blank to use the child theme defaults or enter here to override. <br/>For example: <br/><code>[post_date format=\'M j, Y\'] by [post_author_posts_link] [post_comments] [post_edit]</code><br/>or to hide Post Info entirely use <code>[]</code>'),
		'postmeta_shortcodes' => array('heading' => 'Post Meta Short Codes', 'tip' => 'Content of the line that is placed typically after the post content. <br/> Leave blank to use the child theme defaults or enter here to override. <br/> For example: <br/><code>[post_categories before=\'More Articles About \'] [post_tags]</code><br/>or to hide Post Meta entirely use <code>[]</code>'),
		'no_page_postmeta' => array('heading' => 'Remove On Pages', 'tip' => 'Strip any post info from pages.'),
		'no_archive_postmeta' => array('heading' => 'Remove On Archives', 'tip' => 'Strip any post info and post meta from the top and bottom of post excerpts on archive pages.'),
		'alt_404_page' => array('heading' => 'Alternative 404 Page', 'tip' => 'Send the user to your own custom 404 page if the chosen page is not found.'),
		'css_hacks' => array('heading' => 'Add CSS Hacks', 'tip' => 'Add useful classes such as clearfix.'),
		);

	function init() {
		add_action('do_meta_boxes', array($this, 'do_meta_boxes'), 30, 2 );
		add_action('save_post', array($this, 'save_postmeta'));
		add_action('admin_menu',array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Display'), __('Display'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
	}

	function page_content() {
		$title = $this->admin_heading('Display Settings', GENESIS_CLUB_ICON);		
		$this->print_admin_form_with_sidebar_start($title); 
		do_meta_boxes($this->get_screen_id(), 'side', null); 
		$this->print_admin_form_with_sidebar_middle();
		do_meta_boxes($this->get_screen_id(), 'normal', null); 
		$this->print_admin_form_end(__CLASS__, $this->get_keys());		
	}  
	
	function load_page() {
 		$message =  isset($_POST['options_update']) ? $this->save_display() : '';
		$callback_params = array ('options' => Genesis_Club_Display::get_options(false), 'message' => $message);
		$this->add_meta_box('intro', 'Intro',  'intro_panel', $callback_params);
		$this->add_meta_box('title', 'Responsive Logo', 'logo_panel', $callback_params);
		$this->add_meta_box('labelling', 'Labels',  'labelling_panel',  $callback_params);
		$this->add_meta_box('meta', 'Post Info and Post Meta',  'meta_panel', $callback_params);
		$this->add_meta_box('extras', 'Extra Widget Areas', 'extras_panel', $callback_params);
		$this->add_meta_box('facebook', 'Facebook',  'facebook_panel', $callback_params);
		$this->add_meta_box('404', '404 Page',  'alt_404_panel', $callback_params);
		$this->add_meta_box('css', 'CSS Classes',  'css_panel', $callback_params);		
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel', $callback_params, 'side');
		$this->set_tooltips($this->tips);
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}
 		
	function save_display() {
		check_admin_referer(__CLASS__);
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

			foreach ($keys as $key => $metakey)
				update_post_meta( $post_id, $metakey, array_key_exists($key, $_POST) ? $_POST[$key] : false);			
			do_action('genesis_club_hiding_settings_save',$post_id);
		}
	}

	function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			add_meta_box( 'genesis-club-hiding', 'Genesis Club Hiding Settings', array($this, 'hiding_panel' ), $post_type, 'advanced', 'low' );
			$current_screen = get_current_screen();
			if (method_exists($current_screen,'add_help_tab'))
	    		$current_screen->add_help_tab( array(
			        'id'	=> 'genesis_club_help_tab',
    			    'title'	=> __('Genesis Club'),
        			'content'	=> __('
<p>In the <b>Genesis Club Hiding</b> section below you can choose NOT to show this page in site search page results and to remove the page title.</p>')) );
		}
	}


	function widget_area_visibility_checkbox($option) {
		global $post;
		$hide = 'post'==$post->post_type; /* Hide posts, show pages on custom post types */
		$label = '%1$s the %2$s widget area on this page';
		return $this->visibility_checkbox($option, $hide, $label);
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

	function hiding_panel($post,$metabox) {
		print $this->form_field(self::INDICATOR, self::INDICATOR, '', 1, 'hidden'); 

		print $this->visibility_checkbox('from_search', true, '%1$s this page on the site search results page');

		print $this->visibility_checkbox('title', true, '%1$s the title on this page');

		$options = Genesis_Club_Display::get_options();

		if ($options['before_content'])
			print $this->widget_area_visibility_checkbox('before_content');
				
		if ($options['before_entry'])
			print $this->widget_area_visibility_checkbox('before_entry');
				
		if ($options['before_entry_content'])
			print $this->widget_area_visibility_checkbox('before_entry_content');

		if ($options['after_entry_content'])
			print $this->widget_area_visibility_checkbox('after_entry_content');

		if ($options['after_entry'])
			print $this->widget_area_visibility_checkbox('after_entry');

		if ($options['after_content'])
			print $this->widget_area_visibility_checkbox('after_content');
												
		do_action('genesis_club_hiding_settings_show',$post);
    }
 
 	function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following sections allow you to tweak some Genesis settings you want to change on most sites without having to delve into PHP.</p>
{$message}
INTRO_PANEL;
	}

	function logo_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_form_field('remove_blog_title', $options['remove_blog_title'], 'checkbox');
		$this->print_text_field('logo', $options['logo'], array('size' => 55));
		$this->print_text_field('logo_alt', $options['logo_alt'], array('size' => 55));
	}
 
	function facebook_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 
		$this->print_text_field('facebook_app_id', $options['facebook_app_id'],  array('size' => 20));
		$this->print_text_field('facebook_likebox_bgcolor', $options['facebook_likebox_bgcolor'], array('size' => 8, 'class' => 'color-picker'));
	}
	
	function extras_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_form_field('before_content', $options['before_content'], 'checkbox');
		$this->print_form_field('before_archive', $options['before_archive'], 'checkbox');
		$this->print_form_field('before_entry', $options['before_entry'], 'checkbox');
		$this->print_form_field('before_entry_content', $options['before_entry_content'], 'checkbox');
		$this->print_form_field('after_entry_content', $options['after_entry_content'], 'checkbox');
		$this->print_form_field('after_entry', $options['after_entry'], 'checkbox');
		$this->print_form_field('after_archive', $options['after_archive'], 'checkbox');
		$this->print_form_field('after_content', $options['after_content'], 'checkbox');
	}	

	function meta_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_form_field('no_archive_postmeta', $options['no_archive_postmeta'],  'checkbox');
		$this->print_form_field('no_page_postmeta', $options['no_page_postmeta'],  'checkbox');
		$this->print_text_field('postinfo_shortcodes', $options['postinfo_shortcodes'], array('size' => 60));
		$this->print_text_field('postmeta_shortcodes', $options['postmeta_shortcodes'], array('size' => 60));
	}

	function labelling_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_text_field('read_more_text', $options['read_more_text'], array('size' => 40));
		$this->print_text_field('comment_invitation', $options['comment_invitation'], array('size' => 40));
		$this->print_form_field('comment_notes_hide', $options['comment_notes_hide'], 'radio', 
			array(0 => 'hide neither', 'before' => 'hide note before', 'after' => 'hide note after', 'both' => 'hide both' ));
		$this->print_text_field('breadcrumb_prefix', $options['breadcrumb_prefix'],  array('size' => 40));
		$this->print_text_field('breadcrumb_archive', $options['breadcrumb_archive'], array('size' => 40));
	}	

	function alt_404_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_form_field('alt_404_page', 
			wp_dropdown_pages( array( 'name' => 'alt_404_page', 'selected' => $options['alt_404_page'], 'echo' => false, 
				'depth' => 1, 'option_none_value' => 0, 'show_option_none' => 'Use default 404 page')),
			'fixed');
	}

	function css_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_form_field('css_hacks', $options['css_hacks'], 'checkbox');
	}	  

 	function news_panel($post,$metabox){	
		Genesis_Club_Feed_Widget::display_feeds();
	}
}

