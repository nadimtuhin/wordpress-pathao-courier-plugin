<?php

/**
 * Plugin Name: Pathao Courier
 * Description: Pathao Courier
 * Version: 1.0.0
 * Author: Pathao
 * Text Domain: pathao-courier
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

defined( 'PTC_PLUGIN_URL' ) || define( 'PTC_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
defined( 'PTC_PLUGIN_DIR' ) || define( 'PTC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'PTC_PLUGIN_FILE' ) || define( 'PTC_PLUGIN_FILE', plugin_basename( __FILE__ ) );
defined( 'PTC_PLUGIN_PREFIX' ) || define( 'PTC_PLUGIN_PREFIX', 'ptc' );


require_once PTC_PLUGIN_DIR.'/settings-page.php';
require_once PTC_PLUGIN_DIR.'/pathao-bridge.php';
require_once PTC_PLUGIN_DIR.'/plugin-api.php';
require_once PTC_PLUGIN_DIR.'/wc-order-list.php';

// Enqueue styles and scripts
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_script');
function enqueue_custom_admin_script() {
    // localize the script to your domain name, so that you can reference the ajax_url later
    wp_localize_script('my-custom-admin-js', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_localize_script('my-custom-script', 'ajax_params', array('ajax_url' => admin_url('admin-ajax.php')));
    
    wp_enqueue_style(
        'my-custom-admin-css', 
        plugin_dir_url(__FILE__) . 'css/admin-style.css',
        null,
        filemtime(plugin_dir_path( __FILE__ ) . '/css/admin-style.css'),
        'all'
    );
    // wp_enqueue_script('my-custom-admin-js', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), null, true);
    wp_enqueue_script(
        'my-custom-admin-js',
        plugin_dir_url(__FILE__) . 'js/admin-script.js',
        ['jquery'],
        filemtime(plugin_dir_path( __FILE__ ) . '/js/admin-script.js'),
        true
    );
}