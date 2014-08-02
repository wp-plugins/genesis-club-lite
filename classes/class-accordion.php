<?php
class Genesis_Club_Accordion {

	protected static $defaults  = array(
		'accordions' => array('posts' => array(), 'terms' => array())
	);
	
	protected static $accordion = false; 

	public static function init() {
		Genesis_Club_Options::init(self::$defaults);
		if (!is_admin())  add_action('wp',array(__CLASS__,'prepare'));
	}	

	public static function prepare() {
		 	if (is_archive() || is_singular())				
				self::maybe_add_accordion();				
	}

	private static function empty_accordion($accordion) {
		return ! ($accordion && is_array($accordion) 		
		&& (array_key_exists('enabled',$accordion) 
		|| (array_key_exists('header_class',$accordion) && !empty($accordion['header_class'])) 
		|| (array_key_exists('content_class',$accordion) && !empty($accordion['content_class'])) 
		|| (array_key_exists('container_class',$accordion) && !empty($accordion['container_class'])) ));
	}

	public static function save_accordion($type, $id, $new_accordion) {
 		$accordions = Genesis_Club_Options::get_option('accordions');
		if (self::empty_accordion($new_accordion)) {
			if (is_array($accordions)
			&& array_key_exists($type,$accordions) 
			&& array_key_exists($id,$accordions[$type]))
				unset($accordions[$type][$id]); //delete it if it is present
		} else {
				$accordions[$type][$id] = $new_accordion ;
		}
		return Genesis_Club_Options::save_options( array('accordions' => $accordions));
	}

 	public static function get_accordion($type, $id, $accordions = false ) {
		if (!$accordions) $accordions = Genesis_Club_Options::get_option('accordions');
		if (is_array($accordions)
		&& array_key_exists($type, $accordions)
		&& array_key_exists($id, $accordions[$type]))
			return $accordions[$type][$id];
		else
			return false;	
 	}

 	private static function maybe_add_accordion() {
			if (($accordions = Genesis_Club_Options::get_option('accordions'))
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
    			array('jquery'), GENESIS_CLUB_VERSION, true);			
			add_action(is_admin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts', 
				array(__CLASS__, 'init_accordion'),20);		
			if (is_archive()) {
				add_filter('genesis_pre_get_option_content_archive', array(__CLASS__,'full_not_excerpt'));
				add_filter('genesis_pre_get_option_content_archive_limit', array(__CLASS__,'no_content_limit'));
				if (Genesis_Club_Options::is_html5())
					remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
				else
					remove_action( 'genesis_post_content', 'genesis_do_post_image' );				
			}
	}

    public static function full_not_excerpt($content) {
    		return 'full';
    }

    public static function no_content_limit($content) {
    		return 0; //do not limit the number of characters
    }

	public static function init_accordion() {
		if (is_admin()) 
			self::$accordion['header'] = 'h3';
		else
			if (Genesis_Club_Options::is_html5())
				self::$accordion['header'] = is_archive() ? 'article header' : '.entry-content h3';
			else
				self::$accordion['header'] = is_archive() ?  '.post > h2, .post .wrap > h2' : 'h3';
		unset(self::$accordion['enabled']);
		foreach (self::$accordion as $key => $val) if (empty($val)) unset(self::$accordion[$key]);
		$params = Genesis_Club_Options::json_encode(self::$accordion);	
		$container = is_admin() ? '#wpcontent .accordion' : ( Genesis_Club_Options::is_html5() ? 'main.content' : '#content');
		print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready( function() { jQuery('{$container}').gcaccordion({$params}); });
//]]>
</script>	
SCRIPT;
	}
}
