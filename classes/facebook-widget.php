<?php
class GenesisClubLikebox {
    const CLASSNAME = 'GenesisClubLikebox'; //this class
	const DOMAIN = 'GenesisClub';
	const LSVERSION ='4.6.0';	
	
	public static function init() {
		if (! GenesisClubOptions::get_option('likebox_disabled')) {
			add_action('widgets_init',array(self::CLASSNAME,'register_widgets'));		
			if (!is_admin())  add_action('wp',array(self::CLASSNAME,'prepare'));
		}
	}	

	public static function register_widgets() {
		register_widget( 'GenesisClubFacebookLikeboxWidget' );
	}	

	public static function prepare() {
		add_action( 'genesis_before', array(self::CLASSNAME,'add_fb_root') );
	}	

	public static function add_fb_root() {
		print <<< SCRIPT
			<div id="fb-root"></div>
<script type="text/javascript">(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
SCRIPT;
	}
}


class GenesisClubFacebookLikeboxWidget extends WP_Widget	{
    const DOMAIN = 'GenesisClub'; //text domain for translation
    private	$defaults = array('title' => 'Like Us', 'href' => 'https://www.facebook.com/DIYWebMastery', 
    		'header' => 'false', 'faces' => 'true', 'border' => 'false', 'stream' => 'false',
    		'colorscheme' => 'light', 'width' => 290, 'height' => '');

	function __construct() {
		$widget_ops = array('description' => __('Displays a Facebook Likebox', self::DOMAIN) );
		parent::__construct('genesis-club-likebox', __('Genesis Club Facebook Likebox', self::DOMAIN), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$height = empty($instance['height']) ? '' : sprintf('data-height="%1$s"', $instance['height']);
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;
		printf( '<div class="fb-like-box" data-href="%1$s" data-header="%2$s" data-show-faces="%3$s" data-stream="%4$s" data-show-border="%5$s" data-colorscheme="%6$s" data-width="%7$s" %8$s></div>', 
			$instance['href'], $instance['header'], $instance['faces'], $instance['stream'], 
			$instance['border'], $instance['colorscheme'],
			$instance['width'], $height); 
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $old_instance, $this->defaults );;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['href'] = strip_tags( $new_instance['href'] );
		$instance['header'] = empty($new_instance['header']) ? 'false' : 'true';
		$instance['faces'] = empty($new_instance['faces']) ? 'false' : 'true';
		$instance['stream'] = empty($new_instance['stream']) ? 'false' : 'true';	
		$instance['border'] = empty($new_instance['border']) ? 'false' : 'true';
		$instance['colorscheme'] = $new_instance['colorscheme'];
		$instance['width'] = $new_instance['width'];	
		$instance['height'] = $new_instance['height'];	
		return $instance;
	}

	function form_field($fld, $label,  $value, $type, $options = array(), $args = array()) {
		$fld_id = $this->get_field_id($fld);
		$fld_name = $this->get_field_name($fld);
		if ($args) extract($args);
		$input = '';
		$label = sprintf('<label for="%1$s">%2$s</label>', $fld_id, __($label));
		switch ($type) {
			case 'text':
				$input .= sprintf('<input type="text" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s %6$s/> %7$s',
					$fld_id, $fld_name, $value, 
					isset($size) ? ('size="'.$size.'"') : '', isset($maxlength) ? ('maxlength="'.$maxlength.'"') : '',
					isset($class) ? ('class="'.$class.'"') : '', isset($suffix) ? $suffix : '');
				return sprintf('<p>%1$s%2$s</p>', $label, $input);
				break;
			case 'checkbox':
				$input .= sprintf('<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" %3$svalue="1"/>',
					$fld_id, $fld_name, checked($value, 'true', false));
				return sprintf('%1$s%2$s<br/>',$input, $label);
				break;
			case 'radio': 
				if (is_array($options)) 
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<input type="radio" id="%1$s" name="%2$s" %3$s value="%4$s" />&nbsp;%5$s&nbsp;&nbsp;',
							$fld_id, $fld_name, checked($optkey, $value, false), $optkey, $optlabel); 
				return sprintf('<p>%1$s%2$s</p>', $label, $input);							
				break;		
		}
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		echo $this->form_field('title', 'Title: ', $instance['title'], 'text');
		echo $this->form_field('href', 'Your Full Facebook Page URL: ', $instance['href'], 'text',array(), array('class' => 'widefat'));
		echo $this->form_field('faces', 'Show Faces: ', $instance['faces'], 'checkbox');
		echo $this->form_field('header', 'Show Header: ', $instance['header'], 'checkbox');
		echo $this->form_field('border', 'Show Border: ', $instance['border'], 'checkbox');
		echo $this->form_field('stream', 'Show Stream: ', $instance['stream'], 'checkbox');
		echo $this->form_field('colorscheme', 'Color Scheme: ', $instance['colorscheme'], 'radio', array('light' => 'Light', 'dark' => 'Dark'));
		echo $this->form_field('width', 'Width: ', $instance['width'], 'text',array(), 
			array('size' => 6 ,'maxlength' => 4, 'suffix' => 'px'));
		echo $this->form_field('height', 'Height: ', $instance['height'], 'text',array(), 
			array('size' => 6 ,'maxlength' => 4, 'suffix' => 'px'));
	}
}