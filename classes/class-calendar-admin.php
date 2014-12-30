<?php
class Genesis_Club_Calendar_Admin extends Genesis_Club_Admin {
	private $tips = array(
		'iframe' => array('heading' => 'Google Calendar Iframe', 'tip' => 'Paste the embed code for your Google Calendar.'),
		'label' => array('heading' => 'Timezone Label', 'tip' => 'For example, Choose Your Timezone to see the meetings in tyour local time.'),
		'timezone' => array('heading' => 'Timezone', 'tip' => 'For example, Europe/London')
	);
	
	function init() {
		add_action('admin_menu',array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Genesis Club Calendar'), __('Calendar'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
	}
	
	function page_content() {
		$title = $this->admin_heading('Google Calendar Settings', GENESIS_CLUB_ICON);		
		$this->print_admin_form_with_sidebar_start($title); 
		do_meta_boxes($this->get_screen_id(), 'side', null); 
		$this->print_admin_form_with_sidebar_middle();
		do_meta_boxes($this->get_screen_id(), 'normal', null); 
		$this->print_admin_form_end( __CLASS__, $this->get_keys());
	}  


	function load_page() {
 		$message = isset($_POST['options_update']) ? $this->save_calendar() : '';	
		$callback_params = array ('options' => Genesis_Club_Calendar::get_options(), 'message' => $message);
		$this->add_meta_box('intro', 'Intro', 'intro_panel',  $callback_params);
		$this->add_meta_box('menu', 'Google Calendar Defaults', 'calendar_panel', $callback_params);
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel',$callback_params, 'side');
		$this->set_tooltips($this->tips);
		add_action('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}

	function save_calendar() {
		check_admin_referer(__CLASS__);
		return $this->save_options('Genesis_Club_Calendar','Calendar Settings');
	}
	 
 	function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following section allows you to set up a Google Calendar which shows your events in your visitor's timezone.
This is particularly useful if you are delivering webinars to a global audience.</p>
{$message}
INTRO_PANEL;
	}
  
	function calendar_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$this->print_form_field("iframe", $options['iframe'], 'textarea', array(), array('rows' => 10, 'cols' => 50)) ;
		$this->print_form_field("label", $options['label'], 'textarea', array(), array('rows' => 3, 'cols' => 50)) ;
		$this->print_form_field("timezone", $options['timezone'], 'select', Genesis_Club_Calendar::timezones()) ;
	}	

 	function news_panel($post,$metabox){	
		Genesis_Club_Feed_Widget::display_feeds();
	}
  
}
