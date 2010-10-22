<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Permission
 * @abstract
 */
class Permission_Controller extends Controller
{
	public
		$pageTitle    = 'Permissions',
		$dependencies = array('db', 'input', 'permission'),
		$inAdmin      = TRUE
		;

	function init()
	{
		$this->app->input->validate(array(
			'form-submit'   => 'bool',
			'name'          => 'string',
			'form-submit-2' => 'bool',
			'user'          => 'int',
			'form-submit-3' => 'bool',
			'value'         => 'int'
			));

		if ( !$this->app->permission->check('admin permission access') )
		{
			header('Location: ' . $this->view->route('login?ref=' . $this->request));

			$this->app->end();
		}

		/*
		 * Get users
		 */
		$this->app->db->sql('
			SELECT
				`id`,
				`username`
			FROM `' . $this->app->db->prefix . 'users`
			ORDER BY `username` ASC
			;');

		$users = $this->app->db->result;

		/*
		 * Get permissions
		 */
		$this->app->db->sql('
			SELECT
				*
			FROM `' . $this->app->db->prefix . 'perms`
			ORDER BY `name` ASC
			;');

		$permissions = $this->app->db->result;

		$permissionsGroups = array();

		if ( $permissions )
		{
			foreach ( $permissions as $permission )
			{
				if ( !isset($permissionsGroups[$permission['group']]) )
				{
					$permissionsGroups[$permission['group']] = array();
				}

				$permissionsGroups[$permission['group']][] = $permission;
			}
		}

		/*
		 * Get roles
		 */
		$roles = array();

		$this->app->db->sql('
			SELECT
				`id`,
				`name`
			FROM `' . $this->app->db->prefix . 'perms_roles`
			ORDER BY `name` ASC
			;');

		if ( $r = $this->app->db->result )
		{
			foreach ( $r as $d )
			{
				$roles[$d['id']] = array(
					'id'    => $d['id'],
					'name'  => $d['name'],
					'users' => array()
					);
			}
		}

		/*
		 * Get users
		 */
		$this->app->db->sql('
			SELECT
				prux.`role_id`,
				u.`id`,
				u.`username`
			FROM      `' . $this->app->db->prefix . 'perms_roles_users_xref` AS prux
			LEFT JOIN `' . $this->app->db->prefix . 'users`                  AS u    ON prux.`user_id` = u.`id`
			ORDER BY `username` ASC
			;');

		if ( $r = $this->app->db->result )
		{
			foreach ( $r as $d )
			{
				$roles[$d['role_id']]['users'][] = array(
					'id'       => $d['id'],
					'username' => $d['username'],
					);
			}
		}

		if ( !$this->app->permission->check('admin permission access') )
		{
			header('Location: ' . $this->view->route('login?ref=' . $this->request));

			$this->app->end();
		}

		if ( $this->app->input->POST_valid['form-submit'] )
		{
			if ( !$this->app->input->POST_valid['name'] )
			{
				$this->app->input->errors['name'] = $this->view->t('Please provide a name');
			}

			if ( $this->app->input->errors )
			{
				$this->view->error = $this->view->t('Please correct the errors below.');
			}
			else
			{
				if ( $this->method == 'create' && $this->app->permission->check('admin permission create') )
				{
					$this->app->db->sql('
						INSERT IGNORE INTO `' . $this->app->db->prefix . 'perms_roles` (
							`name`
						)
						VALUES (
							"' . $this->app->input->POST_db_safe['name'] . '"
						)
						;');

					if ( $this->app->db->result )
					{
						header('Location: ' . $this->view->route($this->path . '?notice=created'));

						$this->app->end();
					}
				}
				else if ( $this->method == 'edit' && $this->app->permission->check('admin permission edit') )
				{
					$this->app->db->sql('
						UPDATE `' . $this->app->db->prefix . 'perms_roles` SET
							`name` = "' . $this->app->input->POST_db_safe['name'] . '"
						WHERE
							`id` = ' . ( int ) $this->id . '
						LIMIT 1
						;');

					if ( $this->app->db->result )
					{
						header('Location: ' . $this->view->route($this->path . '?notice=updated'));

						$this->app->end();
					}
				}
			}
		}

		if ( $this->app->input->POST_valid['form-submit-2'] && $this->id && $this->app->permission->check('admin permission edit') )
		{
			if ( !$this->app->input->errors )
			{
				$this->app->db->sql('
					INSERT IGNORE INTO `' . $this->app->db->prefix . 'perms_roles_users_xref` (
						`role_id`,
						`user_id`
						)
					VALUES (
						' . ( int ) $this->id                               . ',
						' . ( int ) $this->app->input->POST_db_safe['user'] . '
						)
					;');

				header('Location: ' . $this->view->route($this->path . '?notice=added'));

				$this->app->end();
			}
		}

		if ( $this->app->input->POST_valid['form-submit-3'] )
		{
			if ( !$this->app->input->errors )
			{
				foreach ( $permissions as $permission )
				{
					foreach ( $roles as $role )
					{
						$this->app->db->sql('
							INSERT INTO `' . $this->app->db->prefix . 'perms_roles_xref` (
								`perm_id`,
								`role_id`,
								`value`
								)
							VALUES (
								' . ( int ) $permission['id'] . ',
								' . ( int ) $role['id']       . ',
								' . ( int ) $this->app->input->POST_db_safe['value'][$permission['id']][$role['id']] . '
								)
							ON DUPLICATE KEY UPDATE
								`value` = ' . ( int ) $this->app->input->POST_db_safe['value'][$permission['id']][$role['id']] . '
							;');
					}
				}

				header('Location: ' . $this->view->route($this->path . '?notice=permissions_updated'));

				$this->app->end();
			}
		}
		else
		{
			if ( isset($this->app->input->GET_raw['notice']) )
			{
				switch ( $this->app->input->GET_raw['notice'] )
				{
					case 'added':
						$this->view->notice = $this->view->t('The user has been added to the role.');

						break;
					case 'removed':
						$this->view->notice = $this->view->t('The user has been removed to the role.');

						break;
					case 'created':
						$this->view->notice = $this->view->t('The role has been created.');

						break;
					case 'updated':
						$this->view->notice = $this->view->t('The role has been updated.');

						break;
					case 'deleted':
						$this->view->notice = $this->view->t('The role has been deleted.');

						break;
					case 'perms_updated':
						$this->view->notice = $this->view->t('The permissions have been updated.');

						break;
				}
			}

			/*
			 * Get values
			 */
			$values = array();

			$this->app->db->sql('
				SELECT
					*
				FROM `' . $this->app->db->prefix . 'perms_roles_xref`
				;');

			if ( $r = $this->app->db->result )
			{
				foreach ( $r as $d )
				{
					if ( !isset($values[$d['perm_id']]) )
					{
						$values[$d['perm_id']] = array();
					}

					$values[$d['perm_id']][$d['role_id']] = $d['value'];
				}
			}

			if ( $permissions && $roles )
			{
				foreach ( $permissions as $permission )
				{
					foreach ( $roles as $role )
					{
						if ( !isset($values[$permission['id']][$role['id']]) )
						{
							$values[$permission['id']][$role['id']] = Permission_Plugin::NO;
						}
					}
				}
			}
		}

		if ( $this->method && $this->id )
		{
			switch ( $this->method )
			{
				case 'edit':
					$this->app->input->POST_html_safe['name'] = $roles[$this->id]['name'];

					break;
				case 'remove_user':
					if ( isset($this->app->input->args['2']) && $userId = ( int ) $this->app->input->args['2'] && $this->app->permission->check('admin permission edit') )
					{
						if ( !$this->app->input->POST_valid['confirm'] )
						{
							$this->app->input->confirm($this->view->t('Are you sure you wish to remove this user from this role?'));
						}
						else
						{
							$this->app->db->sql('
								DELETE
								FROM `' . $this->app->db->prefix . 'perms_roles_users_xref`
								WHERE
									`user_id` = ' . ( int ) $userId   . ' AND
									`role_id` = ' . ( int ) $this->id . '
								;');

							if ( $this->app->db->result )
							{
								header('Location: ' . $this->view->route($this->path . '?notice=removed'));

								$this->app->end();
							}
						}
					}

					break;
				case 'delete':
					if ( $this->app->permission->check('admin permission delete') )
					{
						if ( !$this->app->input->POST_valid['confirm'] )
						{
							$this->app->input->confirm($this->view->t('Are you sure you wish to delete this role?'));
						}
						else
						{
							$this->app->db->sql('
								DELETE
									pr, prx, prux
								FROM      `' . $this->app->db->prefix . 'perms_roles`            AS   pr
								LEFT JOIN `' . $this->app->db->prefix . 'perms_roles_xref`       AS  prx ON pr.`id` =  prx.`role_id`
								LEFT JOIN `' . $this->app->db->prefix . 'perms_roles_users_xref` AS prux ON pr.`id` = prux.`role_id`
								WHERE
									pr.`id` = ' . ( int ) $this->id . '
								;');

							if ( $this->app->db->result )
							{
								header('Location: ' . $this->view->route($this->path . '?notice=deleted'));

								$this->app->end();
							}
						}
					}

					break;
			}
		}

		$this->app->input->POST_html_safe['value'] = $values;

		$this->view->users             = $users;
		$this->view->permissionsGroups = $permissionsGroups;
		$this->view->roles             = $roles;

		$this->view->load('admin/permission.html.php');
	}
}
