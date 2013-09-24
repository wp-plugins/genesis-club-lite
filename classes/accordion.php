<?php
class GenesisClubAccordion {
    const CLASSNAME = 'GenesisClubAccordion'; //this class
	const DOMAIN = 'GenesisClub';
	
	protected static $defaults  = array(
		'accordions' => array()
	);
	
	protected static $accordion = false; 

	public static function init() {
		GenesisClubOptions::init(self::$defaults);
		if ( ! GenesisClubOptions::get_option('accordion_disabled')) {
			if (!is_admin())  add_action('wp',array(self::CLASSNAME,'prepare'));
		}
	}	

	public static function prepare() {
		 	if (is_archive() || is_singular())				
				self::maybe_add_accordion();				
	}

	private static function empty_accordion($accordion) {
		return ! ($accordion && is_array($accordion) 
		&& array_key_exists('header_class',$accordion)  && array_key_exists('content_class',$accordion) 
		&& (array_key_exists('enabled',$accordion) || !empty($accordion['header_class'])  || !empty($accordion['content_class']) ));
	}

	public static function save_accordion($type, $id, $new_accordion) {
 		$accordions = GenesisClubOptions::get_option('accordions');
		if (self::empty_accordion($new_accordion)) {
			if (is_array($accordions)
			&& array_key_exists($type,$accordions) 
			&& array_key_exists($id,$accordions[$type]))
				unset($accordions[$type][$id]); //delete it if it is present
		} else {
				$accordions[$type][$id] = $new_accordion ;
		}
		return GenesisClubOptions::save_options( array('accordions' => $accordions));
	}

 	public static function get_accordion($type, $id, $accordions = false ) {
		if (!$accordions) $accordions = GenesisClubOptions::get_option('accordions');
		if (is_array($accordions)
		&& array_key_exists($type, $accordions)
		&& array_key_exists($id, $accordions[$type]))
			return $accordions[$type][$id];
		else
			return false;	
 	}

 	private static function maybe_add_accordion() {
			if (($accordions = GenesisClubOptions::get_option('accordions'))
			&& ($id = get_queried_object_id()) 
		    && ((is_singular() && ($accordion = self::get_accordion('posts', $id, $accordions)))	
			 || (is_archive() && ($accordion = self::get_accordion('terms', $id, $accordions))))
			&& array_key_exists('enabled',$accordion)) 
				self::add_accordion($accordion);				
	}

	public static function add_accordion ($accordion) {
			self::$accordion = $accordion; //save for later
	   		wp_enqueue_style('gc-accordion', plugins_url('styles/accordion.css', dirname(__FILE__)), 
    			array(), GENESIS_CLUB_VERSION);		
    		wp_enqueue_script('gc-accordion', plugins_url('scripts/jquery.accordion.js', dirname(__FILE__)), 
    			array('jquery','jquery-ui-accordion'), GENESIS_CLUB_VERSION, true);			
			add_action(is_admin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts', 
				array(self::CLASSNAME, 'init_accordion'),10);		
			if (is_archive()) {
				add_filter('genesis_pre_get_option_content_archive', array(self::CLASSNAME,'full_not_excerpt'));
				add_filter('genesis_pre_get_option_content_archive_limit', array(self::CLASSNAME,'no_content_limit'));
			}
	}

    public static function full_not_excerpt($content) {
    		return 'full';
    }

    public static function no_content_limit($content) {
    		return 0; //do not limit the number of characters
    }

	public static function init_accordion() {
		unset(self::$accordion['enabled']);
		self::$accordion['header'] = is_archive() ? 'h2' : 'h3'; 
		foreach (self::$accordion as $key => $val) if (empty($val)) unset(self::$accordion[$key]);
		$params = GenesisClubOptions::json_encode(self::$accordion);	
		$container = is_admin() ? '#wpcontent' : ( GenesisClubOptions::is_html5() ? 'main.content' : '#content');
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
	jQuery('{$container}').gcaccordion({$params});
//]]>
</script>	
SCRIPT;
	}
}
?>