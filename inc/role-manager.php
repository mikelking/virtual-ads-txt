<?php

/**
 * Class Role_Manager
 *
 * @see https://developer.wordpress.org/plugins/users/roles-and-capabilities/
 */
class Role_Manager {
	const ROLE_NAME = 'AdOps';
	const ROLE_SLUG = 'adops';
	const CAPE_NAME = 'edit_ads';
	const PRIORITY  = 11;

	public $capabilities;

	public function __construct() {
		$this->add_capability_to_admin();
		$this->add_custom_role();
	}

	public function add_capability_to_admin() {
		$role = get_role( 'administrator' );
		$role->add_cap( self::CAPE_NAME, true );
	}

	public static function remove_capabilities_and_roles() {
		$role = get_role( 'administrator' );
		$role->remove_cap( self::CAPE_NAME );
		remove_role( self::ROLE_SLUG );
	}

	/**
	 * The add custom capabilities sets up the capabilities and
	 * indirectly ensures that the new custom capability is
	 * added to admin.
	 *
	 * @return array
	 */
	public function add_custom_capabilities() {
		$this->capabilities = array(
			'read' => true,
			self::CAPE_NAME => true,
		);

		$this->add_capability_to_admin();
		return( $this->capabilities );
	}

	/**
	 * The add custom role method adds the new role with new
	 * capability.
	 */
	public function add_custom_role() {
		add_role( self::ROLE_SLUG, self::ROLE_NAME, $this->add_custom_capabilities() );
	}
}

