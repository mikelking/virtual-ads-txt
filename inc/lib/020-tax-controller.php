<?php

/*
Plugin Name: Custom Taxonomy Controller
Version: 1.0
Description: A abstracted framework for working with taxonomy.
Author: Mikel King
Text Domain: tax-controller
License: BSD(3 Clause)
License URI: http://opensource.org/licenses/BSD-3-Clause

Copyright (C) 2014, Mikel King, rd.com, (mikel.king AT rd DOT com)
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
 * Class Custom_Taxonomy_Controller
 * @see https://codex.wordpress.org/Function_Reference/register_taxonomy
 */
class Custom_Taxonomy_Controller {
	const NAME                = '';
	const SINGULAR_NAME       = '';
	const DESCRIPTION         = '';
	const HIERARCHICAL        = false;
	const TEXT_DOMAIN         = '';
	const PUBLICLY_ACCESSIBLE = true;  // label => public
	const PUBLICLY_QUERYABLE  = true;
	const CUSTOM_ADMIN_UI     = true;  // label => show_ui
	const ADD_TO_MENUS        = true;  // label => show_in_nav_menus
	const ADD_TO_ADMIN_MENU   = true;  // label => show_in_menu (bool or string)
	const ADD_TO_ADMIN_COLS   = false; // label => show_admin_column
	const ADD_TO_REST_API     = true;  // label => show_in_rest
	const REST_BASE           = null;
	const REST_CONTROLLER     = 'WP_REST_Terms_Controller';
	const SHOW_IN_TAG_CLOUD   = false; // label => show_tagcloud
	const ADD_TO_QUICK_EDIT   = false; // label => show_in_quick_edit
	const META_BOX_CALL_BACK  = null;  // label => meta_box_cb
	const UPDT_COUNT_CALLBACK = '';    // label => update_count_callback
	const ALTER_PERMALINK     = false; // Prepend the tax in the URL
	const QUERY_VAR           = null;
	const SORT                = null;
	const DEBUG               = false;
	const DEBUG_PAGE          = 'debug';

	public $object_types = array( 'post' );
	public $args;
	public $capabilities;
	public $rewrite_args;
	public $lc_singular_name;
	public $lc_name;
	public $labels;
	public $terms;

	public function __construct( $object_types = null ) {
		if ( isset( $object_types ) ) {
			$this->object_types = $object_types;
		}

		add_action( 'init', array( $this, 'init' ), 0 );
	}

	public function init() {
		$this->set_lc_name();
		$this->set_lc_singular_name();
		$this->set_labels();
		$this->set_capabilities();
		$this->set_rewrite_args();
		$this->set_args();
		$this->register_tax();
		$this->debug_tax();
	}

	public function register_tax() {
		$msg = static::SINGULAR_NAME . ' Tax: '; // most likely superfluous
		try {
			if ( ! taxonomy_exists( $this->lc_singular_name ) ) {
				register_taxonomy( $this->lc_singular_name, $this->object_types, $this->args );
				$this->setup_terms();
			}
		} catch ( WP_Exception $e ) {
			$this->debug_tax( $this, $msg );
			return( true );
		}
	}

	public function set_args() {
		$this->args = array(
			'hierarchical'          => static::HIERARCHICAL,
			'labels'                => $this->labels,
			'description'           => __( static::DESCRIPTION, static::TEXT_DOMAIN ),
			'public'                => static::PUBLICLY_ACCESSIBLE,
			'publicly_queryable'    => static::PUBLICLY_QUERYABLE,
			'show_ui'               => static::CUSTOM_ADMIN_UI,
			'show_in_nav_menus'     => static::ADD_TO_MENUS,
			'show_in_menu'          => static::ADD_TO_ADMIN_MENU,
			'show_in_rest'          => static::ADD_TO_REST_API,
			'query_var'             => static::QUERY_VAR,
			'rest_base'             => static::REST_BASE,
			'rest_controller_class' => static::REST_CONTROLLER,
			'show_tagcloud'         => static::SHOW_IN_TAG_CLOUD,
			'show_in_quick_edit'    => static::ADD_TO_QUICK_EDIT,
			'meta_box_cb'           => static::META_BOX_CALL_BACK,
			'show_admin_column'     => static::ADD_TO_ADMIN_COLS,
			'update_count_callback' => static::UPDT_COUNT_CALLBACK,
			'sort'                  => static::SORT,
		);

		if ( isset( $this->capabilities ) ) {
			$this->args['capabilities'] = $this->capabilities;
		}

		if ( isset( $this->support_args ) ) {
			$this->args['supports'] = $this->support_args;
		}

		if ( isset( $this->rewrite_args ) ) {
			$this->args['rewrite'] = $this->rewrite_args;
		}

	}

	public function setup_terms() {
		$this->debug_terms();

		if ( isset( $this->terms ) && is_array( $this->terms ) && count( $this->terms ) >= 3 ) {
			foreach ( $this->terms as $term ) {
				$this->debug_terms( $term );
				wp_insert_term( $term[0], $term[1], $term[2] );
			}
		}
	}

