<?php
/**
 * This si the markup for the custom post type plugin-list
 *
 * @package Instalist
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<?php

$allowed_html = array(
	'input' => array(
		'id'       => array(),
		'name'     => array(),
		'value'    => array(),
		'class'    => array(),
		'type'     => array(),
		'disabled' => array(),
		'required' => array(),
	),
	'div'   => array(
		'id'    => array(),
		'class' => array(),
	),
	'label' => array(
		'for'   => array(),
		'class' => array(),
	),
);
$plugin_names = get_post_meta( $post->ID, '_inslst_plugin_names', true ) ? get_post_meta( $post->ID, '_inslst_plugin_names', true ) : array();
$plugin_slugs = get_post_meta( $post->ID, '_inslst_plugin_slugs', true ) ? get_post_meta( $post->ID, '_inslst_plugin_slugs', true ) : array();
$plugin_icons = get_post_meta( $post->ID, '_inslst_plugin_icons', true ) ? get_post_meta( $post->ID, '_inslst_plugin_icons', true ) : array();

?>


<?php
$disabled = '';
if ( ! inslst_fs()->is_premium() ) {
	$disabled = 'disabled';
}
?>
<div class="inline-buttons">
	<?php wp_nonce_field( 'inslstinstallselectedpluginfromlist', '_wpnonce_inst_sel' ); ?>
	<button class="button-primary <?php echo esc_html( $disabled ); ?>" id="install_selected_plugins_button">Install
		selected plugins</button>
	<?php wp_nonce_field( 'inslstinstallallpluginfromlist', '_wpnonce_inst_all' ); ?>
	<button class="button-primary" id="install_all_plugin_button">Install all</button>
	<?php wp_nonce_field( 'inslstexportpluginlist', '_wpnonce_export_list' ); ?>
	<button class="button-primary" data-post-id="<?php echo esc_html( $post->ID ); ?>"
		data-nonce="<?php echo esc_html( wp_create_nonce( 'inslstexportpluginlist' ) ); ?>" id="export_list_button">Export
		list</button>
</div>

<div id="plugins-list-table" class="inslst-list">
	<div class="table-head">
		<div class="table-row">
			<div class="table-column"></div>
			<div class="table-column">Plugin icon</div>
			<div class="table-column">Plugin name</div>
			<div class="table-column">Plugin slug</div>
			<div class="table-column">Action</div>
		</div>
	</div>
	<div class="table-body">
		<?php
		$count = count( $plugin_names );
		if ( 0 < $count ) {
			for ( $i = 0; $i <= $count; $i++ ) {
				if ( ! empty( $plugin_names[ $i ] ) ) {
					?>
		<div class="table-row" id="<?php echo esc_html( $plugin_slugs[ $i ] ); ?>">
			<div class="table-column">
					<?php
							$pd = $plugin_icons[ $i ] . ';' . $plugin_names[ $i ] . ';' . $plugin_slugs[ $i ];
					?>
				<input type="checkbox" name="plugin_to_install[]" class="plugin_to_install"
					value="<?php echo esc_html( $pd ); ?>" />
			</div>
			<div class="table-column">
				<img src="<?php echo esc_html( $plugin_icons[ $i ] ); ?>" />
			</div>
			<div class="table-column">
					<?php echo esc_html( $plugin_names[ $i ] ); ?>
			</div>
			<div class="table-column">
					<?php echo esc_html( $plugin_slugs[ $i ] ); ?>
			</div>
			<div class="table-column">
				<button data-slug="<?php echo esc_html( $plugin_slugs[ $i ] ); ?>"
					class="button remove_plugin_button">Remove</button>
			</div>
			<input type="hidden" name="plugin_name[]" class="plugin_name"
				value="<?php echo esc_html( $plugin_names[ $i ] ); ?>" />
			<input type="hidden" name="plugin_slug[]" class="plugin_slug"
				value="<?php echo esc_html( $plugin_slugs[ $i ] ); ?>" />
			<input type="hidden" name="plugin_icon[]" class="plugin_icon"
				value="<?php echo esc_html( $plugin_icons[ $i ] ); ?>" />
		</div>
					<?php
				}
			}
		}
		?>

	</div>
	<div class="table-footer">
		<div class="table-row">
			<div class="table-column"></div>
			<div class="table-column">Plugin icon</div>
			<div class="table-column">Plugin name</div>
			<div class="table-column">Plugin slug</div>
			<div class="table-column">Action</div>
		</div>
	</div>

</div>


<div id="form_wrapper" class="plugins-data-container">
	<?php $uploads = wp_upload_dir(); ?>
	<div id="main_form_wrapper">
		<div class="new_plugin_fields">
			<h3>Search for plugins...</h3>
			<p>Please, start by typing the plugin name.<br>InstaList will provide a list of plugins to make easier to
				find the plugin you are looking for. If you find it, just click on it and the form will be automatically
				filled out with the plugin's data.</p>
			<div class="field">
				<label>Plugin name*</label>
				<input type="text" name="plugin_name_input" id="plugin_name" />
				<input type="hidden" name="search_url" id="search_url"
					value="<?php echo esc_html( $uploads['baseurl'] ); ?>" />
				<input type="hidden" name="plugin_icon_input" id="plugin_icon" value="" />
				<input type="hidden" name="plugin_slug_input" id="plugin_slug" />
			</div>
			<!-- <div class="field">
				<label>Plugin slug*</label>
				<input type="text" name="plugin_slug_input" id="plugin_slug" />
			</div> -->
			<div class="inline-buttons">
				<input type="button" id="add_plugin_button" value="Add plugin" class="button-primary" />
				<input type="button" id="cancel_edit_list" value="Cancel" class="button" />
			</div>
			<h3>...or load installed plugins</h3>
			<?php
			$disabled = '';
			if ( ! inslst_fs()->is_premium() ) {
				$disabled = 'disabled';
			}
			?>
			<div class="inline-buttons">
				<input type="button" id="load_local_plugins" value="Load local plugins"
					class="button-primary <?php echo esc_html( $disabled ); ?>" />
			</div>
		</div>
		<div id="plugins_result_list">
			<div id="plugins-overlay" class="page-overlay">
				<div id="loader-container">
					<div class="plgsspinner"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="pagination_buttons">
		<div class="inline-buttons">
			<input type="hidden" name="current_page" id="current_page" vañue="" />
			<input type="button" id="load_prev_plugins" value="Prevous"
				class="button-primary paginating paginating-prev" />
			<input type="button" id="load_next_plugins" value="Next"
				class="button-primary paginating paginating-next" />
		</div>
	</div>
</div>