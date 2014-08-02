<?php
if (class_exists('Simple_Social_Icons_Widget'))	{
	class Genesis_Club_Icons_Widget extends Simple_Social_Icons_Widget	{

		function css() {  //improved CSS to support multiple widgets per page and avoid using !important
        	$all_instances = $this->get_settings();
        	foreach ( $all_instances as $id => $inst) {
				$instance = wp_parse_args( $inst, $this->defaults );
				Genesis_Club_Icons::add_css($instance);
        	}
			Genesis_Club_Icons::print_css_head(); //widget CSS goes in the header
		}
	}
}