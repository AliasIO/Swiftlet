<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->app) ) die('Direct access to this file is not allowed');

/**
 * Permissions
 * @abstract
 */
class Permission
{
	public
		$ready
		;

	const
		ROLE_OWNER_ID = 1,
		YES           = 1,
		NO            = 0,
		NEVER         = -1
		;

	private
		$app,
		$view,
		$controller
		;

	/**
	 * Initialize
	 * @param object $this->app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		/**
		 * Check if the permissions table exists
		 */
		if ( in_array($app->db->prefix . 'perms', $app->db->tables) )
		{
			$this->app->db->sql('
				SELECT
					p.`name`  AS `permission`,
					pr.`name` AS `role`,
					prx.`value`
				FROM      `' . $app->db->prefix . 'perms_roles_users_xref` AS prux
				LEFT JOIN `' . $app->db->prefix . 'perms_roles`            AS pr   ON prux.`role_id` = pr.`id`
				LEFT JOIN `' . $app->db->prefix . 'perms_roles_xref`       AS prx  ON pr.`id`        = prx.`role_id`
				LEFT JOIN `' . $app->db->prefix . 'perms`                  AS p    ON prx.`perm_id`  = p.`id`
				WHERE
					 p.`name` IS NOT NULL AND
					pr.`name` IS NOT NULL AND
					prux.`user_id` = ' . ( int ) $app->session->get('user id') . '
				', FALSE);

			if ( $r = $app->db->result )
			{
				$permissions = array();

				foreach ( $r as $d )
				{
					if ( empty($permissions[$d['permission']]) || $permissions[$d['permission']] != -1 )
					{
						$permissions[$d['permission']] = $d['value'];
					}
				}

				foreach ( $permissions as $name => $value )
				{
					$app->session->put('permission ' . $name, ( $app->session->get('user id owner') or $value == 1 ) ? 1 : 0);
				}
			}

			$this->ready = TRUE;
		}
	}

	/**
	 * Check if the current user has permissionssion
	 * @param string $name
	 * @return bool
	 */
	function check($name)
	{
		return $this->app->session->get('user is owner') or $this->app->session->get('permission ' . $name);
	}

	/**
	 * Create a new permission
	 * @param string $group
	 * @param string $name
	 * @param string $description
	 * @return integer
	 */
	function create($group, $name, $description)
	{
		$this->app->db->sql('
			INSERT IGNORE INTO `' . $this->app->db->prefix . 'perms` (
				`name`,
				`desc`,
				`group`
				)
			VALUES (
				"' . $this->app->db->escape($name)        . '",
				"' . $this->app->db->escape($description) . '",
				"' . $this->app->db->escape($group)       . '"
				)
			;');
	}

	/**
	 * Delete a permission
	 * @param string $name
	 * @return bool
	 */
	function delete($name)
	{
		$this->app->db->sql('
			SELECT
				`id`
			FROM `' . $this->app->db->prefix . 'perms`
			WHERE
				`name` = "' . $this->app->db->escape($name) . '"
			LIMIT 1
			;');

		if ( $this->app->db->result && $id = $this->app->db->result[0]['id'] )
		{
			$this->app->db->sql('
				DELETE
					p, prx
				FROM      `' . $this->app->db->prefix . 'perms`            AS   p
				LEFT JOIN `' . $this->app->db->prefix . 'perms_roles_xref` AS prx ON p.`id` = prx.`perm_id`
				WHERE
					p.`id` = ' . ( int ) $id . '
				;');

			return !empty($this->app->db->result);
		}
	}
}
