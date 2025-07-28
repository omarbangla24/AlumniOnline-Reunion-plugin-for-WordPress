<?php
/**
 * Plugin Name: Reunion Registration
 * Description: A plugin for managing reunion event registrations, including a registration form, admin data view, and PDF acknowledgement slip generation.
 * Version: 3.7.0
 * Author: Omar Faruk
 * Text Domain: reunion-reg
 * Author URI: https://logicean.com
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('REUNION_REG_VERSION', '3.6.0');
define('REUNION_REG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REUNION_REG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
require_once REUNION_REG_PLUGIN_DIR . 'includes/class-reunion-registration-plugin.php';

// Instantiate the plugin class.
function reunion_reg_run_plugin() {
    return Reunion_Registration_Plugin::get_instance();
}
reunion_reg_run_plugin();

// Register activation hook.
register_activation_hook(__FILE__, ['Reunion_Registration_Plugin', 'activate']);

// Register update hook
add_action('plugins_loaded', function() {
    $plugin = Reunion_Registration_Plugin::get_instance();
    $plugin->update_db_check();
});