<?php
class Genesis_Club_Options {
	const OPTION_NAME  = 'genesis_club_options';
	protected static $options  = array();
	protected static $defaults = array( 'display_disabled' => false, 
		'accordion_disabled' => false, 'bar_disabled' => false, 'background_disabled' => false, 
		'calendar_disabled' => false, 'footer_disabled' => false, 'icons_disabled' => false, 
		'landing_disabled' => false, 'media_disabled' => false, 'menu_disabled' => false, 'seo_disabled' => false,
		'signature_disabled' => false, 'slider_disabled' => false, 'social_disabled' => false);

	protected static $landing_page_templates = array('page_landing.php');

	static function init($more = array()) {
		self::$defaults = array_merge(self::$defaults, (array)$more);
		self::$options = array(); //clear cache
	}	

	static function save_options($new_options) {
		$options = self::get_options(false);
		$new_options = shortcode_atts( self::get_defaults(), array_merge($options, $new_options));
		$updated = update_option(self::OPTION_NAME,$new_options);
		if ($updated) self::get_options(false);
		return $updated;
	}	

	static function get_defaults() {
		return self::$defaults;
	}

	static function get_options($cache = true) {
	   if ($cache && (count(self::$options) > 0)) return self::$options;
	   $the_options = get_option(self::OPTION_NAME);
	   self::$options = empty($the_options) ? self::get_defaults() : shortcode_atts( self::get_defaults(), $the_options);
	   return self::$options;
	}
	
	static function get_option($option_name, $cache = true) {
    	$options = self::get_options($cache);
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;    		
    }

	static function validate_options($options, $defaults) {
		if (is_array($options) && is_array($defaults) )
    		return shortcode_atts($defaults, $options);		
		else
    		return false;		
    }

	static function upgrade_options() {
		$new_options = array();
		$defaults = self::get_defaults();
		$options = get_option(self::OPTION_NAME);

		if (is_array($options)) {
			/* Remove old options and set defaults for new options */ 
			foreach ($defaults as $key => $subdefaults) 
				if (array_key_exists($key, $options) 
				&& is_array($options[$key]) 
				&& is_array($subdefaults)) 
					$new_options[$key] = shortcode_atts($subdefaults, $options[$key]);

			/* Move Display options into their own section */
			foreach ($options as $key => $value) 
				if ( array_key_exists($key, $defaults['display']) && !array_key_exists($key, $defaults))
					$new_options['display'][$key] = $value;
		} else {		
			$new_options = $defaults;
		}
		self::save_options($new_options);
	}

	static function get_meta ($post_id, $key) {
		if ($post_id && $key
		&& ($meta = get_post_meta($post_id, $key, true))
		&& ($options = @unserialize($meta))
		&& is_array($options))
			return $options;
		else 
			return false;
	}

	static function is_html5() {
		return function_exists('genesis_html5') && genesis_html5(); 
	}

	static function is_genesis2() {
		return function_exists('genesis_html5') ; 
	}
   
	static function json_encode($params) {
   		//fix numerics and booleans
		$pat = '/(\")([0-9]+)(\")/';	
		$rep = '\\2';
		return str_replace (array('"false"','"true"'), array('false','true'), 
			preg_replace($pat, $rep, json_encode($params)));
	} 
   
	static function selector($fld_id, $fld_name, $value, $options) {
		$input = '';
		if (is_array($options)) {
			foreach ($options as $optkey => $optlabel)
				$input .= sprintf('<option%1$s value="%2$s">%3$s</option>',
					selected($optkey, $value, false), $optkey, $optlabel); 
		} else {
			$input = $options;
		}
		return sprintf('<select id="%1$s" name="%2$s">%3$s</select>', $fld_id, $fld_name, $input);							
	}
   
