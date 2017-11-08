<?php
class Virtual_Ads_Txt_Admin extends Base_Plugin {
	const FIlE_SPEC     = __FILE__;
	const ADMIN_TITLE   = 'Virtual Ads.txt';
	const OPT_NAME      = 'virtual-ads-txt';
	const PLUGIN_CREDIT = '<h1>multo serious</h1>';

	public $option_name;
	public $options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		add_options_page( self::ADMIN_TITLE . ' Settings', self::ADMIN_TITLE, Role_Manager::CAPE_NAME, self::FILE_SPEC, array( $this, 'settings_page' ) );
	}

	/**
	 * @return array
	 */
	public function get_options() {
		$this->options = get_option( self::OPT_NAME );
		if ( ! is_array( $this->options ) )
			$this->set_default_options();
		return( $this->options );
	}

	public function settings_page() {
			$settings_title = self::OPT_NAME . '_settings';

			$this->get_options();

			$url = new URL_Magick();
			$address = $url::$protocol . $url::PROTOCOL_DELIM . $url::$host;

			if ( isset( $_POST['update'] ) ) {

				// check user is authorised
				if ( ! current_user_can( Role_Manager::CAPE_NAME) ) {
					die( 'Sorry, not allowed...' );
				}
				check_admin_referer( $settings_title );

				$this->options[self::OPT_NAME] = trim( $_POST[self::OPT_NAME] );

				if ( isset( $_POST['remove_settings'] ) ) {
					$this->options['remove_settings'] = true;
				} else {
					$this->options['remove_settings'] = false;
				}

				update_option( self::OPT_NAME, $this->options );
				print ( '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>' );
			}

			$output = '<div class="wrap">';
			$output .= '<h2>' . self::ADMIN_TITLE . 'Settings</h2>';
			$output .= '<form method="post">';
			$output .= wp_nonce_field( $settings_title );
			$output .= '<h3>User Agents and Directives for this site</h3>';
			$output .= '<p>The default rules that are set when the plugin is first activated are appropriate for WordPress.</p>';
			$output .= '<p>You can <a href="' . $address . '/ads.txt" target="_blank" onclick="window.open(\'' . $address;
			$output .= '/ads.txt\', \'popupwindow\', \'resizable=1,scrollbars=1,width=760,height=500\');return false;">';
			$output .= 'preview your ads.txt file here</a> (opens a new window). If your ads.txt file doesn\'t match what';
			$output .= ' is shown below, you may have a physical file that is being displayed instead.</p>';
			$output .= '<table class="form-table">';
			$output .= '<tr>';
			$output .= '<td colspan="2"><textarea name="' . self::OPT_NAME;
			$output .= '" rows="6" id="' . self::OPT_NAME . '" style="width:99%; height:300px;">';
			$output .= stripslashes( $this->options[self::OPT_NAME] ) . '</textarea></td>';
			$output .= '</tr>';
			$output .= '<tr>';
			$output .= '<th scope="row">Delete settings when deactivating this plugin:</th>';
			$output .= '<td><input type="checkbox" id="remove_settings" name="remove_settings"';
			if ( $this->options['remove_settings'] ) {
				$output .= 'checked="checked"';
			}
			$output .= ' /> <span class="setting-description">';
			$output .= 'When you tick this box all saved settings will be deleted when you deactivate this plugin.</span></td>';
			$output .= '</tr>';
			$output .= '</table>';
			$output .= '<p class="submit"><input type="submit" name="update" class="button-primary" value="Save Changes" /></p>';
			$output .= '</form>';
			$output .= '</div>';
			print( $output );

			$this->print_credit();
	}

	public function print_credit() {
		print( PHP_EOL . PHP_EOL . self::PLUGIN_CREDIT . PHP_EOL );
	}
}

