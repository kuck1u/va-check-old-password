<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Plugin Name: VA Check Old Password
 * Plugin URI: http://visualive.jp/
 * Description: This is a WordPress plugin that confirm old password before changing to new password.
 * Author: KUCKLU
 * Version: 1.0.0
 * Author URI: http://visualive.jp/
 * Text Domain: va-check-old-password
 * Domain Path: /languages
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (C) 2015 KUCKLU & VisuAlive.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.txt
 */
/**
 * VA Check Old Password.
 *
 * @package    WordPress
 * @subpackage VA Check Old Password
 * @author     KUCKLU <kuck1u@visualive.jp>
 * @copyright  Copyright (c) 2015 KUCKLU, VisuAlive.
 * @license    GNU General Public License v2 or later
 * @link       http://visualive.jp/
 */
$va_check_old_password_plugin_data = get_file_data( __FILE__, array( 'ver' => 'Version', 'langs' => 'Domain Path', 'mo' => 'Text Domain' ) );
define( 'VA_CHECK_OLD_PASSWORD_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'VA_CHECK_OLD_PASSWORD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'VA_CHECK_OLD_PASSWORD_DOMAIN',      dirname( plugin_basename( __FILE__ ) ) );
define( 'VA_CHECK_OLD_PASSWORD_VERSION',     $va_check_old_password_plugin_data['ver'] );
define( 'VA_CHECK_OLD_PASSWORD_TEXTDOMAIN',  $va_check_old_password_plugin_data['mo'] );
define( 'VA_CHECK_OLD_PASSWORD_LANGS',       $va_check_old_password_plugin_data['langs'] );

/**
 * Ran plugin.
 */
if ( !function_exists( 'va_check_old_password_setup' ) ) :
	function va_check_old_password_setup() {
		if ( !class_exists( 'VA_CHECK_OLD_PASSWORD_SETUP' ) ) {
			class VA_CHECK_OLD_PASSWORD_SETUP extends _VA_CHECK_OLD_PASSWORD {
				protected function __construct() {
					parent::__construct();
				}
			}
		}

		$va_check_old_password = VA_CHECK_OLD_PASSWORD_SETUP::instance();
	}
endif; // va_check_old_password
add_action( 'plugins_loaded', 'va_check_old_password_setup' );

/**
 * Class _VA_CHECK_OLD_PASSWORD
 */
class _VA_CHECK_OLD_PASSWORD {
	/**
	 * Holds the singleton instance of this class
	 *
	 * @var array
	 */
	private static $instances = false;

	/**
	 * Instance
	 *
	 * @return self
	 */
	public static function instance(){
		if( !self::$instances ) {
			self::$instances = new _VA_CHECK_OLD_PASSWORD;
		}

		return self::$instances;
	}

	/**
	 * This hook is called once any activated plugins have been loaded.
	 */
	protected function __construct() {
		load_plugin_textdomain( sprintf( '%s', VA_CHECK_OLD_PASSWORD_TEXTDOMAIN ), false, sprintf( '%s', VA_CHECK_OLD_PASSWORD_TEXTDOMAIN . VA_CHECK_OLD_PASSWORD_LANGS ) );

		add_action( 'show_user_profile',          array( &$this, 'profile_add_oldpass_field' ), -10    );
		add_action( 'user_profile_update_errors', array( &$this, 'profile_check_oldpass' ),      10, 3 );
	}

	/**
	 * Add the input form of the old password.
	 *
	 * @param object $profileuser The current WP_User object.
	 */
	public function profile_add_oldpass_field( $profileuser ) {
		?>
		<table class="form-table">
			<tr class="user-vacop-pass-old-wrap">
				<th><label for="vacop-pass-old"><?php _e( 'Old password', sprintf( '%s', VA_CHECK_OLD_PASSWORD_TEXTDOMAIN ) ); ?></label></th>
				<td>
					<input type="password" name="vacop_pass_old" id="vacop-pass-old" value="" class="regular-text" />
					<p class="description"><?php _e( 'Please old password an input when you change the password.', sprintf( '%s', VA_CHECK_OLD_PASSWORD_TEXTDOMAIN ) ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Check old password.
	 *
	 * @param array   $errors  An array of user profile update errors, passed by reference.
	 * @param bool    $update  Whether this is a user update.
	 * @param WP_User $user    WP_User object, passed by reference.
	 */
	public function profile_check_oldpass( $errors, $update, $user ) {
		if ( isset( $_POST['pass1'] ) && isset( $_POST['pass2'] ) && IS_PROFILE_PAGE ) {
			if ( !isset( $_POST['vacop_pass_old'] ) || empty( $_POST['vacop_pass_old'] ) ) {
				$errors->add( 'vacop_pass_old', __( '<strong>ERROR</strong>: Please enter your old password.', sprintf( '%s', VA_CHECK_OLD_PASSWORD_TEXTDOMAIN ) ) );
			} else {
				$user  = get_userdata( $user->ID );
				$check = wp_check_password( $_POST['vacop_pass_old'], $user->data->user_pass, $user->data->ID );

				if ( !$check ) {
					$errors->add( 'vacop_pass_old', __( '<strong>ERROR</strong>: An old password is wrong.', sprintf( '%s', VA_CHECK_OLD_PASSWORD_TEXTDOMAIN ) ) );
					return;
				}
			}
		}
	}
}
