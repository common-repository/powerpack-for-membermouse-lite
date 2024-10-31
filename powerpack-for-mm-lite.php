<?php
/**
 * Plugin Name: PowerPack for MemberMouse Lite
 * Description: Extend MemberMouse with much needed features and integrations.
 * Version: 1.0.3
 * Plugin URI: https://www.powerpackmm.com/
 * Author: Mintun Media
 * Author URI: https://www.mintunmedia.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('PowerPack_Lite_Plugin')) {

  class PowerPack_Lite_Plugin {

    private static $instance;

    public static function instance() {
      // Check instance
      if (!isset(self::$instance)) {
        self::$instance = new self;
      }
      return self::$instance;
    }

    public function __construct() {
      // Make sure all the plugins are loaded so we can check if MM exists
      add_action('plugins_loaded', array($this, 'init'));
      add_action('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'ppfml_plugin_action_links'));
      add_action('wp_logout', array($this, 'ppfml_logout_redirect'));
    }

    /**
     * Function called on plugin activation
     * Description: checks if PowerPack Pro is installed and if so, does not allow this to be activated.
     */
    static function install() {
      // Deactivate Lite plugin if PRO plugin is already activated or becomes activated
      if (class_exists('PowerPackProPlugin')) {
        deactivate_plugins(plugin_basename(__FILE__));
        die("Awww Shucks! PowerPack Pro is Already Activated! You don't need this plugin anymore.");
      }

      // Check if MemberMouse is installed
      if (! class_exists('MemberMouse')) {
        die("Awww Shucks! MemberMouse is required for this plugin to work. Please activate MemberMouse and install this plugin again.");
      }

      // Set default options
      if (get_option('powerpack-plugin-options') === FALSE) {

        $options = array(
          'google-id' => '',
          'easyssl-mixed' => '',
          'logout-redirect' => '',
          'easyssl-support' => '',
          'easyssl-function' => '',
          'easyssl-httpsetup' => '',
          'easyssl-autoredirect' => '',
          'google-add-ecommerce' => '',
        );

        // Get default MemberMouse products and add to options
        $memberlevels = MM_MembershipLevel::getMembershipLevelsList();

        foreach ( $memberlevels as $memberlevel ) {
          // Add to options array for each level
          $options['mm-' . $memberlevel] = '';
        }

        add_option('powerpack-plugin-options', $options);
      }
    }

    /**
     * Register plugin settings and add admin menus
     */
    public function init() {

      if (class_exists('MemberMouse')) {

        define( 'PPLITE_DIR_PATH', plugin_dir_path(__FILE__) );
        define( 'PPLITE_DIR_URL', plugin_dir_url(__FILE__) );
        add_action('admin_menu', array($this, 'ppfml_admin_menu'));
        add_action('admin_init', array($this, 'ppfml_register_settings'));

        include_once( PPLITE_DIR_PATH . 'inc/ppliteecom.php' );
        include_once( PPLITE_DIR_PATH . 'inc/pplitessl.php' );
        include_once( PPLITE_DIR_PATH . 'inc/helpers.php' );

        if (is_admin()) {
          add_action('admin_enqueue_scripts', array($this, 'ppfml_load_scripts'));
        }
      }
    }

    /**
     * Add plugin action links.
     *
     * Add a link to the settings page on the plugins.php page.
     *
     * @since 1.0.0
     *
     * @param  array  $links List of existing plugin action links.
     * @return array         List of modified plugin action links.
     */
    public function ppfml_plugin_action_links($links) {
      $links = array_merge(array(
        '<a href="' . esc_url(admin_url('/admin.php?page=pp_lite')) . '">' . __('Settings', 'textdomain') . '</a>',
      ), $links);
      return $links;
    }

    /**
     * Add submenu page into MemberMouse Menu
     */
    public function ppfml_admin_menu() {
      add_submenu_page('mmdashboard', 'PowerPack for MemberMouse', 'PowerPack Lite', 'manage_options', 'pp_lite', array($this, 'ppfml_admin_html'));
    }

    /**
     * Register settings in WP Options table the WordPress way
     */
    public function ppfml_register_settings() {
      register_setting('powerpack-settings', 'powerpack-plugin-options');
    }

    /**
     * Markup for Admin dashboard
     */
    public function ppfml_admin_html() {
      include 'inc/admin-code.php';
    }

    /**
     * Load all scripts for plugin - css, select2, fontawesome, js
     */
    public function ppfml_load_scripts($hook) {
      if ('membermouse_page_pp_lite' != $hook) {return;}

      wp_enqueue_style('pplite_admin_css', plugins_url('/inc/css/pplite-admin.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'inc/css/pplite-admin.css'), 'all');
      wp_enqueue_style('pplite_select2_css', plugins_url('/inc/css/select2.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'inc/css/select2.css'), 'all');
      wp_enqueue_style('pplitefontawesome', 'https://use.fontawesome.com/releases/v5.0.8/css/all.css');
      wp_enqueue_script('pplite_admin_js', plugins_url('/inc/js/pplite-corejs.js', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'inc/js/pplite-corejs.js'), true);
      wp_enqueue_script('pplite_select2_js', plugins_url('/inc/js/select2.min.js', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'inc/js/select2.min.js'), true);
    }

    /**
     * New Logout Redirect function
     */
    public function ppfml_logout_redirect() {
      $options = get_option('powerpack-plugin-options');

      if( isset($options['logout-function']) && 1 == $options['logout-function'] ) {
        // Only change logout function if it's turned on.
        $logouturl = $this->ppfml_logout_url();

        wp_redirect($logouturl);
        exit;
      }
      return;
    }

      /**
     * Logout Functionality - Gets URL's for redirect function from WP Options
     * Defaults to MemberMouse Logout URL
     */
    public function ppfml_logout_url() {

      $options = get_option('powerpack-plugin-options');

      $logouturl = MM_CorePageEngine::getUrl(MM_CorePageType::$LOGOUT_PAGE); // Default URL to MemberMouse Logout URL

      if (isset($options['logout-redirect']) && "home" == $options['logout-redirect']) {
        $logouturl = get_home_url();
      } else if (isset($options['logout-redirect']) && "custom" == $options['logout-redirect']) {

        $curruser = MM_USER::getCurrentWPUser();
        $usermembership = $curruser->getMembershipName();

        if (isset($options['mm-' . $usermembership]) && intval($options['mm-' . $usermembership]) > 0) {
          $logouturl = get_permalink($options['mm-' . $usermembership]);
        }
      }

      return $logouturl;

    }

  } // End Class
}

PowerPack_Lite_Plugin::instance();

register_activation_hook(__FILE__, array('PowerPack_Lite_Plugin', 'install'));