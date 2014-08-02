<?php
if (!class_exists('Genesis_Club_Plugin')) { 
 class Genesis_Club_Plugin {

 	private static $path = GENESIS_CLUB_PLUGIN_PATH;
 	private static $slug = GENESIS_CLUB_PLUGIN_NAME;
 	private static $version = GENESIS_CLUB_VERSION;
	private static $modules = array(
		'accordion' => array('class'=> 'Genesis_Club_Accordion', 'heading' => 'Accordion', 'tip' => 'Create one or more accordions to display your frequently answered questions'),
		'background' => array('class'=> 'Genesis_Club_Background','heading' => 'Background', 'tip' => 'Add stylish image or video backgrounds to your pages.'),
		'bar' => array('class'=> 'Genesis_Club_Bar','heading' => 'Bar', 'tip' => 'Add an animated top bar for your calls to action'),
		'calendar' => array('class'=> 'Genesis_Club_Calendar','heading' => 'Calendar', 'tip' => 'Add an Google Calendar that can show your events in your visitors local time'),
		'display' => array('class'=> 'Genesis_Club_Display','heading' => 'Display', 'tip' => 'Extra widget areas and widgets, post meta overrides, page specific hiding, and many more useful features.'),
		'footer' => array('class'=> 'Genesis_Club_Footer','heading' => 'Footer', 'tip' => 'Boost site credibility using footer credits and trademark widgets'),
		'icons' => array('class'=> 'Genesis_Club_Icons','heading' => 'Icons', 'tip' => 'Enhanced Simple Social Icons allowing different sizes for different sets of icons on the same page.'),
		'landing' => array('class'=> 'Genesis_Club_Landing','heading' => 'Landing Pages', 'tip' => 'Use our lead capture forms for your landing pages. Integrates with Aweber, MailChimp, SendReach and Infusionsoft'),
		'media' => array('class'=> 'Genesis_Club_Media','heading' => 'Media', 'tip' => 'Must have features if you are <em>not</em> hosting all your media files in the Media Library'),
		'menu' => array('class'=> 'Genesis_Club_Menu','heading' => 'Menus', 'tip' => 'Use mobile responsive "hamburgers" for your primary, secondary and header right navigation menus'),
		'seo' => array('class'=> 'Genesis_Club_Seo','heading' => 'SEO Migration', 'tip' => 'SEO migrations from the Thesis theme and the WordPress SEO plugin'),
		'signature' => array('class'=> 'Genesis_Club_Signature','heading' => 'Signatures', 'tip' => 'Add author signatures to the foot of your posts'),
		'slider' => array('class'=> 'Genesis_Club_Slider','heading' => 'Slider', 'tip' => 'Deliver your message using animated words and images using a mobile responsive layer slider'),
		'social' => array('class'=> 'Genesis_Club_Social','heading' => 'Social Sharing', 'tip' => 'Add a floating or fixed social panel to get likes for your pages.'),
		);

    public static function get_path(){
		return self::$path;
	}

    public static function get_slug(){
		return self::$slug;
	}
	
	public static function get_version(){
		return self::$version;
	}

	public static function get_modules(){
		return self::$modules;
	}

    public static function get_modules_present(){
    	$modules = array();
    	$module_names = array_keys(self::$modules);
		foreach ($module_names as $module_name) 
			if (self::module_exists($module_name)) 
				$modules[$module_name] = self::$modules[$module_name];  	
		return $modules;
	}

	public static function module_exists($module) {
		return file_exists( dirname(__FILE__) .'/class-'. $module . '.php');
	}

	public static function is_genesis_present() {
		return basename( TEMPLATEPATH ) == 'genesis' ; //is genesis the current parent theme
	}

	public static function is_genesis_loaded() {
		return defined('GENESIS_LIB_DIR'); //is genesis actually loaded? (ie not been nobbled by another plugin) 
	}

	public static function init() {
		if (self::is_genesis_loaded()) {
			require_once (dirname(__FILE__) . '/class-options.php');
			$modules = array_keys(self::$modules);
			foreach ($modules as $module) 
				if (self::is_module_enabled($module))
					self::init_module($module);
		}
	}

	public static function admin_init() {
		if (self::is_genesis_loaded()) {
			require_once (dirname(__FILE__) . '/class-tooltip.php');
			require_once (dirname(__FILE__) . '/class-admin.php');		
			Genesis_Club_Admin::init();
			$modules = array_keys(self::$modules);			
			foreach ($modules as $module) 
				if (self::is_module_enabled($module))
					self::init_module($module, true);
 			if (self::get_activation_key(__CLASS__)) add_action('admin_init',array(__CLASS__, 'upgrade'));  
		}
	}

	public static function activate() { //called on plugin activation
    	if ( self::is_genesis_present() ) {
    		self::set_activation_key(__CLASS__);
		} else {
        	self::deactivate();
       		 wp_die(  __( sprintf('Sorry, you cannot use %1$s unless you are using a child theme based on the StudioPress Genesis theme framework. The %1$s plugin has been deactivated. Go to the WordPress <a href="%2$s">Plugins page</a>.',
        		GENESIS_CLUB_FRIENDLY_NAME, get_admin_url(null, 'plugins.php')), GENESIS_CLUB_PLUGIN_NAME ));
   		} 
	}
	
	public static function upgrade() { //apply any upgrades
		$modules = array_keys(self::$modules);
		foreach ($modules as $module) 
			if (self::is_module_enabled($module))
				self::upgrade_module($module);
		Genesis_Club_Options::upgrade_options();
		self::unset_activation_key(__CLASS__);
	}	

	private static function deactivate() {
		if (is_plugin_active(self::$path)) deactivate_plugins( self::$path); 
	}

	private static function init_module($module, $admin=false) {
		if (array_key_exists($module, self::$modules)
		&& ($class = self::$modules[$module]['class'])) {
			$prefix =  dirname(__FILE__) .'/class-'. $module;
			if ($admin) {
				$class = $class .'_Admin';
				$file = $prefix . '-admin.php';
				if (!class_exists($class) && file_exists($file)) {
					require_once($file);
					if (is_callable(array($class, 'init')))  call_user_func(array($class, 'init'));	
 				}
			} else {
				$file = $prefix . '.php';
				$widgets = $prefix . '-widgets.php';
				if (!class_exists($class) && file_exists($file)) {
					require_once($file);
					if (file_exists($widgets)) require_once($widgets);
					if (is_callable(array($class, 'init'))) call_user_func(array($class, 'init'));		
				}
			} 
		}
	}

	private static function upgrade_module($module) {	
		if (array_key_exists($module, self::$modules)
		&& ($class = self::$modules[$module]['class'])) {
			if (is_callable(array($class.'_Admin','upgrade'))) call_user_func(array($class.'_Admin', 'upgrade'));
			self::unset_activation_key($class);
		}
	}

	private static function activate_module($module) {
		if (array_key_exists($module, self::$modules)
		&& ($class = self::$modules[$module]['class'])
		&&  is_callable(array($class, 'activate'))) {
			call_user_func(array($class, 'activate'));
    		self::set_activation_key();
		}
	}

	public static function is_module_enabled($module) {
		return ! Genesis_Club_Options::get_option(self::get_disabled_key($module));
	}

    public static function get_disabled_key($module) { 
    	return $module . '_disabled'; 
    }

    private static function get_activation_key($class) { 
    	return get_option(self::activation_key_name($class)); 
    }

    private static function set_activation_key($class) { 
    	return update_option(self::activation_key_name($class), true); 
    }

    private static function unset_activation_key($class) { 
    	return delete_option(self::activation_key_name($class), true); 
    }

    private static function activation_key_name($class) { 
    	return strtolower($class) . '_activation'; 
    }
    
 }
}