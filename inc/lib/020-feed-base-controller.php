<?php
/*
Plugin Name: Feed Base Controller
Version: 1.0
Description: A simple framework for working with feeds in PHP & JS.
Author: Mikel King
Text Domain: feed-controller
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

//Debug::enable_error_reporting();

//require_once( 'feed-options.php' );
//require_once( 'post-meta-controller.php' );

/**
 * Class Feed_Base_Controller
 */
class Feed_Base_Controller extends WP_Base {
	const OUTPUT_BUFFERING = false;
	const CACHE_MAX_AGE    = 3600; //seconds
	const ITEM_LIMIT       = 35;
	const DELIMINATOR      = '-';
	const MYSQL_DATE_FMT   = 'D, d M Y H:i:s';
	const POST_DATE_FMT    = 'Y-m-d H:i:s';
	const LEFT_DBL_QUOTE   = '\u201';
	const BACKSPACE        = '\b';
	const SERVICE_NAME     = 'Default';
	const MULTIPAGE_URLS   = false;
	const META_TAXONOMY   = true;
	const CONTENT_TYPE     = 'articles';

	public $frequency;
	public $duration;
	public $feed_item_limit;
	public $feed_prefix;
	public $feed_slug;
	public $feed_key;
	public $feed_options;
	public $pmc;

	public function __construct() {
		self::set_tz();
		$this->set_feed_properties();

		add_action( 'after_setup_theme', array( $this, 'add_feed' ) );
		add_action( 'send_headers', array( $this, 'send_http_page_header' ) );
	}


	public function set_feed_properties() {
		$this->feed_prefix = strtolower( static::SERVICE_NAME );
		$this->feed_slug = $this->feed_prefix . static::DELIMINATOR . static::CONTENT_TYPE;
		$this->feed_key = $this->feed_prefix . '_feed';
	}

	public function print_debug_info() {
		print('<!-- This is a test message. Had this been a real message there would be something useful to tell you. But this is only a test. -->' . PHP_EOL);
	}

	public function print_pmc_meta_data() {
		print('<!-- PMC meta data:' . PHP_EOL);
		print('Meta name: ' . $this->pmc->meta_name . PHP_EOL);
		print('Meta prefix: ' . $this->pmc->meta_prefix . PHP_EOL);
		print('Meta slug: ' . $this->pmc->meta_slug . PHP_EOL);
		print('Meta key: ' . $this->pmc->meta_key . PHP_EOL);
		print(' -->' . PHP_EOL);
	}

	/**
	 * This is a semi abstract method which should be overriden in the children
	 * Although it works, I am also slightly dubious about the inclusion
	 * of flush_rewrite_rules()
	 */
	public function add_feed() {
		//add_feed( $this->feed_slug, array( $this, 'render_feed' ) );
		flush_rewrite_rules();
	}

	public function ob_begin() {
		if ( static::OUTPUT_BUFFERING === true ) {
			ob_start();
		}
	}

	public function ob_flush() {
		if ( static::OUTPUT_BUFFERING === true ) {
			ob_end_flush();
		}
	}

	public function get_post_type() {
		$post_type = 'article';

		if ( $this->is_slideshow() ) {
			$post_type = 'slideshow';
		}

		return( $post_type );
	}

	public function get_the_url() {
		$url = get_the_permalink();

		if ( $this->is_slideshow() ) {
			$url = $url . '1/';
		}

		return( filter_var( $url, FILTER_SANITIZE_URL ) );
	}

	public function get_post_modified_date() {
		return(
		mysql2date(
			static::MYSQL_DATE_FMT,
			get_post_time( static::POST_DATE_FMT, true ),
			false
		)
		);
	}

	public function get_post_published_date() {
		return(
		mysql2date(
			static::MYSQL_DATE_FMT,
			get_post_modified_time( static::POST_DATE_FMT, true ),
			false
		)
		);
	}

	public function get_last_build_date() {
		return(
		mysql2date(
			static::MYSQL_DATE_FMT,
			get_lastpostmodified(),
			false
		)
		);
	}

	public function is_feed() {
		return(
			stripos(
				filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL ),
				$this->feed_slug )
		);
	}

	/**
	 * The purpose here is to create a standardized method for returning the title and yet make it overridable
	 * @return mixed
	 */
	public function get_the_post_title() {
		return( get_the_title_rss() );
	}

	/**
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/send_headers
	 * @see https://developer.wordpress.org/reference/functions/feed_content_type/
	 * @see http://php.net/manual/en/function.header.php
	 */
	public function send_http_page_header() {
		if ( $this->is_feed() ) {
			$header  = 'Content-Type: ' . feed_content_type( 'rss' );
			$header .= '; charset=' . get_option( 'blog_charset' );
			header( 'Cache-Control: max-age=' . static::CACHE_MAX_AGE . ', must-revalidate', true );
			header( $header, true );
		}
	}
}