	public function set_labels() {
		$this->labels = array(
			'name'               => _x( static::NAME, 'taxonomy general name', static::TEXT_DOMAIN ),
			'singular_name'      => _x( static::SINGULAR_NAME, 'taxonomy singular name', static::TEXT_DOMAIN ),
			'all_items'          => __( 'All ' . static::NAME, static::TEXT_DOMAIN ),
			'edit_item'          => __( 'Edit ' . static::SINGULAR_NAME, static::TEXT_DOMAIN ),
			'view_item'          => __( 'View ' . static::SINGULAR_NAME, static::TEXT_DOMAIN ),
			'update_item'        => __( 'Update ' . static::SINGULAR_NAME, static::TEXT_DOMAIN ),
			'add_new_item'       => __( 'Add New ' . static::SINGULAR_NAME, static::TEXT_DOMAIN ),
			'new_item_name'      => __( 'New ' . static::SINGULAR_NAME . ' Name', static::TEXT_DOMAIN ),
			'search_items'       => __( 'Search ' . static::NAME, static::TEXT_DOMAIN ),
			'popular_items'      => __( 'Popular ' . static::NAME, static::TEXT_DOMAIN ),
			'not_found'          => __( 'No ' . $this->lc_name . ' found.', static::TEXT_DOMAIN ),
		);

		if ( static::HIERARCHICAL === false ) {
			$this->labels['parent_item'] = null;
			$this->labels['parent_item_colon'] = null;
			$this->labels['separate_items_with_commas'] = __( 'Separate ' . $this->lc_name . ' with  commas', static::TEXT_DOMAIN );
			$this->labels['add_or_remove_items'] = __( 'Add or remove ' . $this->lc_name, static::TEXT_DOMAIN );
			$this->labels['choose_from_most_used'] = __( 'Choose from the most used ' . $this->lc_name, static::TEXT_DOMAIN );
			$this->labels['not_found'] = __( 'No ' . $this->lc_name . ' found.', 'textdomain' );
			$this->labels['menu_name'] = _x( static::NAME, 'admin menu', static::TEXT_DOMAIN );

		} else {
			$this->labels['menu_name'] = _x( static::NAME, 'admin menu', static::TEXT_DOMAIN );
			$this->labels['parent_item'] = __( 'Parent ' . static::SINGULAR_NAME, static::TEXT_DOMAIN );
			$this->labels['parent_item_colon'] = __( 'Parent ' . static::NAME . ':', static::TEXT_DOMAIN );
		}
	}

	public function set_capabilities() {
		$this->capabilities = array(
			'edit_items'   => 'edit_' . $this->lc_singular_name,
			'manage_items' => 'manage_' . $this->lc_singular_name,
			'delete_items' => 'delete_' . $this->lc_singular_name,
			'assign_items' => 'assign_' . $this->lc_name,  // not sure about this one
		);
	}

	/**
	 * @see https://make.wordpress.org/plugins/2012/06/07/rewrite-endpoints-api/
	 */
	public function set_rewrite_args() {
		$this->rewrite_args = array(
			'slug'         => $this->lc_name,
			'with_front'   => static::ALTER_PERMALINK,
			'hierarchical' => static::HIERARCHICAL,
			'ep_mask'      => EP_NONE,
		);
	}


	public function set_lc_name() {
		return( $this->lc_name = strtolower( static::NAME ) );
	}

	public function set_lc_singular_name() {
		return( $this->lc_singular_name = strtolower( static::SINGULAR_NAME ) );
	}

	/**
	 * A basic debugging method
	 * @param null $tax
	 * @param null $msg
	 */
	public function debug_tax( $tax = null, $msg = null ) {
		if ( $tax ) {
			$object = $tax;
		} else {
			$object = $this;
		}

		// Displays the TAX setting in a standard WP admin message
		if ( static::DEBUG && is_admin() ) {
			$dump_msg = var_export( $object->args, true );
			$am = new Admin_Message( $msg . $dump_msg );
			$am->display_admin_normal_message();
		} elseif ( static::DEBUG && is_page( static::DEBUG_PAGE ) ) {
			/**
			 * Displays the TAX setting in a WP page named debug (default)
			 * @example http://MY-DOMAIN.com/debug/
			 */
			var_dump( $object->args );
		}
	}

	/**
	 * A basic debugging method
	 * @param null $tax
	 * @param null $msg
	 */
	public function debug_terms( $terms = null, $msg = null ) {
		if ( ! $terms ) {
			$terms = $this->terms;
		}

		// Displays the TAX setting in a standard WP admin message
		if ( static::DEBUG && is_admin() ) {
			$dump_msg = var_export( $terms, true );
			$am = new Admin_Message( $msg . $dump_msg );
			$am->display_admin_normal_message();
		} elseif ( static::DEBUG && is_page( static::DEBUG_PAGE ) ) {
			/**
			 * Displays the TAX setting in a WP page named debug (default)
			 * @example http://MY-DOMAIN.com/debug/
			 */
			var_dump( $terms );
		}
	}
}
