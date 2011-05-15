<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Permission_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'session', 'user'),
		$hooks        = array('dashboard' => 5, 'init' => 4, 'install' => 1, 'remove' => 1)
		;

	const
		ROLE_OWNER_ID = 1,
		YES           = 1,
		NO            = 0,
		NEVER         = -1
		;

	/*
	 * Implement install hook
	 */
	function install()
	{
		if ( !in_array($this->app->db->prefix . 'perms', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE {perms} (
					`id`    INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					`name`  VARCHAR(255)     NOT NULL UNIQUE,
					`desc`  VARCHAR(255)     NOT NULL,
					`group` VARCHAR(255)     NOT NULL
					) ENGINE = INNODB
				');

			$this->app->db->sql('
				INSERT INTO {perms} (
					`name`,
					`desc`,
					`group`
					)
				VALUES (
					"admin permission access",
					"Manage roles",
					"Permissions"
				), (
					"admin permission create",
					"Create roles",
					"Permissions"
				), (
					"admin permission edit",
					"Edit roles",
					"Permissions"
				), (
					"admin permission delete",
					"Delete roles",
					"Permissions"
				)
				');
		}

		if ( !in_array($this->app->db->prefix . 'perms_roles', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE {perms_roles} (
					`id`   INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					`name` VARCHAR(255)     NOT NULL UNIQUE
					) ENGINE = INNODB
				');

			$this->app->db->sql('
				INSERT INTO {perms_roles} (
					`name`
					)
				VALUES (
					"Administrator"
					)
				');
		}

		if ( !in_array($this->app->db->prefix . 'perms_roles_xref', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE {perms_roles_xref} (
					`perm_id` INT(10) UNSIGNED NOT NULL,
					`role_id` INT(10) UNSIGNED NOT NULL,
					`value`   INT(1)               NULL,
					FOREIGN KEY (`perm_id`) REFERENCES {perms}       (`id`) ON DELETE CASCADE,
					FOREIGN KEY (`role_id`) REFERENCES {perms_roles} (`id`) ON DELETE CASCADE
					) ENGINE = INNODB
				');
		}

		if ( !in_array($this->app->db->prefix . 'perms_roles_users_xref', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE {perms_roles_users_xref} (
					`role_id` INT(10) UNSIGNED NOT NULL,
					`user_id` INT(10) UNSIGNED NOT NULL,
					FOREIGN KEY (`role_id`) REFERENCES {perms_roles} (`id`) ON DELETE CASCADE,
					FOREIGN KEY (`user_id`) REFERENCES {users}       (`id`) ON DELETE CASCADE
					) ENGINE = INNODB
				');
		}
	}

	/*
	 * Implement remove hook
	 */
	function remove()
	{
		if ( in_array($this->app->db->prefix . 'perms_roles_users_xref', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE {perms_roles_users_xref}');
		}

		if ( in_array($this->app->db->prefix . 'perms_roles_xref', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE {perms_roles_xref}');
		}

		if ( in_array($this->app->db->prefix . 'perms_roles', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE {perms_roles}');
		}

		if ( in_array($this->app->db->prefix . 'perms', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE {perms}');
		}
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		$this->app->db->sql('
			SELECT
				p.`name`  AS `permission`,
				pr.`name` AS `role`,
				prx.`value`
			FROM      {perms_roles_users_xref} AS prux
			LEFT JOIN {perms_roles}            AS pr   ON prux.`role_id` =  pr.`id`
			LEFT JOIN {perms_roles_xref}       AS prx  ON   pr.`id`      = prx.`role_id`
			LEFT JOIN {perms}                  AS p    ON  prx.`perm_id` =   p.`id`
			WHERE
				 p.`name` IS NOT NULL     AND
				pr.`name` IS NOT NULL     AND
				prux.`user_id` = :user_id
			', array(
				':user_id' => ( int ) $this->app->session->get('user id')
				), FALSE
			);

		if ( $r = $this->app->db->result )
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
				$this->app->session->put('permission ' . $name, ( $this->app->session->get('user id owner') or $value == 1 ) ? 1 : 0);
			}
		}
	}

	/*
	 * Implement dashboard hook
	 * @param array $params
	 */
	function dashboard(&$params)
	{
		$params[] = array(
			'name'        => 'Permsissions',
			'description' => 'Add and edit roles and permissions',
			'group'       => 'Users',
			'path'        => 'admin/permission',
			'permission'  => 'admin permission access'
			);
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
	 * @return bool
	 */
	function create($group, $name, $description)
	{
		$this->app->db->sql('
			INSERT IGNORE INTO {perms} (
				`name`,
				`desc`,
				`group`
				)
			VALUES (
				:name,
				:desc,
				:group
				)
			', array(
				':name'  => $name,
				':desc'  => $description,
				':group' => $group
				)
			);

		return ( bool ) $this->app->db->result;
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
			FROM {perms}
			WHERE
				`name` = :name
			LIMIT 1
			', array(
				':name' => $name
				)
			);

		if ( $this->app->db->result && $id = $this->app->db->result[0]['id'] )
		{
			$this->app->db->sql('
				DELETE
					p, prx
				FROM      {perms}            AS   p
				LEFT JOIN {perms_roles_xref} AS prx ON p.`id` = prx.`perm_id`
				WHERE
					p.`id` = :id
				', array(
					':id' => ( int ) $id
					)
				);

			return ( bool ) $this->app->db->result;
		}
	}
}
