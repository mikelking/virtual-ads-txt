<?php

/*
Plugin Name: URL Magick
Version: 1.0.1
Description: A simple framework for consistently manipulating URLs part of the <a href='https://github.com/mikelking/bacon' target='_blank'>bacon project</a>.
Author: Mikel King
Text Domain: url-magick
License: BSD(3 Clause)
License URI: http://opensource.org/licenses/BSD-3-Clause

	Copyright (C) 2014, Mikel King, olivent.com, (mikel.king AT rd DOT com)
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
		This software without specific prior written permission.

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

class URL_Magick {
	const URL_DELIM         = '/';
	const PROTOCOL_DELIM    = '://';

	public static $protocol;
	public static $host;
	public static $user;
	public static $pass;
	public static $uri;
	public static $query;
	public static $fragment;
	public static $endpoint;
	public static $cleaned_url;

	public function __construct( $url = null ) {
		try {
			if ( ! $url ) {
				$url = self::get_current_page_url();
			}
			self::$cleaned_url = self::get_cleaned_url( $url );
			self::parse_url();
		} catch ( WP_Exception $e ) {
			var_dump( $this->cpt_args );
			return( true );
		}
	}

	public static function get_protocol() {
		if ( isset( $_SERVER ) && array_key_exists( 'HTTP_X_FORWARDED_PROTO', $_SERVER ) ) {
			return( filter_var( $_SERVER['HTTP_X_FORWARDED_PROTO'], FILTER_SANITIZE_URL ) );
		}
		return( 'http' );
	}

	public static function get_current_page_url() {
		//$_SERVER['REQUEST_SCHEME']
		//default to make wp-cli pass by
		if ( array_key_exists( 'REQUEST_SCHEME', $_SERVER ) ) {
			return( self::get_protocol() . self::PROTOCOL_DELIM . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		}
		return( 'http' . self::PROTOCOL_DELIM . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	}

	/**
	 * @return mixed
	 */
	public static function parse_url() {
		$url_parts = parse_url( self::$cleaned_url );
		self::set_endpoint();

		if ( isset( $url_parts['scheme'] ) ) {
			self::$protocol = $url_parts['scheme'];
		}

		if ( isset( $url_parts['host'] ) ) {
			self::$host = $url_parts['host'];
		}

		if ( isset( $url_parts['user'] ) ) {
			self::$user = $url_parts['user'];
		}

		if ( isset( $url_parts['pass'] ) ) {
			self::$pass = $url_parts['pass'];
		}

		if ( isset( $url_parts['path'] ) ) {
			self::$uri = $url_parts['path'];
			self::set_endpoint();
		}

		if ( isset( $url_parts['query'] ) ) {
			self::$query = $url_parts['query'];
		}

		if ( isset( $url_parts['fragment'] ) ) {
			self::$fragment = $url_parts['fragment'];
		}

		return( $url_parts );
	}

	public static function set_endpoint() {
		$uri_parts = explode( self::URL_DELIM, self::$uri );
		$part_count = count( $uri_parts );
		if ( $part_count && stripos( self::$uri, 'amp' ) ) {
			self::$endpoint = $uri_parts[$part_count - 2];
		}
	}

	public static function get_cleaned_url( $url ) {
		return( filter_var( $url, FILTER_SANITIZE_URL ) );
	}

	public static function print_url_parts() {
		print( 'Protocol: ' . self::$protocol . PHP_EOL );
		print( 'Host: ' . self::$host . PHP_EOL );
		print( 'User: ' . self::$user . PHP_EOL );
		print( 'Password: ' . self::$pass . PHP_EOL );
		print( 'URI: ' . self::$uri . PHP_EOL );
		print( 'Query string: ' . self::$query . PHP_EOL );
		print( 'URL fragment: ' . self::$fragment . PHP_EOL );
		print( 'Endpoint: ' . self::$endpoint . PHP_EOL );

		print( 'Cleaned URL: ' . self::$cleaned_url . PHP_EOL );
	}
}
