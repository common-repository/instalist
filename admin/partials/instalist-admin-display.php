<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://codingfix.com
 * @since      1.0.0
 *
 * @package    instalist
 * @subpackage instalist/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<?php

WP_Filesystem();
global $wp_filesystem;

require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
?>
<section id="instalist-options">
	<div id="saving-overlay" class="page-overlay">
		<div id="loader-container">
			<div class="plgsspinner"></div>
		</div>
	</div>
	<div id="inslst-content-wrapper">
		<div class="inslstsection">
			<div id="working-overlay" class="page-overlay"></div>
				<div class="import-section">
					<h2>Import list</h2>
					<p>You can import a list from a file you have previously exported or even a file you have written down manually. The allowed file format is csv (each line corresponds to a plugin and the separator between plugin name and plugin slug must be a semicolon).<br />Just choose a file and click Import button.</p>
					<div class="import-form-wrapper">
						<form id="import_plugin_list_form" method="post" action="admin-post.php" enctype="multipart/form-data"  multiple="false">
							<input type="hidden" name="action" value="inslst_import_plugin_list" />
							<?php wp_nonce_field( 'inslstimportpluginlist' ); ?>
							<input type="file" name="import_list" id="import_list">
							<input type="submit" data-is-premium="<?php echo esc_html( INSTALIST_IS_PREMIUM ); ?>" id="import_plugin_list_button" value="Import list" class="button-primary" disabled />
						</form>
					</div>
				</div>
		</div>

		<div class="inslstsection">
		</div>

		<div class="inslstsection">
		</div>

		<div class="inslstsection">
			<!-- <input type="submit" value="Install and activate" class="button-primary save-changes" /> -->
		</div>
	</div>

</section>