	static function form_field($fld_id, $fld_name, $label, $value, $type, $options = array(), $args = array(), $separator = false) {
		if ($args) extract($args);
		$input = '';
		$label = sprintf('<label class="diy-label" for="%1$s">%2$s</label>', $fld_id, __($label));
		switch ($type) {
			case 'text':
				$input .= sprintf('<input type="text" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s %6$s/> %7$s',
					$fld_id, $fld_name, $value, 
					isset($size) ? ('size="'.$size.'"') : '', isset($maxlength) ? ('maxlength="'.$maxlength.'"') : '',
					isset($class) ? ('class="'.$class.'"') : '', isset($suffix) ? $suffix : '');
				break;
			case 'file':
				$input .= sprintf('<input type="file" id="%1$s" name="%2$s" accept="image/*" value="%3$s" %4$s %5$s %6$s/>',
					$fld_id, $fld_name, $value, 
					isset($size) ? ('size="'.$size.'"') : '', isset($maxlength) ? ('maxlength="'.$maxlength.'"') : '',
					isset($class) ? ('class="'.$class.'"') : '');
				break;
			case 'textarea':
				$input .= sprintf('<textarea id="%1$s" name="%2$s"%3$s%4$s%5$s>%6$s</textarea>',
					$fld_id, $fld_name, 
					isset($rows) ? (' rows="'.$rows.'"') : '', isset($cols) ? (' cols="'.$cols.'"') : '',
					isset($class) ? (' class="'.$class.'"') : '', $value);
				break;
			case 'checkbox':
				$input .= sprintf('<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" %3$svalue="1"/>',
					$fld_id, $fld_name, checked($value, '1', false));
				break;
			case 'radio': 
				if (is_array($options)) {
					if (array_key_exists('legend',$args) && ($legend = $args['legend']))
						$input .= sprintf('<legend class="screen-reader-text"><span>%1$s</span></legend>', $legend);
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<input type="radio" id="%1$s" name="%2$s" %3$s value="%4$s" /><label for="%1$s">%5$s</label>',
							$fld_id, $fld_name, str_replace('\'','"',checked($optkey, $value, false)), $optkey, $optlabel); 
					$input = sprintf('<fieldset class="diy-fieldset">%1$s</fieldset>',$input); 						
				}
				break;		
			case 'select': 
				$input =  self::selector($fld_id, $fld_name, $value, $options);							
				break;	
			case 'hidden': return sprintf('<input type="hidden" name="%1$s" value="%2$s" />', $fld_name, $value);	
			default: $input = $value;	
		}
		switch ($separator) {
			case 'tr': $format = '<tr><th scope="row" valign="top">%1$s</th><td>%2$s</td></tr>'; break;
			case 'br': $format = 'checkbox'==$type ? '%2$s%1$s<br/>' : '%1$s%2$s<br/>'; break;
			default: $format = strpos($input,'fieldset') !== FALSE ? '<div class="wrapfieldset">%1$s%2$s</div>' :'<p>%1$s%2$s</p>';
		}
		return sprintf($format, $label, $input);
	}

	static function is_mobile_device() {
		return  preg_match("/wap.|.wap/i", $_SERVER["HTTP_ACCEPT"])
    		|| preg_match("/iphone|ipad/i", $_SERVER["HTTP_USER_AGENT"]);
	} 

	static function get_post_id() {
		global $post;

		if (is_object($post) 
		&& property_exists($post, 'ID') 
		&& ($post_id = $post->ID))
			return $post_id ;
		else
			return false;
	}

	static function is_landing_page($page_template='') {
		
		if (empty($page_template)
		&& ($post_id = self::get_post_id()))
			$page_template = get_post_meta($post_id,'_wp_page_template',TRUE);
		
		if (empty($page_template)) return false;

		$landing_pages = (array) apply_filters('genesis_club_landing_page_templates', self::$landing_page_templates);
		return in_array($page_template, $landing_pages );
	}

	static function add_tooltip_support() {
		add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueue_tooltip_styles'));
		add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueue_color_picker_styles'));
		add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueue_color_picker_scripts'));
	}

	public static function register_styles() {
		wp_register_style('genesis-club-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
		wp_register_style('genesis-club-tooltip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
	}

	static function enqueue_tooltip_styles() {
		wp_enqueue_style('genesis-club-tooltip');
	}

	static function enqueue_admin_styles() {
		wp_enqueue_style('genesis-club-admin');
	}

	static function enqueue_color_picker_styles() {
        wp_enqueue_style('wp-color-picker');
	}

	static function enqueue_color_picker_scripts() {
		wp_enqueue_script('wp-color-picker');
		add_action('admin_print_footer_scripts', array(__CLASS__, 'enable_color_picker'));
 	}

    static function enable_color_picker() {
	    print <<< SCRIPT
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
	        $('.color-picker').wpColorPicker();
		});
		//]]>
	</script>
SCRIPT;
    }

}
