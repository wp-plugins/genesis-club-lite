<?php
class Genesis_Club_Menu_Admin extends Genesis_Club_Admin {
	private $tips = array(
		'threshold' => array('heading' => 'Device Threshold', 'tip' => 'Enter the size in pixels at which the full menu is collapsed into the "hamburger" icon or leave blank to disable this feature.'),
		'icon_size' => array('heading' => 'Hamburger Icon Size', 'tip' => 'Size of the menu icon measured in rem, or leave blank to use the default which is 2.4 (1.5 times the size of a small icon).'),
		'icon_color' => array('heading' => 'Hamburger Icon Color', 'tip' => 'Color of the hamburger menu icon (e.g #808080), or leave blank if you want the icon to adopt the same color as the links in the menu.'),
		'primary' => array('heading' => 'Primary Responsive Menu', 'tip' => 'Choose where you want the primary menu to be displayed when the hamburger is clicked.'),
		'secondary' => array('heading' => 'Secondary Responsive Menu', 'tip' => 'Choose where you want the secondary menu to be displayed when the hamburger is clicked.'),
		'header' => array('heading' => 'Header Responsive Menu', 'tip' => 'Choose where you want the header right menu to be displayed when the hamburger is clicked.'),
		'search_menu' => array('heading' => 'Add Search Box', 'tip' => 'Here you can add a search box to the end of one of the menus'),
		'search_text' => array('heading' => 'Search Label', 'tip' => 'Enter the text you want to appear in the Search Box'),
		'search_button' => array('heading' => 'Search Button', 'tip' => 'Click checkbox to show a Search button (providing your theme has a visible Search Button)'),
		);
		
	
	function init() {
		add_action('admin_menu',array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Menus'), __('Menus'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
	}

	function page_content() {
		$title =  $this->admin_heading('Genesis Club Menu Settings', GENESIS_CLUB_ICON);				
		$this->print_admin_form_with_sidebar($title, __CLASS__, $this->get_keys()); 
	}    

	
	function load_page() {
		$this->set_tooltips($this->tips);
 		$message = isset($_POST['options_update']) ? $this->save_menu() : '';
		$callback_params = array ('options' => Genesis_Club_Menu::get_options(), 'message' => $message);
		$this->add_meta_box('intro', 'Intro',  'intro_panel', $callback_params);
		$this->add_meta_box('menu', 'Menu Settings', 'menu_panel', $callback_params);
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel',$callback_params, 'side');
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}

	function save_menu() {
		check_admin_referer(__CLASS__);
		return $this->save_options('Genesis_Club_Menu', 'Menu');
	}

 	function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following section allows you to set up responsive hamburger menus on the primary, secondary and right header navigation areas.</p>
{$message}
INTRO_PANEL;
	}

	function search_panel($options){	
      return 	
         $this->fetch_form_field('search_menu', $options['search_menu'], 'radio', 
            array('primary' => 'Primary Navigation', 'secondary' => 'Secondary Navigation', 'header' => 'Header Right','none' => 'No Search Box')).
         $this->responsive_text_field("search_text",$options['search_text'], 10) .
         $this->fetch_form_field('search_button', $options['search_button'], 'checkbox');
   }	

  
	function hamburger_panel($options){	
      return 	
         $this->responsive_text_field("threshold",$options['threshold'], 4, 'px') .
         $this->responsive_text_field("icon_size",$options['icon_size'], 4, 'rem') .
         $this->responsive_text_field("icon_color",$options['icon_color'], 7, '', 'color-picker') .
         $this->responsive_menu_locations('primary', $options['primary']) .
         $this->responsive_menu_locations('secondary', $options['secondary']) .
         $this->responsive_menu_locations('header', $options['header']);
	}	

	private function responsive_menu_locations($name, $val) {
		$opts = array(
			'0' => 'No responsive menu', 
			'below' => 'Menu slides open below the hamburger');
		$sidr = array(
			'left' => 'Menu slides open on the left of the screen',
			'right' => 'Menu slides open on the right of the screen');	
		if (Genesis_Club_Utils::is_html5()) $opts = $opts + $sidr;	
		return $this->fetch_form_field($name,  $val, 'select', $opts) ;
	}

	private function responsive_text_field($name, $val, $size, $suffix='', $class='') {	
		$args = array('size'=> $size);
		if (!empty($suffix)) $args['suffix'] = $suffix;
		if (!empty($class)) $args['class'] = $class;
		return $this->fetch_text_field($name, $val, $args) ;
	}

	function menu_panel($post,$metabox) {
      $options = $metabox['args']['options'];
      $this->display_metabox( array(
         'Hamburger' => $this->hamburger_panel($options),
         'Search' => $this->search_panel($options),
  //     'Fixed Header' => $this->fixed_panel($options)
		));
	}

}
