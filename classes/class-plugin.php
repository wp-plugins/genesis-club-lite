<?php
if (!class_exists('Genesis_Club_Plugin')) { 
 class Genesis_Club_Plugin {

 	private static $path = GENESIS_CLUB_PLUGIN_PATH;
 	private static $slug = GENESIS_CLUB_PLUGIN_NAME;
 	private static $version = GENESIS_CLUB_VERSION;
	private static $admin_modules = array();
	private static $modules = array(
//		'api' => array('class'=> 'Genesis_Club_API','heading' => 'API Keys', 'tip' => 'Check your Genesis Club Pro license is up to date and if a new version of the plugin is available.'),
		'accordion' => array('class'=> 'Genesis_Club_Accordion', 'heading' => 'Accordion', 'tip' => 'Create one or more accordions to display your frequently answered questions'),
		'background' => array('class'=> 'Genesis_Club_Background','heading' => 'Background', 'tip' => 'Add stylish image or video backgrounds to your pages.'),
		'bar' => array('class'=> 'Genesis_Club_Bar','heading' => 'Bar', 'tip' => 'Add an animated top bar for your calls to action'),
		'calendar' => array('class'=> 'Genesis_Club_Calendar','heading' => 'Calendar', 'tip' => 'Add an Google Calendar that can show your events in your visitors local time'),
		'display' => array('class'=> 'Genesis_Club_Display','heading' => 'Display', 'tip' => 'Extra widget areas and widgets, post meta overrides, page specific hiding, and many more useful features.'),
		'fonts' => array('class'=> 'Genesis_Club_Fonts','heading' => 'Fonts', 'tip' => 'Add Google Fonts and Google Font Effects to add variety to your titles and landing pages'),
		'footer' => array('class'=> 'Genesis_Club_Footer','heading' => 'Footer', 'tip' => 'Boost site credibility using footer credits and trademark widgets'),
		'icons' => array('class'=> 'Genesis_Club_Icons','heading' => 'Icons', 'tip' => 'Enhanced Simple Social Icons allowing different sizes for different sets of icons on the same page.'),
		'landing' => array('class'=> 'Genesis_Club_Landing','heading' => 'Landing Pages', 'tip' => 'Use our lead capture forms for your landing pages. Integrates with Aweber, MailChimp, SendReach and Infusionsoft'),
		'media' => array('class'=> 'Genesis_Club_Media','heading' => 'Media', 'tip' => 'Must have features if you are <em>not</em> hosting all your media files in the Media Library'),
		'menu' => array('class'=> 'Genesis_Club_Menu','heading' => 'Menus', 'tip' => 'Add a mobile responsive hamburger and a search box to your primary, secondary and header right menus'),
		'post' => array('class'=> 'Genesis_Club_Post','heading' => 'Post Widgets', 'tip' => 'Enhanced widgets for displaying post specific information and image galleries'),
		'seo' => array('class'=> 'Genesis_Club_Seo','heading' => 'SEO ', 'tip' => 'Page redirects (in Lite and Pro) and SEO Migration Tools for Yoast and Thesis (in Pro only)'),
		'signature' => array('class'=> 'Genesis_Club_Signature','heading' => 'Signatures', 'tip' => 'Add an author signatures, with a PS or PPS to personalize of your posts'),
		'slider' => array('class'=> 'Genesis_Club_Slider','heading' => 'Slider', 'tip' => 'Deliver your key message in animated words and images using a mobile responsive multi-layer slider'),
		'social' => array('class'=> 'Genesis_Club_Social','heading' => 'Social Sharing', 'tip' => 'Add a floating or fixed social panel to allow users to share your pages.'),
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
		return substr(basename( TEMPLATEPATH ), 0,7) == 'genesis' ; //is genesis the current parent theme
	}

	public static function is_genesis_loaded() {
		return defined('GENESIS_LIB_DIR'); //is genesis actually loaded? (ie not been nobbled by another plugin) 
	}

	public static function init() {
		$d = dirname(__FILE__) . '/';
		require_once ($d . 'class-diy-options.php');
		require_once ($d . 'class-options.php');
		require_once ($d . 'class-utils.php');
		require_once ($d . 'class-widget.php');
		Genesis_Club_Options::init();
		if (self::is_genesis_loaded()) {
			$modules = array_keys(self::$modules);
			foreach ($modules as $module) 
				if (self::is_module_enabled($module))
					self::init_module($module);
         add_action('wp', array(__CLASS__,'maybe_enqueue_tooltip_styles' ));
		}
	}

	public static function admin_init() {
		if (self::is_genesis_loaded()) {
			$d = dirname(__FILE__) . '/';		
			require_once ($d . 'class-tooltip.php');
			require_once ($d . 'class-admin.php');
			require_once ($d . 'class-feed-widget.php');
			require_once ($d . 'class-dashboard.php');
			new Genesis_Club_Dashboard(self::$version, self::$path, self::$slug);
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
       		 wp_die(  __( sprintf('Sorry, you cannot use %1$s unless you are using a child theme based on the StudioPress Genesis theme framework. The %1$s plugin has been deactivated. Go to the WordPress <a href="%2$s"><em>Plugins page</em></a>.',
        		GENESIS_CLUB_FRIENDLY_NAME, get_admin_url(null, 'plugins.php')), GENESIS_CLUB_DOMAIN ));
   		} 
	}
	
	public static function upgrade() { //apply any upgrades
		self::check_multiple_versions();	

		$modules = array_keys(self::$modules);
		foreach ($modules as $module) 
			if (self::is_module_enabled($module))
				self::upgrade_module($module);
		Genesis_Club_Options::upgrade_options();
		self::unset_activation_key(__CLASS__);
	}	

	private static function deactivate($path ='') {
		if (empty($path)) $path = self::$path;
		if (is_plugin_active($path)) deactivate_plugins( $path );
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
					self::$admin_modules[$module] = new $class(self::$version, self::$path, self::$slug, $module);
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

	public static function call_module_func($module, $func) {	
		if (array_key_exists($module, self::$modules)
		&& array_key_exists($module, self::$admin_modules)
		&& is_callable(array( self::$admin_modules[$module], $func))) {
			return call_user_func(array(self::$admin_modules[$module], $func));
		}
	}

	private static function upgrade_module($module) {	
		if (array_key_exists($module, self::$modules)
		&& ($class = self::$modules[$module]['class'])) {
			if (array_key_exists($module, self::$admin_modules)
			&& is_callable(array( self::$admin_modules[$module],'upgrade'))) 
				call_user_func(array(self::$admin_modules[$module], 'upgrade'));
			self::unset_activation_key($class);
		}
	}

	private static function activate_module($module) {
		if (array_key_exists($module, self::$modules)
		&& ($class = self::$modules[$module]['class'])
		&&  is_callable(array($class, 'activate'))) {
			call_user_func(array($class, 'activate'));
    		self::set_activation_key($class);
		}
	}


	public static function is_post_type_enabled($post_type){
		return in_array($post_type, array('post', 'page')) || self::is_custom_post_type_enabled($post_type);
	}

	public static function is_custom_post_type_enabled($post_type){
		return in_array($post_type, (array)Genesis_Club_Options::get_option('custom_post_types'));
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
    
	private static function check_multiple_versions() {
		if (is_plugin_active('genesis-club-pro/main.php') 
		&& is_plugin_active('genesis-club-lite/main.php')) {
			self::deactivate('genesis-club-pro/main.php'); 
			self::deactivate('genesis-club-lite/main.php'); 
       		 wp_die(  __( sprintf('You cannot run both Genesis Club Lite and Genesis Club Pro at the same time.<br/><strong>Both have been deactivated</strong>.<br/>Now go to the WordPress <a href="%1$s" style="text-decoration:underline"><em>Plugins page</em></a> and activate the one you want to use.',
        		 get_admin_url(null, 'plugins.php?s=genesis%20club')), GENESIS_CLUB_DOMAIN ));			 
		}
	}

	static function maybe_enqueue_tooltip_styles() {
	   /* Add Genesis Club Widgets Tooltip CSS for Beaver Builder Editor */
      if ( class_exists('FLBuilderModel')
      && is_callable(array('FLBuilderModel', 'is_builder_active')) 
      && FLBuilderModel::is_builder_active() ) {
         add_action('wp_enqueue_scripts', array('Genesis_Club_Utils', 'register_tooltip_styles'));
         add_action('wp_enqueue_scripts', array('Genesis_Club_Utils', 'enqueue_tooltip_styles'));
      }
	}		
	
 }
}