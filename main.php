<?php
/*
 * Plugin Name: Genesis Club Lite
 * Plugin URI: http://www.diywebmastery.com/plugins/genesis-club-lite/
 * Description: A toolbox of useful customisation functions for Genesis Child Themes.
 * Version: 1.3.1
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
 
define('GENESIS_CLUB_VERSION','1.3.1');
define('GENESIS_CLUB_FRIENDLY_NAME', 'Genesis Club Lite') ;
define('GENESIS_CLUB_PLUGIN_NAME', plugin_basename(dirname(__FILE__))) ;
define('GENESIS_CLUB_PLUGIN_PATH', GENESIS_CLUB_PLUGIN_NAME.'/main.php');
define('GENESIS_CLUB_DOMAIN', 'GENESIS_CLUB_DOMAIN') ;  //text domain
define('GENESIS_CLUB_PRO_URL','http://www.genesisclubpro.com');
define('GENESIS_CLUB_SUPPORT_URL','http://www.diywebmastery.com/plugins/genesis-club-lite/');
require_once(dirname(__FILE__) . '/classes/class-plugin.php');
register_activation_hook(__FILE__, array('Genesis_Club_Plugin','activate'));
add_action('init', array('Genesis_Club_Plugin','init'),0);
if (is_admin()) add_action('init', array('Genesis_Club_Plugin','admin_init'),0);
?>