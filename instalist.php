<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://codingfix.com
 * @since             1.0.1
 * @package           Instalist
 *
 * @wordpress-plugin
 * Plugin Name:       Instalist
 * Plugin URI:        https://codingfix.com
 * Description:       Create lists of favorite plugins and install/activate them all with just a click.
 * Version:           1.0.4
 * Author:            Codingfix
 * Author URI:        https://codingfix.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       instalist
 * Domain Path:       /languages
 */
// [x]: files are deleted but still appears in media library. Clicking on View link shows a page eith just the file name!
// [ ]: if user lets tab open on list-editor and wpnonce expires the list_name result to be empty because nonce verification fails
// [x]: quando si importa una lista il nome della lista viene salvato con l'estensione csv e se si esporta viene aggiunta un'altra estensione csv
// [x]: le id dei bottoni in list table devono essere diverse o meglio bisogna trasformarle in classi: export_list_button, install_all_plugin_button
// [x]: check in repo plugins without icon: maybe they have icon in [2x] item
// [x]: if user type in the slug give a automatic name
// [x]: if activation fails for one fails for all next plugins in the queue
// [x]: modificare i require_once in include_once in LSFT ma rimane il problema e un plugin richiede un altro plugin: anche scrivendo nel DB poi le pagine admin sono bianche
// [x] loop through get_plugins function result and for each plugin call get_plugins_from_repo function then pass result to the javascript which will fill the box with all installed plugins. In te interface remove the checkbox and add a button Get Installed plugins.
// [x] plugins-overlay no va bene vedere di aggiungere uno spinner nel bottone o vicino al bottone
// [x] INSTALL SELECTED BUTTON IS DISABLED IN PREMIUM VERSION
// [x] AGGIUSTARE JAVASCRIPT PER AGGIUNGERE A DIV E NON A TABLE
// [x] SECNDO CLICK SULLA LISTA DEI PLUGIN RICERCATI inizia la ricerca un'altra volta
// [ ] nell'elenco dei plugin presenti ella lista bisogna aggiugere il dato se sono o no già installati ed eventualmente disabilitare la checkbox
// [ ] Quando si crea una lista e si salva senza il nome viene comunque salvata la pagina con un messaggio di errore di WordPress controlla l'email ecc ecc
if ( function_exists( 'inslst_fs' ) ) {
    inslst_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'inslst_fs' ) ) {
        /**
         * Create a helper function for easy SDK access.
         */
        function inslst_fs() {
            global $inslst_fs;
            if ( !isset( $inslst_fs ) ) {
                // Include Freemius SDK.
                require_once __DIR__ . '/vendor/freemius/wordpress-sdk/start.php';
                $inslst_fs = fs_dynamic_init( array(
                    'id'             => '16025',
                    'slug'           => 'instalist',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_ecd52b261044c7793e4fa91a4dcb4',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'       => 'instalist',
                        'first-path' => 'edit.php?post_type=inslst_plugin_list',
                        'support'    => false,
                        'pricing'    => true,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $inslst_fs;
        }

        // Init Freemius.
        inslst_fs();
        // Signal that SDK was initiated.
        do_action( 'inslst_fs_loaded' );
    }
    // If this file is called directly, abort.
    if ( !defined( 'WPINC' ) ) {
        die;
    }
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'INSTALIST_VERSION', '1.0.4' );
    if ( inslst_fs()->is_premium() ) {
        define( 'INSTALIST_IS_PREMIUM', 1 );
    } else {
        define( 'INSTALIST_IS_PREMIUM', 0 );
    }
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-instalist-activator.php
     */
    function inslst_activate_instalist() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-instalist-activator.php';
        Instalist_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-instalist-deactivator.php
     */
    function inslst_deactivate_instalist() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-instalist-deactivator.php';
        Instalist_Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'inslst_activate_instalist' );
    register_deactivation_hook( __FILE__, 'inslst_deactivate_instalist' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-instalist.php';
    /**
     * Perform cleanup after uninstallation.
     *
     * @since    1.1.2
     */
    function inslst_fs_uninstall_cleanup() {
        delete_option( 'inslst_options' );
    }

    inslst_fs()->add_action( 'after_uninstall', 'inslst_fs_uninstall_cleanup' );
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function inslst_run_instalist() {
        $plugin = new Instalist();
        $plugin->run();
    }

    inslst_run_instalist();
}