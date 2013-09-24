<?php
/*
 * Plugin Name: Genesis Club Lite
 * Plugin URI: http://www.diywebmastery.com/plugins/genesis-club-lite/
 * Description: A toolbox of useful customisation functions for Genesis Child Themes.
 * Version: 1.1.1
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define('GENESIS_CLUB_VERSION','1.1.1');
define('GENESIS_CLUB_FRIENDLY_NAME', 'Genesis Club Lite') ;
define('GENESIS_CLUB_PLUGIN_NAME', plugin_basename(dirname(__FILE__))) ;
define('GENESIS_CLUB_PLUGIN_PATH', GENESIS_CLUB_PLUGIN_NAME.'/main.php');
define('GENESIS_CLUB_HOME_URL','http://www.diywebmastery.com/plugins/genesis-club-lite/');
$dir = dirname(__FILE__) . '/classes/';
require_once($dir . 'options.php');
require_once($dir . 'accordion.php');
require_once($dir . 'display.php');
require_once($dir . 'profile.php');
require_once($dir . 'facebook-widget.php');
if (is_admin()) {
	require_once($dir . 'tooltip.php');
	require_once($dir . 'admin.php');
	require_once($dir . 'accordion-admin.php');	
	require_once($dir . 'display-admin.php');
	require_once($dir . 'profile-admin.php');
	require_once($dir . 'post-admin.php');
	register_activation_hook(__FILE__, array('GenesisClubAdmin','activation'));
}

function genesis_club_init() {
	if ( basename( TEMPLATEPATH ) == 'genesis' ) { //can only initialize plugin if Genesis
		GenesisClubAccordion::init();	
		GenesisClubDisplay::init();	
		GenesisClubProfile::init();	
		GenesisClubLikebox::init();	
		if (is_admin()) {
			GenesisClubAdmin::init();
			GenesisClubAccordionAdmin::init();	
			GenesisClubDisplayAdmin::init();
			GenesisClubPostAdmin::init();			
			GenesisClubProfileAdmin::init();			
		}
	} else { //deactivate - this may happen on theme switch to non Genesis
		if (is_admin()) add_action('admin_init',array('GenesisClubAdmin','deactivate')); 
	}
}
add_action ('init', 'genesis_club_init',0);
?>