<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->model) ) die('Direct access to this file is not allowed');

/**
 * Permissions
 * @abstract
 */
class perm
{
	public
		$ready
		;

	const
		roleOwnerId = 1,
		yes         = 1,
		no          = 0,
		never       = -1
		;

	private
		$model,
		$contr
		;

	/**
	 * Initialize
	 * @param object $this->model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->view  = $model->view;
		$this->contr = $model->contr;

		/**
		 * Check if the permissions table exists
		 */
		if ( in_array($model->db->prefix . 'perms', $model->db->tables) )
		{
			$this->model->db->sql('
				SELECT
					p.`name`  AS `permission`,
					pr.`name` AS `role`,
					prx.`value`
				FROM      `' . $model->db->prefix . 'perms_roles_users_xref` AS prux
				LEFT JOIN `' . $model->db->prefix . 'perms_roles`            AS pr   ON prux.`role_id` = pr.`id`
				LEFT JOIN `' . $model->db->prefix . 'perms_roles_xref`       AS prx  ON pr.`id`        = prx.`role_id` 
				LEFT JOIN `' . $model->db->prefix . 'perms`                  AS p    ON prx.`perm_id`  = p.`id`
				WHERE
					p.`name`  IS NOT NULL AND
					pr.`name` IS NOT NULL AND
					prux.`user_id` = ' . ( int ) $model->session->get('user id') . '
				', FALSE);

			if ( $r = $model->db->result )
			{
				$perms = array();

				foreach ( $r as $d )
				{
					if ( empty($perms[$d['permission']]) || $perms[$d['permission']] != -1 )
					{
						$perms[$d['permission']] = $d['value'];
					}
				}

				foreach ( $perms as $name => $value )
				{			
					$model->session->put('perm ' . $name, ( $model->session->get('user id owner') or $value == 1 ) ? 1 : 0);
				}			
			}

			$this->ready = TRUE;
		}
	}

	/**
	 * Check if the current user has permssion
	 * @param string $name
	 * @return bool
	 */
	function check($name)
	{
		return $this->model->session->get('user is owner') or $this->model->session->get('perm ' . $name);
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
		$this->model->db->sql('
			INSERT IGNORE INTO `' . $this->model->db->prefix . 'perms` (
				`name`,
				`desc`,
				`group`
				)
			VALUES (
				"' . $this->model->db->escape($name) . '",
				"' . $this->model->db->escape($description) . '",
				"' . $this->model->db->escape($group) . '"
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
		$this->model->db->sql('
			SELECT
				`id`
			FROM `' . $this->model->db->prefix . 'perms`
			WHERE
				`name` = "' . $this->model->db->escape($name) . '"
			LIMIT 1
			;');

		if ( $this->model->db->result && $id = $this->model->db->result[0]['id'] )
		{
			$this->model->db->sql('
				DELETE
					p, prx
				FROM      `' . $this->model->db->prefix . 'perms`            AS p
				LEFT JOIN `' . $this->model->db->prefix . 'perms_roles_xref` AS prx ON p.`id` = prx.`perm_id`
				WHERE
					p.`id` = ' . ( int ) $id . '
				;');

			return !empty($this->model->db->result);
		}
	}
}
