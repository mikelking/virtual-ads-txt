<?php

/**
 * Class HTTP_Header_Controller
 * @see https://developer.wordpress.org/Plugin_API/Action_Reference/send_headers
 * @see https://developer.wordpress.org/reference/functions/feed_content_type/
 * @see http://php.net/manual/en/function.header.php
 */
class HTTP_Header_Controller extends Singleton_Base {
	const VERSION              = '0.1';
	const OUTPUT_BUFFERING     = false;
	const FEED_SLUG            = '/feed/';
	const CACHE_MAX_AGE        = 3600; //seconds
	const DEFAULT_CACHE_AGE    = 14400; //seconds
	const HOME_CACHE_AGE       = 14400; //seconds
	const TAXONOMY_CACHE_AGE   = 21600; //seconds (6 hours)
	const ARCHIVE_CACHE_AGE    = 21600; //seconds (6 hours)
	const CONTENT_CACHE_AGE    = 86400; // seconds (1 day)
	const FEED_CACHE_AGE       = 3600; //seconds


	public function __construct() {
		add_action( 'send_headers', array( $this, 'send_http_page_headers' ) );
	}

	/**
	 * Check for standard WordPres content (i.e., posts, pages & attachments)
	 * @return bool
	 */
	public function is_content() {
		return(
			is_single() ||
			is_singular() ||
			is_attachment()
		);
	}

	/**
	 * Default for HTTP page headers
	 */
	public function send_http_page_headers() {
		$this->send_http_content_header();
		$this->send_http_home_header();
		$this->send_http_taxonomy_header();
		$this->send_http_feed_header();
		$this->send_http_archive_header();
		$this->send_http_default_header();
	}

	public function is_feed() {
		return(
		stripos(
			filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL ),
			static::FEED_SLUG )
		);
	}

	/**
	 * Default for Feeds
	 */
	public function send_http_feed_header() {
		if ( $this->is_feed() ) {
			$header  = $this->get_the_header_content_type();
			$header .= 'charset=' . get_option( 'blog_charset' );
			header( 'Cache-Control: max-age=' . static::FEED_CACHE_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}

	/**
	 * For Taxonomy listing pages
	 */
	public function send_http_taxonomy_header() {
		if ( is_tag() || is_category() ) {
			header( 'Cache-Control: max-age=' . static::TAXONOMY_CACHE_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}

	/**
	 * For Archive listing pages
	 */
	public function send_http_archive_header() {
		if ( is_archive() ) {
			header( 'Cache-Control: max-age=' . static::ARCHIVE_CACHE_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}

	/**
	 * For standard Content pages
	 */
	public function send_http_content_header() {
		if ( $this->is_content() ) {
			header( 'Cache-Control: max-age=' . static::ARCHIVE_CACHE_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}

	/**
	 *
	 */
	public function send_http_home_header() {
		if ( is_front_page() || is_home() ) {
			header( 'Cache-Control: max-age=' . static::HOME_CACHE_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}

	/**
	 * Default max cache age for ALL pages
	 */
	public function send_http_default_header() {
		if ( ! is_admin() && ! Base_Plugin::is_cms_user() ) {
			header( 'Cache-Control: max-age=' . static::DEFAULT_CACHE_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}

	/**
	 * This method may not be required for standard content types
	 * @return string
	 */
	public function get_the_header_content_type() {
		return( 'Content-Type: ' . feed_content_type( 'rss' ) ) . '; ';
	}

	/**
	 * This may not be necessary
	 */
	public function ob_begin() {
		if ( static::OUTPUT_BUFFERING === true ) {
			ob_start();
		}
	}

	/**
	 * This may not be necessary
	 */
	public function ob_flush() {
		if ( static::OUTPUT_BUFFERING === true ) {
			ob_end_flush();
		}
	}
}
