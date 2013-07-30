<?php
class GenesisClubIconsWidget extends Simple_Social_Icons_Widget	{

		function css() {  //improved CSS to support multiple widgets per page and avoid using !important
    		$css ='';
        	$all_instances = $this->get_settings();
        	foreach ( $all_instances as $id => $inst) {
				$instance = wp_parse_args( $inst, $this->defaults );
				$font_size = round( (int) $instance['size'] / 2 );
            	$icon_padding = round ( (int) $font_size / 2 );
    			$prefix = '#simple-social-icons-'.$id.'.simple-social-icons ul li';
    			$icss = <<< CSS
{$prefix} a,{$prefix} a:hover {
background-color: {$instance['background_color']};
-moz-border-radius: {$instance['border_radius']}px;
-webkit-border-radius: {$instance['border_radius']}px;
border-radius: {$instance['border_radius']}px;
color: {$instance['icon_color']};
font-size: {$font_size}px;
padding: {$icon_padding}px;
}

{$prefix} a:hover {
background-color: {$instance['background_color_hover']};
color: {$instance['icon_color_hover']};
}
CSS;
				$css .= $icss;
        	}
			printf('<style type="text/css" media="screen">%1$s</style>', 
				str_replace( array( "\t", "\n", "\r" ), array(""," "," "),$css));
		}
}