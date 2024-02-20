<?php
/**
 * Plugin Name: WC Categories Menu Customizer
 * Plugin URI: https://buyreadysite.com/
 * Description: Allows easy creation and updating of a WooCommerce categories menu directly from the WordPress admin dashboard. Simply set your desired menu name and click create to auto-populate your menu with product categories.
 * Version: 1.0
 * Author: BuyReadySite.com
 * Author URI: https://buyreadysite.com/about-us
 * Text Domain: woocommerce-categories-menu-creator
 * Domain Path: /languages/
 * Requires at least: 5.5
 * Tested up to: 6.4.3
 * Requires PHP: 7.1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 */
function brs_check_woocommerce_active() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action( 'admin_notices', 'brs_woocommerce_not_active_notice' );
        return false;
    }

    return true;
}

/**
 * Admin notice for when WooCommerce is not active
 */
function brs_woocommerce_not_active_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e( 'WooCommerce Categories Menu Creator requires WooCommerce to be installed and active.', 'woocommerce-categories-menu-creator' ); ?></p>
    </div>
    <?php
}

if ( brs_check_woocommerce_active() ) {
    include_once plugin_dir_path( __FILE__ ) . 'admin/menu-page.php';
    add_action('admin_menu', 'brs_woocommerce_categories_menu_creator_menu');
    add_action('init', 'brs_load_textdomain'); // Перемещено сюда
}

add_action('admin_menu', 'brs_woocommerce_categories_menu_creator_menu');

function brs_woocommerce_categories_menu_creator_menu() {
    add_menu_page(
        __('WooCommerce Categories Menu', 'woocommerce-categories-menu-creator'), // Page title
        __('WC Categories Menu', 'woocommerce-categories-menu-creator'), // Menu title
        'manage_options', // Capability
        'woocommerce-categories-menu-creator', // Menu slug
        'brs_woocommerce_categories_menu_creator_page', // Функция должна совпадать с объявленной
        'dashicons-menu' // Icon URL
    );
}


function brs_load_textdomain() {
    load_plugin_textdomain('woocommerce-categories-menu-creator', false, basename(dirname(__FILE__)) . '/languages/');
}
