<?php
/**
  Plugin Name: Events Manager - iPay88 Gateway
  Plugin URI: http://spurpress.com/
  Description: iPay88 Gateway for Events Manager. This requires Events Manager Pro plugin for it to work.
  Version: 0.9
  Author: Christopher Laconsay
  Author URI: http://chrislaconsay.com
  License: GPL-2.0+
 */
// If this file is called directly, abort.
if (!defined('ABSPATH'))
    exit;

/**
 * events manager pro is a pre-requisite
 */
function emp_ipay88_prereq() {
    ?> 
    <div class="error">
        <p><?php _e('Events Manager - iPay88 Gateway needs <a href="http://eventsmanagerpro.com/">Events Manager Pro</a> installed and activated for it to work.', 'events-manager-ipay88'); ?></p>
    </div>
    <?php
}

/**
 * initialise plugin once other plugins are loaded 
 */
function emp_ipay88_register() {
    //check that EM Pro is installed
    if (!defined('EMP_VERSION')) {
        add_action('admin_notices', 'emp_ipay88_prereq');
    }

    if (class_exists('EM_Gateways')) {
        require_once( plugin_dir_path(__FILE__) . 'class-ipay88-gateway.php' );
        EM_Gateways::register_gateway('ipay88', 'EM_Gateway_iPay88');
    }
}

add_action('plugins_loaded', 'emp_ipay88_register', 1000);
