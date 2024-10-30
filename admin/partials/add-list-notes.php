<?php
/**
 * This si the markup for the custom post type plugin-list
 *
 * @package Instalist
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$plugin_notes = get_post_meta( $post->ID, '_inslst_plugin_list_notes', true );
?>
<div id="notes_wrapper">
	<p>Type here a short description of this list and its goals.</p>
	<label for="inslst_plugin_list_notes">Notes:</label>
	<textarea id="inslst_plugin_list_notes_textarea" name="plugin_list_notes" rows="5"><?php echo esc_html( $plugin_notes ); ?></textarea>
</div>