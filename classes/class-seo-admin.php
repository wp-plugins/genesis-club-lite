<?php
class Genesis_Club_Seo_Admin extends Genesis_Club_Admin
{
   private $tab = 'redirect';


   private $yoast_installed = false;
   private $redirects;
   
   function is_yoast_installed() {
      return $this->yoast_installed;
   }

	function init() {
      $this->yoast_installed = defined('WPSEO_VERSION');
		require_once(dirname(__FILE__).'/class-seo-admin-redirects.php');
		$this->redirects = new Genesis_Club_Redirects_Admin($this->version, $this->path, $this->get_parent_slug(), $this->slug);
		add_action('admin_menu',array($this, 'admin_menu'));
	}

	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Genesis Club SEO'), __('SEO'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));			
	}		

	function page_content() {
		$title =  $this->admin_heading('Genesis Club SEO Settings');				
		$this->print_admin_form($title, __CLASS__, false, false, $this->get_tabs($_SERVER['REQUEST_URI'])); 
	}

	function load_page() {
 		add_action ('admin_enqueue_scripts', array($this, 'enqueue_styles')); 
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
		$tab = array_key_exists('tab',$_GET) ? $_GET['tab'] : 'redirects';		
		switch ($tab) {
			case 'thesis' : $this->load_page_thesis(); break;
			case 'yoast' : $this->load_page_yoast(); break;
			default: $this->redirects->load_page();
		}
	}

	function load_page_yoast() {
		require_once(dirname(__FILE__).'/class-seo-admin-yoast.php');
		$yoast = new Genesis_Club_Yoast_Admin($this->version, $this->path, $this->get_parent_slug(), $this->slug);
 		$yoast->load_page();
	}

	function load_page_thesis() {	
		require_once(dirname(__FILE__).'/class-seo-admin-thesis.php');
		$thesis = new Genesis_Club_Thesis_Admin($this->version, $this->path, $this->slug);
 		$thesis->load_page();
 	}
		
	function enqueue_styles() {
		$this->enqueue_admin_styles();	
		wp_enqueue_style($this->get_code('seo'), plugins_url('styles/seo.css',dirname(__FILE__)), array(),$this->get_version());
 		wp_enqueue_script($this->get_code('seo'), plugins_url('scripts/seo.js',dirname(__FILE__)), array(),$this->get_version());
  }
  
	function get_tabs($current_url) {
		$s='';
		$tabs = array(
			'redirects' =>  __( 'Migrate Yoast SEO Redirects' ),
			'yoast' =>  __( $this->is_yoast_installed() ? 'Genesis SEO to Yoast SEO Migration' : 'Yoast SEO to Genesis SEO Migration' ),
			'thesis' => __( 'Thesis To Genesis Migration' )  
		);
		
		if (strpos($current_url,'tab=') !== FALSE) {
			$tab=substr($current_url,strpos($current_url,'tab=')+4);
			if (strpos($tab,'&') !== FALSE) $tab = substr($tab,0,strpos($tab,'&'));
		} else {
			$tab = 'redirects';
		}
		foreach ( $tabs as $tab_id => $label ) {
			$url = admin_url(sprintf('admin.php?page=%1$s&tab=%2$s', $this->get_slug(), $tab_id));
			$s .= sprintf('<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
				$url, $tab == $tab_id ? ' nav-tab-active' : '', esc_html($label) );
		}
		return sprintf('<h3 class="nav-tab-wrapper">%1$s</h3>',$s); 
	}

}
