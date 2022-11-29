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

	$fiat_allowed_sizes = array( 'small', 'medium', 'large' );

	// Save the options if the user click save.
	if( isset( $_POST['fiatupdateoptions'] ) ) {
		if( !isset( $_POST['fiat-thumb-size'] ) ) { $_POST['fiat-thumb-size'] = 'small'; }

		if( in_array($_POST['fiat-thumb-size'], $fiat_allowed_sizes ) )	{
			update_option( 'fiat-thumb-size', sanitize_text_field( $_POST['fiat-thumb-size'] ) );

			echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>" . __( 'Settings saved.', 'featured-image-admin-thumb-fiat') . "</strong></p></div>\n";
		}
		else {
			echo "<div id='setting-error-settings_updated' class='error settings-error'><p><strong>" . __( 'Invalid size!.','featured-image-admin-thumb-fiat') . "</strong></p></div>\n";
		}
	}

	$current_thumb_size = get_option( 'fiat-thumb-size', 'small' );
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<br>

	<form method="post" action="options-general.php?page=fiat">
		<div>
		Thumbnail display size:
			<select name="fiat-thumb-size" id="fiat-thumb-size">
				<option value="small"<?php if( $current_thumb_size == 'small' ) { echo ' selected'; } ?>><?php _e( 'Small', 'featured-image-admin-thumb-fiat'); ?></option>
				<option value="medium"<?php if( $current_thumb_size == 'medium' ) { echo ' selected'; } ?>><?php _e( 'Medium', 'featured-image-admin-thumb-fiat'); ?></option>
				<option value="large"<?php if( $current_thumb_size == 'large' ) { echo ' selected'; } ?>><?php _e( 'Large', 'featured-image-admin-thumb-fiat'); ?></option>
			</select>
		</div>

		<div class="submit">
			<input type="submit" class="button-primary" name="fiatupdateoptions" value="<?php _e('Update Options', 'featured-image-admin-thumb-fiat' ); ?>" />
		</div>
	</form>
</div>
