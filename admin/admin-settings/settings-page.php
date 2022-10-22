<?php // MyPlugin - Settings Page
namespace DPUK_Post_Sync\Admin;
use DPUK_Post_Sync\Admin as Admin;

// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	
	exit;
	
}



// display the plugin settings page
function dpukpostsync_display_settings_page() {
	
	// check if user is allowed access
	if ( ! current_user_can( 'manage_options' ) ) return;
	
	?>
	
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			
			<?php
			
			// output security fields
			settings_fields( 'dpukpostsync_options' );
			
			// output setting sections
			do_settings_sections( 'dpukpostsync' );
			
			// submit button
			submit_button();
			
			?>
			
		</form>
	</div>
	
	<?php
	
}

