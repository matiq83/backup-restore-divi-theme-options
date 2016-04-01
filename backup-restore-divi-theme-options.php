<?php
/**
 * Plugin Name: Backup/Restore Divi Theme Options
 * Description: Backup & Restore your Divi Theme Options.
 * Theme URI: https://github.com/SiteSpace/backup-restore-divi-theme-options
 * Author: Divi Space
 * Author URI: http://divispace.com
 * Version: 1.0.2
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tags: divi, theme options, theme settings, divi theme options, divi options, divi theme settings, divi settings
 * Text Domain: backup-restore-divi-theme-options
 */


class backup_restore_divi_theme_options {

	/**
	 * Constructor function for the class. 
	 * Very first function which will get called when plugin is active.
	 * Calling admin_menu hook to add a submenu page under Tools main menu. 
	 * /
	function backup_restore_divi_theme_options() {
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	
	/**
	 * Adding menu items on the admin side under Tools main menu.
	 * /
	function admin_menu() {

		$page = add_submenu_page('tools.php', 'Backup/Restore Theme Options', 'Backup/Restore Theme Options', 'manage_options', 'backup-restore-divi-theme-options', array(&$this, 'options_page'));

		add_action("load-{$page}", array(&$this, 'import_export'));

		add_submenu_page( 'et_divi_options',__( 'Backup/Restore Theme Options', 'Divi' ), __( 'Backup/Restore Theme Options', 'Divi' ), 'manage_options', 'tools.php?page=backup-restore-divi-theme-options', 'backup-restore-divi-theme-options' );

	}
	
	/**
	 * Processing the import or export requests.
	 * For the export request it is giving a ".dat" file to download which holds all data ready to import.
	 * For the import request it is reading a file and will add all Divi theme options values into the database
	 * /
	function import_export() {
		if (isset($_GET['action']) && ($_GET['action'] == 'download')) {
			header("Cache-Control: public, must-revalidate");
			header("Pragma: hack");
			header("Content-Type: text/plain");
			header('Content-Disposition: attachment; filename="divi-theme-options-'.date("dMy").'.dat"');
			echo serialize($this->_get_options());
			die();
		}
		if (isset($_POST['upload']) && check_admin_referer('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions')) {
			if ($_FILES["file"]["error"] > 0) {
				// error
			} else {
				$options = unserialize(file_get_contents($_FILES["file"]["tmp_name"]));
				if ($options) {
					foreach ($options as $option) {
						update_option($option->option_name, unserialize($option->option_value));
					}
				}
			}
			wp_redirect(admin_url('tools.php?page=backup-restore-divi-theme-options'));
			exit;
		}
	}
	
	/**
	 * Giving an interface on the admin side to download serialized ".dat" file
	 * Also giving an interface where admin can upload the file to import data for Divi theme
	 * /
	function options_page() { ?>

		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Backup/Restore Theme Options</h2>
			<form action="" method="POST" enctype="multipart/form-data">
				<style>#backup-restore-divi-theme-options td { display: block; margin-bottom: 20px; }</style>
				<table id="backup-restore-divi-theme-options">
					<tr>
						<td>
							<h3>Backup/Export</h3>
							<p>Here are the stored settings for the current theme:</p>
							<p><textarea disabled class="widefat code" rows="20" cols="100" onclick="this.select()"><?php echo serialize($this->_get_options()); ?></textarea></p>
							<p><a href="?page=backup-restore-divi-theme-options&action=download" class="button-secondary">Download as file</a></p>
						</td>
						<td>
							<h3>Restore/Import</h3>
							<p><label class="description" for="upload">Restore a previous backup</label></p>
							<p><input type="file" name="file" /> <input type="submit" name="upload" id="upload" class="button-primary" value="Upload file" /></p>
							<?php if (function_exists('wp_nonce_field')) wp_nonce_field('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions'); ?>
						</td>
					</tr>
				</table>
			</form>
		</div>

	<?php }
	
	/**
	 * Retrieving all saved options for the Divi theme and unserializing it.
	 * /
	function _display_options() {
		$options = unserialize($this->_get_options());
	}
	
	/**
	 * Retrieving all saved options for the Divi theme
	 * 
	 * @return WPDB results
	 * /
	function _get_options() {
		global $wpdb;
		return $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name = 'et_divi'"); // edit 'shapeSpace_options' to match theme options
	}
}
new backup_restore_divi_theme_options();
?>
