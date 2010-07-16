<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Permissions',
	'inAdmin'   => TRUE
	);

require($contrSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'input', 'permission'));

$app->input->validate(array(
	'form-submit'   => 'bool',
	'name'          => 'string',
	'form-submit-2' => 'bool',
	'user'          => 'int',
	'form-submit-3' => 'bool',
	'value'         => 'int'
	));

$id     = isset($app->input->GET_raw['id']) && ( int ) $app->input->GET_raw['id'] ? ( int ) $app->input->GET_raw['id'] : FALSE;
$action = isset($app->input->GET_raw['action']) ? $app->input->GET_raw['action'] : FALSE;

if ( !$app->permission->check('admin permission access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

/*
 * Get users
 */
$app->db->sql('
	SELECT
		`id`,
		`username`
	FROM `' . $app->db->prefix . 'users`
	ORDER BY `username` ASC
	;');

$users = $app->db->result;

/*
 * Get permissions
 */
$app->db->sql('
	SELECT
		*
	FROM `' . $app->db->prefix . 'perms`
	ORDER BY `name` ASC
	;');

$permissions = $app->db->result;

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

$app->db->sql('
	SELECT
		`id`,
		`name`
	FROM `' . $app->db->prefix . 'perms_roles`
	ORDER BY `name` ASC
	;');

if ( $r = $app->db->result )
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
$app->db->sql('
	SELECT
		prux.`role_id`,
		u.`id`,
		u.`username`
	FROM      `' . $app->db->prefix . 'perms_roles_users_xref` AS prux
	LEFT JOIN `' . $app->db->prefix . 'users`                  AS u    ON prux.`user_id` = u.`id`
	ORDER BY `username` ASC
	;');

if ( $r = $app->db->result )
{
	foreach ( $r as $d )
	{
		$roles[$d['role_id']]['users'][] = array(
			'id'       => $d['id'],
			'username' => $d['username'],
			);
	}
}

if ( !$app->permission->check('admin permission access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

if ( $app->input->POST_valid['form-submit'] )
{
	if ( !$app->input->POST_valid['name'] )
	{
		$app->input->errors['name'] = $view->t('Please provide a name');
	}

	if ( $app->input->errors )
	{
		$view->error = $view->t('Please correct the errors below.');
	}
	else
	{
		if ( $action == 'create' && $app->permission->check('admin permission create') )
		{
			$app->db->sql('
				INSERT IGNORE INTO `' . $app->db->prefix . 'perms_roles` (
					`name`
				)
				VALUES (
					"' . $app->input->POST_db_safe['name'] . '"
				)
				;');

			if ( $app->db->result )
			{
				header('Location: ?notice=created');

				$app->end();
			}
		}
		else if ( $action == 'edit' && $app->permission->check('admin permission edit') )
		{
			$app->db->sql('
				UPDATE `' . $app->db->prefix . 'perms_roles` SET
					`name` = "' . $app->input->POST_db_safe['name'] . '"
				WHERE
					`id` = ' . ( int ) $id . '
				LIMIT 1
				;');

			if ( $app->db->result )
			{
				header('Location: ?notice=updated');

				$app->end();
			}
		}
	}
}

if ( $app->input->POST_valid['form-submit-2'] && $id && $app->permission->check('admin permission edit') )
{
	if ( !$app->input->errors )
	{
		$app->db->sql('
			INSERT IGNORE INTO `' . $app->db->prefix . 'perms_roles_users_xref` (
				`role_id`,
				`user_id`
				)
			VALUES (
				' . ( int ) $id . ',
				' . ( int ) $app->input->POST_db_safe['user'] . '
				)
			;');

		header('Location: ?notice=added');

		$app->end();
	}
}

if ( $app->input->POST_valid['form-submit-3'] )
{
	if ( !$app->input->errors )
	{
		foreach ( $permissions as $permission )
		{
			foreach ( $roles as $role )
			{
				$app->db->sql('
					INSERT INTO `' . $app->db->prefix . 'perms_roles_xref` (
						`perm_id`,
						`role_id`,
						`value`
						)
					VALUES (
						' . ( int ) $permission['id'] . ',
						' . ( int ) $role['id'] . ',
						' . ( int ) $app->input->POST_db_safe['value'][$permission['id']][$role['id']] . '
						)
					ON DUPLICATE KEY UPDATE
						`value` = ' . ( int ) $app->input->POST_db_safe['value'][$permission['id']][$role['id']] . '
					;');
			}
		}

		header('Location: ?notice=permissions_updated');

		$app->end();
	}
}
else
{
	if ( isset($app->input->GET_raw['notice']) )
	{
		switch ( $app->input->GET_raw['notice'] )
		{
			case 'added':
				$view->notice = $view->t('The user has been added to the role.');

				break;
			case 'removed':
				$view->notice = $view->t('The user has been removed to the role.');

				break;
			case 'created':
				$view->notice = $view->t('The role has been created.');

				break;
			case 'updated':
				$view->notice = $view->t('The role has been updated.');

				break;
			case 'deleted':
				$view->notice = $view->t('The role has been deleted.');

				break;
			case 'perms_updated':
				$view->notice = $view->t('The permissions have been updated.');

				break;
		}
	}

	/*
	 * Get values
	 */
	$values = array();

	$app->db->sql('
		SELECT
			*
		FROM `' . $app->db->prefix . 'perms_roles_xref`
		;');

	if ( $r = $app->db->result )
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
					$values[$permission['id']][$role['id']] = Permission::NO;
				}
			}
		}
	}
}

if ( $action && $id )
{
	switch ( $action )
	{
		case 'edit':
			$app->input->POST_html_safe['name'] = $roles[$id]['name'];

			break;
		case 'remove':
			if ( isset($app->input->GET_raw['user_id']) && $userId = ( int ) $app->input->GET_raw['user_id'] && $app->permission->check('admin permission edit') )
			{
				if ( !$app->input->POST_valid['confirm'] )
				{
					$app->input->confirm($view->t('Are you sure you wish to remove this user from this role?'));
				}
				else
				{
					$app->db->sql('
						DELETE
						FROM `' . $app->db->prefix . 'perms_roles_users_xref`
						WHERE
							`user_id` = ' . ( int ) $userId . ' AND
							`role_id` = ' . ( int ) $id . '
						;');

					if ( $app->db->result )
					{
						header('Location: ?notice=removed');

						$app->end();
					}
				}
			}

			break;
		case 'delete':
			if ( $app->permission->check('admin permission delete') )
			{
				if ( !$app->input->POST_valid['confirm'] )
				{
					$app->input->confirm($view->t('Are you sure you wish to delete this role?'));
				}
				else
				{
					$app->db->sql('
						DELETE
							pr, prx, prux
						FROM      `' . $app->db->prefix . 'perms_roles`            AS pr
						LEFT JOIN `' . $app->db->prefix . 'perms_roles_xref`       AS prx  ON pr.`id`       = prx.`role_id`
						LEFT JOIN `' . $app->db->prefix . 'perms_roles_users_xref` AS prux ON prx.`role_id` = prux.`role_id`
						WHERE
							pr.`id` = ' . ( int ) $id . '
						;');

					if ( $app->db->result )
					{
						header('Location: ?notice=deleted');

						$app->end();
					}
				}
			}

			break;
	}
}

$app->input->POST_html_safe['value'] = $values;

$view->id                = $id;
$view->action            = $action;
$view->users             = $users;
$view->permissionsGroups = $permissionsGroups;
$view->roles             = $roles;

$view->load('admin/permissions.html.php');

$app->end();
