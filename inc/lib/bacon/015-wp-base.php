<?php
/*
Plugin Name: WordPress Base Class
Version: 1.4
Description: This is is the buffer between the base classes and your plugins. It is meant for adding code specific to your site. For example the is_slideshow() method. Adding code like this here allows you to have a standardized interface for dealing with custom post types within any descendant plugin.
Author: Mikel King
Text Domain: wp-base
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
class WP_Base extends Base_Plugin {

	/**
	 * Generic method handler
	 *
	 * Allows to call is_mycusomposttype() for any custom post type.
	 * Calling is_video() will check if the current post is of type 'video'.
	 *
	 * @param  string $method_name
	 * @param  array $arguments
	 * @return mixed
	 */
	public function __call( $method_name, $arguments ) {
		if ( substr( $name, 0, 3 ) === 'is_' ) {
			return self::_is_post_type( substr( $name, 3 ) );
		} else {
			return parent::__call( $name, $args );
		}
	}

	/**
	 * Generic post type check
	 * @param  string  $expected_post_type Post type to check for
	 * @return bool
	 */
	private static function _is_post_type( $expected_post_type ) {
		$post_type = get_post_type( get_the_ID() );

		return $post_type == $expected_post_type;
	}

	/**
	 * Example of WordPress specific custom post type check
	 * @return bool
	 */
	public static function is_slideshow() {
		if ( get_post_type( get_the_ID() ) === 'slideshows' ) {
			return( true );
		}
		return( false );
	}


	/**
	 * @todo recommended for deprecation
	 * @param $asset_path
	 * @return mixed
	 */
	public function get_url_to_dir( $asset_path ) {
		return( plugins_url( $asset_path, __DIR__ ));
	}

	/**
	 * @todo recommended for deprecation
	 * @param $asset_path
	 * @return mixed
	 */
	public function get_url_to_file( $asset_path ) {
		return( plugins_url( $asset_path, __FILE__ ));
	}
}

