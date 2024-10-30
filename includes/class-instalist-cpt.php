<?php
/**
 * Registrar of custom post type.
 *
 * @link       https://codingfix.com
 * @since      1.0.0
 *
 * @package    Instalist
 * @subpackage Instalist/includes
 */

/**
 * Registrar of custom post type.
 *
 * Register the new custom post type Plugin List
 *
 * @package    Instalist
 * @subpackage Instalist/includes
 * @author     Marco Gasi <info@codingfix.com>
 */
class Instalist_CPT {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_cpt_plugin_list' ) );
		add_filter( 'save_post', array( $this, 'validate_plugin_list_title' ), 10, 2 );
		add_filter( 'gettext', array( $this, 'change_publish_button_text' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_custom_row_actions' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'show_upgrade_notice' ) );
		add_action( 'load-post-new.php', array( $this, 'restrict_plugin_list_creation' ) );
		add_filter( 'manage_inslst_plugin_list_posts_columns', array( $this, 'set_custom_edit_columns' ) );
		add_action( 'manage_inslst_plugin_list_posts_custom_column', array( $this, 'custom_column' ), 10, 2 );
	}

	/**
	 * Register Plugin List post type.
	 *
	 * @since    1.0.0
	 */
	public function register_cpt_plugin_list() {
		$args = array(
			'label'               => __( 'All', 'instalist' ),
			'labels'              => array(
				'name'               => __( 'Plugin Lists', 'instalist' ),
				'singular_name'      => __( 'Plugin List', 'instalist' ),
				'add_new'            => __( 'Add Plugin List', 'instalist' ),
				'add_new_item'       => __( 'Add Plugin List', 'instalist' ),
				'edit'               => __( 'Edit', 'instalist' ),
				'edit_item'          => __( 'Edit Plugin List', 'instalist' ),
				'new_item'           => __( 'New Plugin List', 'instalist' ),
				'view'               => __( 'View Plugin List', 'instalist' ),
				'view_item'          => __( 'View Plugin List', 'instalist' ),
				'search_items'       => __( 'Search Plugin List', 'instalist' ),
				'not_found'          => __( 'No Plugin List', 'instalist' ),
				'not_found_in_trash' => __( 'No plugin list found in trash', 'instalist' ),
				'menu_name'          => __( 'All Plugin Lists', 'instalist' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => 'instalist',
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'menu_positon'        => 65,
		);

		register_post_type( 'inslst_plugin_list', $args );
	}

	/**
	 * Validate list data
	 *
	 * @param array $data The data of the plugin list.
	 * @param array $postarr Non ne ho idea.
	 * @since 1.0.0
	 */
	public function validate_plugin_list_title( $data, $postarr ) {
		global $post;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['plugin_list_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['plugin_list_metabox_nonce'] ) ), 'plugin_list_metabox_nonce' ) ) {
			return;
		}

		if ( 'inslst_plugin_list' !== $post->post_type ) {
			return;
		}

		if ( empty( $post->post_title ) ) {
			$nonce = wp_create_nonce( 'nolistnameerror' );
			wp_safe_redirect(
				add_query_arg(
					array(
						'_wpnonce'    => $nonce,
						'title_error' => '1',
					),
					get_edit_post_link( $post->post_id, 'url' )
				)
			);
			exit;
		}
	}

	/**
	 * Change Publish button text
	 *
	 * @param string $translation The data of the plugin list.
	 * @param string $text Non ne ho idea.
	 * @since 1.0.0
	 */
	public function change_publish_button_text( $translation, $text ) {
		global $post;
		if ( isset( $post ) && 'inslst_plugin_list' === $post->post_type ) {
			if ( 'Publish' === $text ) {
				return 'Save';
			}
		}
		return $translation;
	}

	/**
	 * Change Publish button text
	 *
	 * @param string $actions The data of the plugin list.
	 * @param object $post Non ne ho idea.
	 * @since 1.0.0
	 */
	public function add_custom_row_actions( $actions, $post ) {
		if ( 'inslst_plugin_list' === $post->post_type ) {
			$plugin_slugs   = get_post_meta( $post->ID, '_inslst_plugin_slugs', true ) ? get_post_meta( $post->ID, '_inslst_plugin_slugs', true ) : array();
			$plugins_number = count( $plugin_slugs );
			if ( 0 < $plugins_number ) {
				$actions['export_list']  = '<a data-post-id="' . $post->ID . '" data-nonce="' . wp_create_nonce( 'inslst_exportpluginlist' ) . '" id="export_list_button" href="#">' . __( 'Export list', 'instalist' ) . '</a>';
				$actions['install_list'] = '<a data-post-id="' . $post->ID . '" data-nonce="' . wp_create_nonce( 'inslst_installpluginlist' ) . '" class="install_list_button" href="#">' . __( 'Install list', 'instalist' ) . '</a>';
			} else {
				$actions['export_list']  = '<a class="disabled" href="#">' . __( 'Export list', 'instalist' ) . '</a>';
				$actions['install_list'] = '<a class="disabled" href="#">' . __( 'Install list', 'instalist' ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * Restrict new list creation
	 *
	 * @since    1.0.0
	 */
	public function restrict_plugin_list_creation() {
		$args  = array(
			'post_type'      => 'inslst_plugin_list',
			'posts_per_page' => -1,
		);
		$query = new WP_Query( $args );
		if ( $query->found_posts >= 1 && ! inslst_fs()->is_premium() ) {
			$nonce = wp_create_nonce( 'upgradeneedederror' );
			wp_safe_redirect(
				add_query_arg(
					array(
						'result'    => 'error',
						'_wpnonce'  => $nonce,
						'post_type' => 'inslst_plugin_list',
					),
					admin_url( 'edit.php' )
				)
			);
		}
	}

	/**
	 * Show upgrade notice
	 *
	 * @since    1.0.0
	 */
	public function show_upgrade_notice() {
		if ( isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'upgradeneedederror' ) ) {
				if ( isset( $_GET['result'] ) && 'error' === $_GET['result'] ) {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'You cannot create more than one plugin list with the free version. Please upgrade to the premium version.', 'instalist' ) . '</p></div>';
				}
			}
		}
	}

	/**
	 * Set custom columns
	 *
	 * @param array $columns Custom columns.
	 * @since    1.0.0
	 */
	public function set_custom_edit_columns( $columns ) {
		$new_columns = array();
		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
			unset( $columns['cb'] );
		}
		if ( isset( $columns['title'] ) ) {
			$new_columns['title'] = $columns['title'];
			unset( $columns['title'] );
		}

		$new_columns['plugins_number'] = __( 'Plugins number', 'instalist' );
		$new_columns['plugins']        = __( 'Plugins', 'instalist' );
		$new_columns['notes']          = __( 'Notes', 'instalist' );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Set custom columns
	 *
	 * @param string $column Custom columns.
	 * @param int    $post_id The iD of the post.
	 * @since    1.0.0
	 */
	public function custom_column( $column, $post_id ) {
		$allowed_html = array( 'p' => array( 'title' => array() ) );
		switch ( $column ) {
			case 'notes':
				$notes  = get_post_meta( $post_id, '_inslst_plugin_list_notes', true );
				$output = "<p title='$notes'>" . wp_trim_words( $notes, 15 ) . '</p>';
				echo wp_kses( $output, $allowed_html );
				break;
			case 'plugins_number':
				$plugins_array = get_post_meta( $post_id, '_inslst_plugin_names', true );
				echo esc_html( count( $plugins_array ) );
				break;
			case 'plugins':
				$plugins_array = get_post_meta( $post_id, '_inslst_plugin_names', true );
				$plugins       = implode( ' | ', $plugins_array );
				echo wp_kses( "<p title='$plugins'>" . wp_trim_words( $plugins, 30 ) . '</p>', $allowed_html );
				break;
		}
	}
}
