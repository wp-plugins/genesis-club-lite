<?php
class Genesis_Club_Text_Widget extends Genesis_Club_Widget {

	private $tips = array(
			'text' => array('heading' => 'Text', 'tip' => 'Widget Content'),
			'autop' =>  array('heading' => 'Auto-paragraph', 'tip' => 'Click to convert automatically convert new lines to paragraph breaks.'),
			);
	
    private	$defaults = array('title' => '', 'html_title' => '', 'text' => '', 'autop' => false);

	
	function __construct() {
		$widget_ops = array('description' => __('Displays a Text widget with enhanced Title', GENESIS_CLUB_DOMAIN) );
		$control_ops = array();
		parent::__construct('genesis-club-text', __('Genesis Club Text', GENESIS_CLUB_DOMAIN), $widget_ops, $control_ops, $this->defaults);
	}

	function widget( $args, $instance ) {
      $args = $this->override_args($args, $instance) ;
      extract($args);
      echo $before_widget;
      $text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
      printf('<div class="textwidget">%1$s</div>', empty( $instance['filter'] ) ? $text : wpautop( $text ) );
      echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
		if (current_user_can('unfiltered_html') )
			$instance['text'] = $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['autop'] = isset($new_instance['autop']);
		return $instance;
	}

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);
		$this->print_form_field('text', 'textarea', array(), array('rows' => 16, 'cols' => 30, 'class' => 'widefat' ));
		$this->print_form_field('autop', 'checkbox');		
	}

}

class Genesis_Club_Facebook_Likebox_Widget extends Genesis_Club_Widget {
    const DOMAIN = 'GenesisClub'; //text domain for translation

    private	$defaults = array('title' => 'Like Us', 'href' => 'https://www.facebook.com/DIYWebMastery', 
    		'header' => true, 'small_header' => false, 'faces' => true,  'stream' => false,
    		'adaptive' => 'true', 'width' => 290, 'height' => '');

	private $tips = array(
			'href' => array('heading' => 'Facebook URL', 'tip' => 'URL of Facebook page. For example,  https://www,facebook.com/yourpage/'),
			'header' => array('heading' => 'Show Cover Photo', 'tip' => 'Show Cover Photo.'),
			'small_header' => array('heading' => 'Small Header', 'tip' => 'Use the small header'),
			'faces' => array('heading' => 'Show Faces', 'tip' => 'Show faces of those who liked this site'),
			'stream' => array('heading' => 'Show Posts', 'tip' => 'Show recent posts.'),
			'adaptive' => array('heading' => 'Autofit', 'tip' => 'Autofit Facebook social plugin into container'),
			'width' => array('heading' => 'Width', 'tip' => 'Set the width in pixels according to the width of your sidebar. Around 280px is typical.'),
			'height' => array('heading' => 'Height', 'tip' => 'Set the height in pixels based upon how many rows of face you want to display. Around 400px is good.'),
			);

	function __construct() {
		$widget_ops = array('description' => __('Displays a Facebook Page Socal Plugin Widget (replaces deprecated Facebook LikeBox)', GENESIS_CLUB_DOMAIN) );
		$control_ops = array();
		parent::__construct('genesis-club-likebox', __('Genesis Club Facebook Page', GENESIS_CLUB_DOMAIN), $widget_ops,$control_ops, $this->defaults );
	}


	function widget( $args, $instance ) {
      $args = $this->override_args($args, $instance) ;
      extract($args);
		echo $before_widget;
		printf( '<div class="fb-page" data-href="%1$s" data-hide-cover="%2$s" data-small-header="%3$s" data-show-facepile="%4$s" data-show-posts="%5$s" data-adapt-container-width="%6$s" data-width="%7$s" %8$s><div class="fb-xfbml-parse-ignore"><blockquote cite="%1$s"><a href="%1$s">Facebook</a></blockquote></div></div>', 
			$instance['href'], 
			$instance['header'] ? 'false' : 'true', 
			$instance['small_header'] ? 'true' : 'false', 
			$instance['faces'] ? 'true' : 'false', 
			$instance['stream'] ? 'true' : 'false', 
			$instance['adaptive'] ? 'true' : 'false', 
			empty($instance['width']) ? '' : $instance['width'],
			empty($instance['height']) ? '' : sprintf('data-height="%1$s"', $instance['height'])); 
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
		$instance['href'] = strip_tags( $new_instance['href'] );
		$instance['width'] = $new_instance['width'];	
		$instance['height'] = $new_instance['height'];	
		$instance['adaptive'] = empty($new_instance['adaptive']) ? 0 : 1;
		$instance['header'] = empty($new_instance['header']) ? 0 : 1;
		$instance['small_header'] = empty($new_instance['small_header']) ? 0 : 1;
		$instance['faces'] = empty($new_instance['faces']) ? 0 : 1;
		$instance['stream'] = empty($new_instance['stream']) ? 0 : 1;	
		return $instance;
	}

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);
		$this->print_form_field('href', 'textarea', array(), array('class' => 'widefat'));
		$this->print_form_field('width', 'text',array(), array('size' => 4 ,'maxlength' => 4, 'suffix' => 'px'));
		$this->print_form_field('height', 'text',array(), array('size' => 4 ,'maxlength' => 4, 'suffix' => 'px'));
		$this->print_form_field('adaptive', 'checkbox');
		$this->print_form_field('header','checkbox');
		$this->print_form_field('small_header', 'checkbox');
		$this->print_form_field('faces', 'checkbox');
		$this->print_form_field('stream', 'checkbox');
	}

}