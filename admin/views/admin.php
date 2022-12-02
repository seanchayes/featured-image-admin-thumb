<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Featured_Image_Admin_Thumb
 * @author    Sean Hayes <sean@seanhayes.biz>
 * @license   GPL-2.0+
 * @link      http://www.seanhayes.biz
 * @copyright 2014 Sean Hayes
 */

	$fiat_allowed_sizes = wp_get_registered_image_subsizes() ;
	$fiat_allowed_actions = array( 'thumbnail', 'postedit' );

	// Save the options if the user click save.
	if( isset( $_POST['fiatupdateoptions'] ) ) {
		if( !isset( $_POST['fiat-thumb-size'] ) ) { $_POST['fiat-thumb-size'] = 'small'; }
		if( !isset( $_POST['fiat-link-action'] ) ) { $_POST['fiat-link-action'] = 'thumbnail'; }

		if( array_key_exists($_POST['fiat-thumb-size'], $fiat_allowed_sizes ) )	{
			update_option( 'fiat-thumb-size', sanitize_text_field( $_POST['fiat-thumb-size'] ) );
		}
		else {
			echo "<div id='setting-error-settings_updated' class='error settings-error'><p><strong>" . __( 'Invalid size!','featured-image-admin-thumb-fiat') . "</strong></p></div>\n";
		}

		if( in_array($_POST['fiat-link-action'], $fiat_allowed_actions ) )	{
			update_option( 'fiat-link-action', sanitize_text_field( $_POST['fiat-link-action'] ) );
		}
		else {
			echo "<div id='setting-error-settings_updated' class='error settings-error'><p><strong>" . __( 'Invalid action!','featured-image-admin-thumb-fiat') . "</strong></p></div>\n";
		}

		echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>" . __( 'Settings saved.', 'featured-image-admin-thumb-fiat') . "</strong></p></div>\n";
	}

	$current_thumb_size = get_option( 'fiat-thumb-size', 'thumbnail' );
	$current_link_action = get_option( 'fiat-link-action', 'thumbnail' );
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<br>

	<form method="post" action="options-general.php?page=fiat">
		<div>
		<?php _e( 'Thumbnail display size', 'featured-image-admin-thumb-fiat' ); ?>:
			<select name="fiat-thumb-size" id="fiat-thumb-size">
<?php
				foreach( $fiat_allowed_sizes as $name => $size ) {
					if( $size['crop'] ) { $crop = "cropped "; } else { $crop = ""; }
					if( $current_thumb_size == $name ) { $selected = ' selected'; } else { $selected = ''; }

					echo "\t\t\t\t<option value=\"$name\"$selected>$name ($crop$size[width]x$size[height])</option>" . PHP_EOL;
				}
?>
			</select>
		</div>

		<p></p>

		<div>
		<?php _e( 'Thumbnail link action', 'featured-image-admin-thumb-fiat' ); ?>:
			<select name="fiat-link-action" id="fiat-link-action">
				<option value="thumbnail"<?php if( $current_link_action == 'thumbnail' ) { echo ' selected'; } ?>><?php _e( 'Set Thumbnail', 'featured-image-admin-thumb-fiat'); ?></option>
				<option value="postedit"<?php if( $current_link_action == 'postedit' ) { echo ' selected'; } ?>><?php _e( 'Edit Post', 'featured-image-admin-thumb-fiat'); ?></option>
			</select>
		</div>

		<div class="submit">
			<input type="submit" class="button-primary" name="fiatupdateoptions" value="<?php _e('Update Options', 'featured-image-admin-thumb-fiat' ); ?>" />
		</div>
	</form>
</div>
