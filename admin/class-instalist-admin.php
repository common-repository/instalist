<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://codingfix.com
 * @since      1.0.0
 *
 * @package    Instalist
 * @subpackage Instalist/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Instalist
 * @subpackage Instalist/admin
 * @author     Marco Gasi <info@codingfix.com>
 */
class Instalist_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The plugins list.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins    The current version of this plugin.
	 */
	private $inslst_plugins;

	/**
	 * The global args.
	 *
	 * @since    1.0.0
	 * @var      array    $args    The argument for the plugins.
	 */
	private $wp_plugins_path;

	/**
	 * The current page number.
	 *
	 * @since    1.0.0
	 * @var      string    $page_number    The number of the page in the wp repo.
	 */
	private $page_number;

	/**
	 * The installer given by the concatenation of the plugin slug and the plugin main file.
	 *
	 * @since    1.0.0
	 * @var      string    $page_number    The number of the page in the wp repo.
	 */
	private $installer;

	/**
	 * The array of plugins in the current list.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins_in_list    The array of plugins in the current list.
	 */
	private $plugins_in_list;

	/**
	 * The array of plugins to install.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins_to_activate    The array of plugins to install.
	 */
	private $plugins_to_activate;

	/**
	 * The array of plugins whose activation failed.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins_not_activates   The array of plugins whose activation failed.
	 */
	private $plugins_not_activated;

	/**
	 * Download errors.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins_to_activate    Download errors.
	 */
	private $download_errors;

	/**
	 * The custom post registrar.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins_to_activate    The array of plugins to install.
	 */
	private $inslst_cpt;

	/**
	 * The custom post metabox.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins_to_activate    The array of plugins to install.
	 */
	private $inslst_metabox;

	/**
	 * The page number of the paginated list.
	 *
	 * @since    1.0.0
	 * @var      string    $plugins_page_number    The plugin page number in wp repo.
	 */
	private $plugins_page_number;

	/**
	 * The highest page number reached.
	 *
	 * @since    1.0.0
	 * @var      string    $highest_page_number    The highest page number reached.
	 */
	private $highest_page_number;

	/**
	 * The plugins options.
	 *
	 * @since    1.0.0
	 * @var      array    $options    The options of this plugin.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->options               = get_option( 'inslst_options' );
		$this->plugin_name           = $plugin_name;
		$this->version               = $version;
		$this->wp_plugins_path       = preg_replace( '#\\\\#', '/', ABSPATH ) . 'wp-content/plugins/';
		$this->download_errors       = array();
		$this->plugins_in_list       = array();
		$this->plugins_to_activate   = array();
		$this->plugins_not_activated = array();
		$this->inslst_cpt            = new Instalist_CPT();
		$this->inslst_metabox        = new Instalist_Metabox();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$current_screen = get_current_screen();
		if ( 'inslst_plugin_list' === $current_screen->post_type || 'instalist_page_inslst_settings' === $current_screen->id
		|| 'instalist_page_inslst_import' === $current_screen->id ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/instalist-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . 'sweeetalert-style', plugin_dir_url( __FILE__ ) . 'css/sweetalert2.min.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$current_screen = get_current_screen();
		if ( 'inslst_plugin_list' === $current_screen->post_type || 'instalist_page_inslst_settings' === $current_screen->id
		|| 'instalist_page_inslst_import' === $current_screen->id ) {
			wp_enqueue_script( $this->plugin_name . '_main_js', plugin_dir_url( __FILE__ ) . 'js/instalist-admin.js', array( 'jquery', 'jquery-form', $this->plugin_name . '_sweetalert-script' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name . '_sweetalert-script', plugin_dir_url( __FILE__ ) . 'js/sweetalert2.min.js', array(), $this->version, false );
			wp_localize_script( $this->plugin_name . '_main_js', 'instalistAdmin', array( 'nonce' => wp_create_nonce( $this->plugin_name . '_admin_nonce' ) ) );
		}
	}

	/**
	 * If list name is not set shows an error.
	 *
	 * @since    1.0.0
	 */
	public function inslst_show_title_error_notice() {
		if ( isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'nolistnameerror' ) ) {
				if ( isset( $_GET['title_error'] ) && '1' === $_GET['title_error'] ) {
					echo '<div class="error notice"><p>The list name cannot be empty.</p></div>';
				}
			}
		}
	}

	/**
	 * Adds settings page to menu.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_menu_page(
			'Instalist',
			'Instalist',
			apply_filters( 'inslst_admin_capability', 'edit_posts', 'plugin_list_list' ),
			'instalist',
			array( $this, 'plugin_list_page' ),
			'dashicons-list-view',
			6
		);

		add_submenu_page(
			'instalist',
			__( 'Add New Plugin List', 'instalist' ),
			__( 'Add New Plugin List', 'instalist' ),
			apply_filters( 'inslst_admin_capability', 'edit_posts', 'plugin_list_list' ),
			'post-new.php?post_type=inslst_plugin_list'
		);

		add_submenu_page(
			'instalist',
			__( 'Import', 'instalist' ),
			__( 'Import', 'instalist' ),
			apply_filters( 'inslst_import', 'manage_options', 'inslst_import' ),
			'inslst_import',
			array( $this, 'inslst_display_plugin_admin_page' )
		);
	}

	/**
	 * Add a links near Deactivate link in the plugin list
	 *
	 * @param string $links add link to Settings page in plugin page.
	 * @since    1.0.0
	 */
	public function inslst_add_action_links( $links ) {
		/*
		*  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		*/
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=instalist' ) . '">' . __( 'Settings', 'instalist' ) . '</a>',
		);
		return array_merge( $settings_link, $links );
	}

	/**
	 * Callback function for the admin settings page.
	 *
	 * @since    1.0.0
	 */
	public function inslst_display_plugin_admin_page() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/instalist-admin-display.php';
	}

	/**
	 * Get plugins
	 *
	 * @since    1.0.0
	 */
	public function inslst_install_and_activate_plugins() {
		$errors = array();
		foreach ( $this->inslst_plugins as $plugin ) {
			$slug = $plugin['slug'];

			$wprepopath = 'https://downloads.wordpress.org/plugin/' . $slug . '.zip';

			$response = wp_remote_get( $wprepopath );
			if ( is_wp_error( $response ) ) {
				$this->download_errors[] = $response->get_error_message();
			} else {
				$zip  = $response['body'];
				$data = explode( '=', $response['headers']['content-disposition'] );
				$file = end( $data );
				WP_Filesystem();
				global $wp_filesystem;
				$wp_filesystem->put_contents( $file, $zip );
				if ( unzip_file( $file, $this->wp_plugins_path ) ) {
					wp_delete_file( $file );
				}
				$filelist = $wp_filesystem->dirlist( $this->wp_plugins_path . '/' . $slug . '/' );
				foreach ( $filelist as $data ) {
					if ( is_array( $data ) ) {
						if ( 'f' === $data['type'] ) {
							$ext = strtolower( pathinfo( $data['name'], PATHINFO_EXTENSION ) );
							if ( 'php' === $ext ) {
								$plg_data = get_plugin_data( $this->wp_plugins_path . '/' . $slug . '/' . $data['name'] );
								if ( count( $plg_data ) > 0 ) {
									$plg_name = $plg_data['Name'];
									if ( ! empty( $plg_name ) ) {
										$this->installer         = $slug . '/' . $data['name'];
										$this->plugins_in_list[] = $this->installer;
									}
								}
							}
						}
					}
				}
			}
		}
		foreach ( $this->plugins_in_list as $plugin ) {
			$validated = validate_plugin_requirements( $plugin );
			if ( is_wp_error( $validated ) ) {
				$this->plugins_not_activated[] = $plugin;
			} else {
				$this->plugins_to_activate[] = $plugin;
			}
		}
		foreach ( $this->plugins_to_activate as $plugin ) {
			$activated = activate_plugin( $plugin, '', false, true );
			if ( is_wp_error( $activated ) ) {
				$this->plugins_not_activated[] = $plugin;
			}
		}
		$errors_number = count( $this->plugins_not_activated );
		if ( 0 < $errors_number ) {
			foreach ( $this->plugins_not_activated as $error ) {
				delete_transient( $error );
			}
			wp_send_json( array( 'result' => 'activation_error' ) );
			exit;
		} else {
			wp_send_json( array( 'result' => 'success' ) );
			exit;
		}
	}

	/**
	 * Installs selected plugins from a list
	 *
	 * @since    1.0.0
	 */
	public function inslst_install_selected_plugins() {
		if ( isset( $_POST['nonce'] ) ) {
			if ( false === check_ajax_referer( 'inslstinstallselectedpluginfromlist', 'nonce' ) ) {
				wp_send_json( array( 'result' => 'failure' ) );
				die;
			}
		}

		if ( isset( $_POST['plugin_data'] ) ) {
			$plugin_string = array_map( 'sanitize_text_field', wp_unslash( $_POST['plugin_data'] ) );
			foreach ( $plugin_string as $pd ) {
				$plugin_data            = explode( ';', $pd );
				$this->inslst_plugins[] = array(
					'slug' => end( $plugin_data ),
				);
			}
			$this->inslst_install_and_activate_plugins();
		} else {
			wp_send_json( array( 'result' => 'un casino' ) );
		}
		exit;
	}

	/**
	 * Installs all plugins from a list
	 *
	 * @since    1.0.0
	 */
	public function inslst_install_all_plugins() {
		if ( isset( $_POST['nonce'] ) ) {
			if ( false === check_ajax_referer( 'inslstinstallallpluginfromlist', 'nonce' ) ) {
				wp_send_json( array( 'result' => 'failure' ) );
				die;
			}
		}

		if ( isset( $_POST['plugin_data'] ) ) {
			$plugin_string = array_map( 'sanitize_text_field', wp_unslash( $_POST['plugin_data'] ) );
			foreach ( $plugin_string as $pd ) {
				$plugin_data            = explode( ';', $pd );
				$this->inslst_plugins[] = array(
					'slug' => end( $plugin_data ),
				);
			}
			$this->inslst_install_and_activate_plugins();
		} else {
			wp_send_json( array( 'result' => 'un casino' ) );
		}
		exit;
	}

	/**
	 * Installs all plugins from a list
	 *
	 * @since    1.0.0
	 */
	public function inslst_install_list() {
		if ( isset( $_POST['nonce'] ) ) {
			if ( false === check_ajax_referer( 'inslstinstallpluginlist', 'nonce' ) ) {
				wp_send_json( array( 'result' => 'failure' ) );
				die;
			}
		}

		if ( isset( $_POST['post_id'] ) ) {
			$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
		} else {
			wp_send_json( array( 'result' => 'failure' ) );
			die;
		}

		$plugin_slugs = get_post_meta( $post_id, '_plugin_slugs', true ) ? get_post_meta( $post_id, '_plugin_slugs', true ) : array();

		$plugins_number = count( $plugin_slugs );

		if ( 0 < $plugins_number ) {
			foreach ( $plugin_slugs as $slug ) {
				$this->inslst_plugins[] = array(
					'slug' => $slug,
				);
			}
			$this->inslst_install_and_activate_plugins();
		} else {
			wp_send_json( array( 'result' => 'This list is empty.' ) );
		}
		exit;
	}

	/**
	 * Export a plugin list
	 *
	 * @since    1.0.0
	 */
	public function inslst_export_plugin_list() {
		if ( isset( $_POST['nonce'] ) ) {
			if ( false === check_ajax_referer( 'inslstexportpluginlist', 'nonce' ) ) {
				wp_send_json(
					array(
						'result'  => 'failure',
						'message' => 'Invalid nonce',
					)
				);
				die;
			}
		}

		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json(
				array(
					'result'  => 'failure',
					'message' => 'No post id found',
				)
			);
			die;
		}
		$post_id = intval( $_POST['post_id'] );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( array( 'message' => 'Invalid post ID.' . $post_id ) );
		}
		if ( 'inslst_plugin_list' !== $post->post_type ) {
			wp_send_json_error( array( 'message' => 'Invalid post type.' ) );
		}

		$plugin_notes = get_post_meta( $post_id, '_inslst_plugin_list_notes', true ) . PHP_EOL;
		if ( empty( $plugin_notes ) ) {
			$plugin_notes = '' . PHP_EOL;
		}
		// Crea il contenuto del file CSV.
		$csv_content  = '';
		$csv_content  = "{$plugin_notes}";
		$plugin_icons = get_post_meta( $post_id, '_inslst_plugin_icons', true );
		$plugin_names = get_post_meta( $post_id, '_inslst_plugin_names', true );
		$plugin_slugs = get_post_meta( $post_id, '_inslst_plugin_slugs', true );

		if ( ! empty( $plugin_names ) && ! empty( $plugin_slugs ) ) {
			foreach ( $plugin_names as $index => $name ) {
				$csv_content .= "{$plugin_icons[$index]}|$name|{$plugin_slugs[$index]}" . PHP_EOL;
			}
		}

		$file_name = preg_replace( '/\s/', '_', get_the_title( $post ) );
		// Imposta gli header per il download del file CSV.
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Output del contenuto CSV.
		echo esc_html( $csv_content );

		exit;
	}

	/**
	 * Import a plugin list
	 *
	 * @since    1.0.0
	 */
	public function inslst_import_plugin_list() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed' );
		}
		$args  = array(
			'post_type'      => 'inslst_plugin_list',
			'posts_per_page' => -1,
		);
		$query = new WP_Query( $args );
		if ( $query->found_posts >= 1 && ! inslst_fs()->is_premium() ) {
			wp_send_json( array( 'result' => 'upgrade_needed' ) );
		}
		check_admin_referer( 'inslstimportpluginlist' );

		if ( isset( $_FILES['import_list']['name'] ) ) {
			$full_list_name = sanitize_text_field( wp_unslash( $_FILES['import_list']['name'] ) );
			$name_parts     = explode( '.', $full_list_name );
			$list_name      = preg_replace( '/_/', ' ', $name_parts[0] );
			// Verifica se esiste già un post con lo stesso nome.
			$original_name = $list_name;
			$i             = 1;
			while ( post_exists( $list_name, '', '', 'inslst_plugin_list' ) ) {
				$list_name = $original_name . ' ' . $i;
				++$i;
			}

			$attachment_id = media_handle_upload( 'import_list', 0 );
			$my_file       = get_attached_file( $attachment_id, true );
			$uploads       = wp_upload_dir();
			$response      = wp_remote_get(
				$uploads['url'] . '/' . $full_list_name,
				array(
					'sslverify' => false,
				)
			);
			if ( is_wp_error( $response ) ) {
				die( 'error 2' );
			}
			$plugin_icons = array();
			$plugin_names = array();
			$plugin_slugs = array();

			$file_contents = wp_remote_retrieve_body( $response );
			$rows          = explode( PHP_EOL, $file_contents );

			$i = 0;
			foreach ( $rows as $row ) {
				if ( ! empty( $row ) ) {
					if ( 0 === $i ) {
						$plugin_list_notes = $row;
					} else {
						$csv_data       = explode( '|', $row );
						$plugin_icons[] = $csv_data[0];
						$plugin_names[] = $csv_data[1];
						$plugin_slugs[] = $csv_data[2];
					}
				}
				++$i;
			}

			// Crea un nuovo post di tipo 'inslst_plugin_list'.
			$new_post = array(
				'post_title'  => $list_name,
				'post_status' => 'publish',
				'post_type'   => 'inslst_plugin_list',
			);
			$post_id  = wp_insert_post( $new_post );
			// Salva i dati nelle metabox.
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_inslst_plugin_icons', array_map( 'sanitize_text_field', wp_unslash( $plugin_icons ) ) );
				update_post_meta( $post_id, '_inslst_plugin_names', array_map( 'sanitize_text_field', wp_unslash( $plugin_names ) ) );
				update_post_meta( $post_id, '_inslst_plugin_slugs', array_map( 'sanitize_text_field', wp_unslash( $plugin_slugs ) ) );
				update_post_meta( $post_id, '_inslst_plugin_list_notes', sanitize_text_field( wp_unslash( $plugin_list_notes ) ) );
			}
			/**
			 * Delete post of type attachment that WP creates and shoes in Media library
			 */
			wp_delete_post( $attachment_id );
			/**
			 * Delete the uploaded file once data are stored in the DB
			 */
			wp_delete_file( $my_file );

			wp_send_json( array( 'result' => 'success' ) );

		}

		exit;
	}

	/**
	 * Send response back to javascript ajax caller.
	 *
	 * @param    array $response The result of the API query.
	 *
	 * @since    1.0.0
	 */
	public function inslst_return_response( $response ) {
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Class contructor.
	 *
	 * @since    1.0.0
	 */
	public function inslst_reset_page_number() {
		$this->options['plugins_page'] = 1;
		update_option( 'inslst_options', $this->options );
	}

	/**
	 * Get plugins from wp repo
	 *
	 * @param    string $plugin The plugin slug.
	 * @param    array  $installed_plugins The active plugins array.
	 *
	 * @since    1.0.0
	 */
	public function inslst_is_installed( $plugin, $installed_plugins ) {
		foreach ( $installed_plugins as $ip ) {
			if ( false !== stripos( $ip, $plugin ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get plugins from wp repo
	 *
	 * @since    1.0.0
	 */
	public function inslst_get_plugins_from_repo() {
		$needle = filter_input( INPUT_POST, 'needle', FILTER_SANITIZE_STRING );

		$active_plugins = get_option( 'active_plugins' );

		$this->plugins_page_number = filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING );
		if ( 1 === $this->plugins_page_number ) {
			$this->inslst_reset_page_number();
		}

		if ( $this->plugins_page_number > 1 ) {
			$this->highest_page_number = $this->plugins_page_number;
		}

		$plugins_collection = plugins_api(
			'query_plugins',
			array(
				'search'   => $needle,
				'per_page' => 100,
				'page'     => $this->plugins_page_number,
				'fields'   => array(
					'short_description' => false,
					'description'       => false,
					'sections'          => false,
					'tested'            => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'downloadlink'      => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'versions'          => false,
					'donate_link'       => false,
					'reviews'           => false,
					'banners'           => false,
					'icons'             => true,
					'active_installs'   => false,
					'group'             => false,
					'contributors'      => false,
				),
			)
		);

		if ( count( $plugins_collection->plugins ) > 1 ) {
			$this->options['plugins_page'] = ++$this->plugins_page_number;
			$this->highest_page_number     = $this->options['plugins_page'];
		} else {
			$this->options['plugins_page'] = 1;
		}

		// increment the page, or when no results, reset to 1.
		update_option( 'inslst_options', $this->options );

		$plugins_matches = array();
		foreach ( $plugins_collection->plugins as $single_plugin ) {

			if ( null !== $single_plugin['name'] ) {
				$installed = 0;
				if ( $this->inslst_is_installed( $single_plugin['name'], $active_plugins ) ) {
					$installed = 1;
				}
				$plugins_matches[] = array(
					'plugin_name' => $single_plugin['name'],
					'plugin_slug' => $single_plugin['slug'],
					'icons'       => $single_plugin['icons'],
					'installed'   => $installed,
					'imgurl'      => plugin_dir_path( __FILE__ ) . 'img/image-not-found.png',
				);
			}
		}

		$this->inslst_return_response( $plugins_matches );
		exit;
	}

	/**
	 * Get installed plugins
	 *
	 * @since    1.0.0
	 */
	public function inslst_get_installed_plugins() {
		$all_plugins = apply_filters( 'all_plugins', get_plugins() );
		foreach ( (array) $all_plugins as $plugin_file => $plugin_data ) {

			$plugins_collection = plugins_api(
				'query_plugins',
				array(
					'search' => $plugin_data['Name'],
					'fields' => array(
						'icons' => true,
					),
				)
			);

			$plugins_matches[] = array(
				'plugin_name' => $plugin_data['Name'],
				'plugin_slug' => $plugin_data['TextDomain'],
				'icons'       => $plugins_collection->plugins[0]['icons'],
				'imgurl'      => plugin_dir_url( __FILE__ ) . 'img/image-not-found.png',
			);

		}

		$this->inslst_return_response( $plugins_matches );
		exit;
	}

	/**
	 * Check plugin list count
	 *
	 * @since    1.0.0
	 */
	public function inslst_check_plugin_list_count() {
		check_ajax_referer( $this->plugin_name . '_admin_nonce', 'security' );

		// Verifica il numero di liste esistenti.
		$args  = array(
			'post_type'      => 'inslst_plugin_list',
			'posts_per_page' => -1,
		);
		$query = new WP_Query( $args );
		wp_send_json( array( 'found_posts' => $query->found_posts ) );
		exit;
	}
}
