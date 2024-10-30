<?php
/**
 * Create metabox for custom post types.
 *
 * @link       https://codingfix.com
 * @since      1.0.0
 *
 * @package    Instalist
 * @subpackage Instalist/includes
 */

/**
 * Create metabox for custom post types.
 *
 * Add the metabox for custom post types
 *
 * @package    Instalist
 * @subpackage Instalist/includes
 * @author     Marco Gasi <info@codingfix.com>
 */
class Instalist_Metabox {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'inslst_add_plugin_list_metabox' ) );
		add_action( 'save_post', array( $this, 'inslst_save_plugin_list_metabox' ) );
	}

	/**
	 * Add metaboxes for Plugin List CPT.
	 *
	 * @since    1.0.0
	 */
	public function inslst_add_plugin_list_metabox() {
		add_meta_box(
			'instalist-plugin-list-metabox',
			__( 'Plugins', 'instalist' ),
			array( $this, 'inslst_render_plugin_list_metabox' ),
			'inslst_plugin_list',
			'normal',
			'high'
		);
		add_meta_box(
			'inslst_plugin_list_notes',
			__( 'Notes', 'instalist' ),
			array( $this, 'inslst_render_notes_meta_box' ),
			'inslst_plugin_list',
			'normal',
			'high'
		);
	}

	/**
	 * Renders the main metabox for Plugin List CPT.
	 *
	 * @param object $post The current post.
	 * @since    1.0.0
	 */
	public function inslst_render_plugin_list_metabox( $post ) {
		wp_nonce_field( 'inslst_plugin_list_metabox_nonce', 'inslst_plugin_list_metabox_nonce' );
		include_once plugin_dir_path( __DIR__ ) . 'admin/partials/create-plugin-list.php';
	}

	/**
	 * Renders the notes metabox for Plugin List CPT.
	 *
	 * @param object $post The current post.
	 * @since    1.0.0
	 */
	public function inslst_render_notes_meta_box( $post ) {
		wp_nonce_field( 'inslst_plugin_list_notes_nonce', 'inslst_plugin_list_notes_nonce' );
		include_once plugin_dir_path( __DIR__ ) . 'admin/partials/add-list-notes.php';
	}

	/**
	 * Save the metaboxes for Plugin List CPT.
	 *
	 * @param int $post_id The current post.
	 * @since    1.0.0
	 */
	public function inslst_save_plugin_list_metabox( $post_id ) {
		// Verifica nonce per il plugin list metabox.
		if ( ! isset( $_POST['inslst_plugin_list_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inslst_plugin_list_metabox_nonce'] ) ), 'inslst_plugin_list_metabox_nonce' ) ) {
			return;
		}

		// Verifica nonce per il notes metabox.
		if ( ! isset( $_POST['inslst_plugin_list_notes_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inslst_plugin_list_notes_nonce'] ) ), 'inslst_plugin_list_notes_nonce' ) ) {
			return;
		}

		// Controllo se è un salvataggio automatico.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verifica permessi.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Salva i plugin.
		if ( isset( $_POST['plugin_name'] ) && isset( $_POST['plugin_slug'] ) && isset( $_POST['plugin_icon'] ) ) {
			update_post_meta( $post_id, '_inslst_plugin_names', array_map( 'sanitize_text_field', wp_unslash( $_POST['plugin_name'] ) ) );
			update_post_meta( $post_id, '_inslst_plugin_slugs', array_map( 'sanitize_text_field', wp_unslash( $_POST['plugin_slug'] ) ) );
			update_post_meta( $post_id, '_inslst_plugin_icons', array_map( 'sanitize_text_field', wp_unslash( $_POST['plugin_icon'] ) ) );
		} else {
			delete_post_meta( $post_id, '_inslst_plugin_names' );
			delete_post_meta( $post_id, '_inslst_plugin_slugs' );
			delete_post_meta( $post_id, '_inslst_plugin_icons' );
		}

		// Salva i dati della metabox Notes.
		if ( isset( $_POST['plugin_list_notes'] ) ) {
			$notes = sanitize_text_field( wp_unslash( $_POST['plugin_list_notes'] ) );
			update_post_meta( $post_id, '_inslst_plugin_list_notes', $notes );
		} else {
			delete_post_meta( $post_id, '_inslst_plugin_list_notes' );
		}
	}
}
