<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://codingfix.com
 * @since      1.0.0
 *
 * @package    Instalist
 * @subpackage Instalist/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Instalist
 * @subpackage Instalist/includes
 * @author     Marco Gasi <info@codingfix.com>
 */
class Instalist {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Instalist_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'INSTALIST_VERSION' ) ) {
			$this->version = INSTALIST_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'instalist';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Instalist_Loader. Orchestrates the hooks of the plugin.
	 * - Instalist_i18n. Defines internationalization functionality.
	 * - Instalist_Admin. Defines all hooks for the admin area.
	 * - Instalist_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		require_once plugin_dir_path( __DIR__ ) . 'includes/class-instalist-cpt.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-instalist-metabox.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-instalist-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-instalist-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-instalist-admin.php';

		$this->loader = new Instalist_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Instalist_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Instalist_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Instalist_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );

		/**
		 * Managing plugin within a list
		 */

		// install selected plugins from a list.
		$this->loader->add_action( 'wp_ajax_inslst_install_selected_plugins', $plugin_admin, 'inslst_install_selected_plugins' );
		$this->loader->add_action( 'wp_ajax_nopriv_inslst_install_selected_plugins', $plugin_admin, 'inslst_install_selected_plugins' );

		// install all plugins of a list from within the list.
		$this->loader->add_action( 'wp_ajax_inslst_install_all_plugins', $plugin_admin, 'inslst_install_all_plugins' );
		$this->loader->add_action( 'wp_ajax_nopriv_inslst_install_all_plugins', $plugin_admin, 'inslst_install_all_plugins' );
		$this->loader->add_action( 'admin_post_inslst_install_all_plugins', $plugin_admin, 'inslst_install_all_plugins' );

		// install all plugins of a list from inline actions.
		$this->loader->add_action( 'wp_ajax_inslst_install_list', $plugin_admin, 'inslst_install_list' );
		$this->loader->add_action( 'wp_ajax_nopriv_inslst_install_list', $plugin_admin, 'inslst_install_list' );

		/**
		 * Manage plugin lists
		 */

		// export a plugin list.
		$this->loader->add_action( 'wp_ajax_inslst_export_plugin_list', $plugin_admin, 'inslst_export_plugin_list' );
		$this->loader->add_action( 'wp_ajax_npopriv_inslst_export_plugin_list', $plugin_admin, 'inslst_export_plugin_list' );
		$this->loader->add_action( 'admin_post_inslst_export_plugin_list', $plugin_admin, 'inslst_export_plugin_list' );

		// import a plugin list.
		$this->loader->add_action( 'wp_ajax_inslst_import_plugin_list', $plugin_admin, 'inslst_import_plugin_list' );
		$this->loader->add_action( 'wp_ajax_nopriv_inslst_import_plugin_list', $plugin_admin, 'inslst_import_plugin_list' );
		$this->loader->add_action( 'admin_post_inslst_import_plugin_list', $plugin_admin, 'inslst_import_plugin_list' );

		$this->loader->add_action( 'admin_post_inslst_get_plugins_from_repo', $plugin_admin, 'inslst_get_plugins_from_repo' );
		$this->loader->add_action( 'wp_ajax_inslst_get_plugins_from_repo', $plugin_admin, 'inslst_get_plugins_from_repo' );
		$this->loader->add_action( 'wp_ajax_nopriv_inslst_get_plugins_from_repo', $plugin_admin, 'inslst_get_plugins_from_repo' );

		$this->loader->add_action( 'wp_ajax_inslst_get_installed_plugins', $plugin_admin, 'inslst_get_installed_plugins' );
		$this->loader->add_action( 'wp_ajax_nopriv_inslst_get_installed_plugins', $plugin_admin, 'inslst_get_installed_plugins' );

		$this->loader->add_action( 'wp_ajax_inslst_check_plugin_list_count', $plugin_admin, 'inslst_check_plugin_list_count' );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'inslst_show_title_error_notice' );

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'inslst_add_action_links' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Instalist_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
