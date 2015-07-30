<?php
require_once(dirname(__FILE__).'/class-tooltip.php');

abstract class Genesis_Club_Widget extends WP_Widget {

   const ALLOWED_TAGS = '<a>,<img>,<span>,<i>,<em>,<br>';
   
	private $instance;
	private $tooltips;
   private $defaults = array('title' => '', 'html_title' => '');
   private $tips = array('title' => array('heading' => 'Label', 'tip' => 'Label appears only in the Widget Dashboard to make widget identification easier'),
                        'html_title' => array('heading' => 'Widget Title', 'tip' => 'Enhanced widget title can contain some HTML such as links, spans and breaks')
                        );

	public function __construct( $id_base, $name, $widget_options = array(), $control_options = array(), $defaults = false) {
	   $this->set_defaults($defaults);
      parent::__construct($id_base, $name, $widget_options, $control_options);
   }

	function get_defaults() {
		return $this->defaults;
	}

	function set_defaults($defaults) {
	  if (is_array($defaults) && (count($defaults) > 0))
	     $this->defaults = array_merge($this->defaults, $defaults);
	}

	public function override_args($args, &$instance) {	
		$instance = wp_parse_args( (array) $instance, $this->get_defaults() );
      $title = empty($instance['html_title']) ? '': $instance['html_title']; 
      if ( ! empty( $title ) ) $args['before_widget'] .= sprintf('%1$s%2$s%3$s',  $args['before_title'], $title, $args['after_title']);
      return $args;
   }

	public function update_instance($new_instance,  $old_instance) {
		$instance = wp_parse_args( (array) $old_instance, $this->get_defaults() );
		$instance['title'] = strip_tags($new_instance['title']);		
		$instance['html_title'] = strip_tags( $new_instance['html_title'],  self::ALLOWED_TAGS );	
      return $instance;
   }

	function form_init( $instance, $tips = false, $html_title = true) {
	  if (is_array($tips) && (count($tips) > 0))
	     $this->tips = array_merge($this->tips, $tips);
 	  $this->tooltips = new Genesis_Club_Tooltip($this->tips);
	  $this->instance = wp_parse_args( (array) $instance, $this->get_defaults() );      
	  $this->print_form_field('title', 'text', array(), array('size' => 20));
	  if ($html_title) $this->print_form_field('html_title', 'textarea', array(), array( 'class' => 'widefat' ));
	  print ('<hr />');	  
	}
   
	public function print_form_field($fld, $type, $options = array(), $args = array()) {
		print Genesis_Club_Utils::form_field( 
			$this->get_field_id($fld), $this->get_field_name($fld), 
			$this->tooltips->tip($fld), 
			isset($this->instance[$fld]) ? $this->instance[$fld] : false,
			$type, $options, $args);
	}
	
	function taxonomy_options ($fld) {
	  $selected = array_key_exists($fld, $this->instance) ? $this->instance[$fld] : '';
		$s = sprintf('<option %1$s value="%2$s">%3$s</option>', 
			selected('', $selected, false ), '', __('All Taxonomies and Terms', GENESIS_CLUB_DOMAIN ));
		$taxonomies = get_taxonomies( array('public' => true ), 'objects');
		foreach ( $taxonomies as $taxonomy ) {
			if ($taxonomy->name !== 'nav_menu') {
				$query_label = $taxonomy->name;
				$s .= sprintf('optgroup label="%1$s">', esc_attr( $taxonomy->labels->name ));
				$s .= sprintf('<option style="margin-left: 5px; padding-right:10px;" %1$s value="%2$s">%3$s</option>',
					selected( $query_label , $selected, false), 
					$query_label, $taxonomy->labels->all_items) ;
				$terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1');
				foreach ( $terms as $term ) 
					$s .= sprintf('<option %1$s value="%2$s">%3$s</option>',
						selected($query_label. ',' . $term->slug, $selected, false),
						$query_label. ',' . $term->slug, '-' . esc_attr( $term->name )) ;
				$s .= '</optgroup>';
			}
		}
		return  $s;
	}

}