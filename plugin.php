<?php
/*
Plugin Name: Virtual Ads.txt
Version: 1.0
Description: Edit, maintain and serve the content for your ads.txt in a WordPress content container.
Author: Mikel King
Text Domain: virtual-ads-txt
License: BSD(3 Clause)
License URI: http://opensource.org/licenses/BSD-3-Clause

	Copyright (C) 2014, Mikel King, olivent.com, (mikel.king AT olivent DOT com)
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

		* Redistributions of source code must retain the above copyright notice, this
		list of conditions and the following disclaimer.

		* Redistributions in binary form must reproduce the above copyright notice,
		this list of conditions and the following disclaimer in the documentation
		and/or other materials provided with the distribution.

		* Neither the name of the {organization} nor the names of its
		contributors may be used to endorse or promote products derived from
		this software without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * Helps inhibit cross site scripting attacks
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * I hate having to write code like this class_exists() should work
 *
 * @param $class_name
 * @return bool
 */
function reliable_class_exists( $class_name ) {
	foreach ( get_declared_classes() as $class ) {
		if ( strcasecmp( $class_name, $class ) == 0 ) {
			return( true );
		}
	}
	return( false );
}

/**
 * If your site does not have bacon installed as a central mu-plugin library then
 * this will load the version included in the lib tree.
 * However if your site does; then this will skip along.
 */
if ( ! reliable_class_exists( 'Base_Plugin' ) ) {
	require( 'inc/lib/bacon-loader.php' );
}

require( 'inc/admin.php' );
require( 'inc/role-manager.php' );

/**
 * Class Virtual_Ads_Txt_Controller
 *
 * Purpose to act as the central hub for all ads.txt operations
 *
 * @todo Implement a site 2 site ads.txt import
 */
class Virtual_Ads_Txt_Controller extends Base_Plugin {
	const VERSION      = '1.0';
	const FILE_SPEC    = __FILE__;
	const PRIORITY     = 0;
	const HEADER       = "###\n# Ads.txt - created by the Virtual Ads.txt WordPress plugin. \n";
	const HEADER2      = "# Source: https://www.wordpress.org/plugins/virtual-ads-txt/\n#\n";
	const SPEC_SITE    = "# IAW IAB specifications https://iabtechlab.com/ads-txt/\n\n";
	const VALIDATOR    = "# VALIDATOR: https://adstxt.adnxs.com/ \n\n";
	const FOOTER       = "\n\n# TBD \n";
	const HTTP_STATUS  = 'Status: 200 OK';
	const HTTP_CODE    = 200;
	const CONTENT_TYPE = 'text/plain; charset=utf-8';
	const CACHE_CNTRL  = 'Cache-Control: max-age=';
	const CACHE_AGE    = 300; // duration in seconds
	const OPT_NAME     = 'virtual-ads-txt';
	const URI_TARGET   = 'ads.txt';

	public $options;
	public $url;
	public $admin;
	public $debug_headers;

	protected function __construct() {
		$this->validate_standard_paths();

		// This is how to add an deactivation hook if needed
		register_deactivation_hook( static::FILE_SPEC, array( $this, 'deactivator' ) );

		// This is how to add an deactivation hook if needed
		register_activation_hook( static::FILE_SPEC, array( $this, 'activator' ) );

		if ( $this->is_ads_txt_request() ) {
			add_action( 'send_headers', array( $this, 'render_ads_txt' ), static::PRIORITY );
		}

		if ( is_admin() ) {
			$admin = new Virtual_Ads_Txt_Admin();
		}
	}

	public function render_ads_txt() {
		$hhc = new HTTP_Header_Controller;
		$hhc->ob_begin();
		$this->send_http_page_headers();
		$this->send_ads_txt();
		$hhc->ob_flush();
	}

	public function deactivator() {
		$options = $this->get_options();
		if ( $options['remove_settings'] ) {
			delete_option( self::OPT_NAME );
		}

		Role_Manager::remove_capabilities_and_roles();
	}

	public function activator() {
		$rm = new Role_Manager();
	}

	public function set_content_type( $headers ) {
		if ( ! isset( $headers ) ) {
			print( 'Danger, danger! Will Robinson!\n' );
		}
		$headers['Content-Type'] = self::CONTENT_TYPE;
		$this->debug_headers = $headers;
		return( $headers );
	}

	public function send_http_page_headers() {
			header( 'Cache-Control: max-age=' . static::CACHE_AGE, true );
			//header( 'Content-Disposition: attachment; filename="ads.txt"', true );
			header( 'Content-Type:' . self::CONTENT_TYPE, true );
			header( 'Source: Virtual Ads.txt by Mikel King', true );
			//header( self::HTTP_STATUS, true, self::HTTP_CODE );
	}

	public function is_ads_txt_request() {
		$url = new URL_Magick();
		if ( strcasecmp( trim( $url::$uri, '/' ), self::URI_TARGET ) == 0 ) {
			return( true );
		}
		return( false );
	}

	public function send_ads_txt() {
		$output  = self::HEADER;
		$output .= self::HEADER2;
		$output .= self::SPEC_SITE;
		$output .= self::VALIDATOR;
		$output .= $this->get_virtual_ads_txt();
		$output .= self::FOOTER;

		print( $output );
		print_r( $this->debug_headers );
		exit;
	}

	public function get_virtual_ads_txt () {
		$this->get_options();
		if ( $this->options[self::OPT_NAME] != '' ) {
			return( stripcslashes( $this->options[self::OPT_NAME] ) );
		}
	}

	/**
	 * @return array
	 */
	public function get_options() {
		$this->options = get_option( self::OPT_NAME );
		if ( ! is_array( $this->options ) ) {
			$this->set_default_options();
		}
		return( $this->options );
	}

	/**
	 * @return array
	 */
	public function set_default_options() {
		$this->options = array(
			self::OPT_NAME => "# sample source: https://support.google.com/dfp_premium/answer/7441288?hl=en\n"
				. "google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0\n"
				. "google.com, pub-0000000000000000, RESELLER, f08c47fec0942fa0\n"
				. "greenadexchange.com, 12345, DIRECT, AEC242\n"
				. "blueadexchange.com, 4536, DIRECT\n"
				. "silverssp.com, 9675, RESELLER\n",
			'remove_settings' => false,
		);
		update_option( self::OPT_NAME, $this->options );
		return( $this->options );
	}


	public function init() {
		$this->validate_standard_paths();
	}

	/**
	 * Validate may not exactly be the correct term here but in essence
	 * we want to ensure that WordPress is setup so that we can work.
	 */
	public function validate_standard_paths() {
		if ( ! defined( 'WP_PLUGIN_URL' ) ) {
			if ( ! defined( 'WP_CONTENT_DIR' ) ) {
				define( 'WP_CONTENT_DIR', ABSPATH.'wp-content' );
			}
			if ( ! defined( 'WP_CONTENT_URL' ) ) {
				define( 'WP_CONTENT_URL', get_option( 'siteurl' ).'/wp-content' );
			}
			if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
				define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins' );
			}
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins' );
		}
	}
}

$vatc = Virtual_Ads_Txt_Controller::get_instance();

