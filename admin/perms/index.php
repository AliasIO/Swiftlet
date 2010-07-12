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

$app->check_dependencies(array('db', 'form', 'perm'));

$app->form->validate(array(
	'form-submit'   => 'bool',
	'name'          => 'string',
	'form-submit-2' => 'bool',
	'user'          => 'int',
	'form-submit-3' => 'bool',
	'value'         => 'int'
	));

$id     = isset($app->GET_raw['id']) && ( int ) $app->GET_raw['id'] ? ( int ) $app->GET_raw['id'] : FALSE;
$action = isset($app->GET_raw['action']) ? $app->GET_raw['action'] : FALSE;

if ( !$app->perm->check('admin perm access') )
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

$perms = $app->db->result;

$permsGroups = array();

if ( $perms )
{
	foreach ( $perms as $perm )
	{
		if ( !isset($permsGroups[$perm['group']]) )
		{
			$permsGroups[$perm['group']] = array();
		}

		$permsGroups[$perm['group']][] = $perm;
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

if ( !$app->perm->check('admin perm access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

if ( $app->POST_valid['form-submit'] )
{
	if ( !$app->POST_valid['name'] )
	{
		$app->form->errors['name'] = $app->t('Please provide a name');
	}

	if ( $app->form->errors )
	{
		$view->error = $app->t('Please correct the errors below.');
	}
	else
	{
		if ( $action == 'create' && $app->perm->check('admin perm create') )
		{
			$app->db->sql('
				INSERT IGNORE INTO `' . $app->db->prefix . 'perms_roles` (
					`name`
				)
				VALUES (
					"' . $app->POST_db_safe['name'] . '"
				)
				;');
			
			if ( $app->db->result )
			{
				header('Location: ?notice=created');

				$app->end();
			}
		}
		else if ( $action == 'edit' && $app->perm->check('admin perm edit') )
		{
			$app->db->sql('
				UPDATE `' . $app->db->prefix . 'perms_roles` SET
					`name` = "' . $app->POST_db_safe['name'] . '"
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

if ( $app->POST_valid['form-submit-2'] && $id && $app->perm->check('admin perm edit') )
{
	if ( !$app->form->errors )
	{
		$app->db->sql('
			INSERT IGNORE INTO `' . $app->db->prefix . 'perms_roles_users_xref` (
				`role_id`,
				`user_id`
				)
			VALUES (
				' . ( int ) $id . ',
				' . ( int ) $app->POST_db_safe['user'] . '
				)
			;');

		header('Location: ?notice=added');

		$app->end();
	}
}

if ( $app->POST_valid['form-submit-3'] )
{
	if ( !$app->form->errors )
	{
		foreach ( $perms as $perm )
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
						' . ( int ) $perm['id'] . ',
						' . ( int ) $role['id'] . ',
						' . ( int ) $app->POST_db_safe['value'][$perm['id']][$role['id']] . '
						)
					ON DUPLICATE KEY UPDATE
						`value` = ' . ( int ) $app->POST_db_safe['value'][$perm['id']][$role['id']] . '
					;');
			}
		}
		
		header('Location: ?notice=perms_updated');

		$app->end();
	}
}
else
{
	if ( isset($app->GET_raw['notice']) )
	{
		switch ( $app->GET_raw['notice'] )
		{
			case 'added':
				$view->notice = $app->t('The user has been added to the role.');

				break;
			case 'removed':
				$view->notice = $app->t('The user has been removed to the role.');

				break;
			case 'created':
				$view->notice = $app->t('The role has been created.');

				break;
			case 'updated':
				$view->notice = $app->t('The role has been updated.');

				break;
			case 'deleted':
				$view->notice = $app->t('The role has been deleted.');

				break;
			case 'perms_updated':
				$view->notice = $app->t('The permissions have been updated.');

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

	if ( $perms && $roles )
	{
		foreach ( $perms as $perm )
		{
			foreach ( $roles as $role )
			{
				if ( !isset($values[$perm['id']][$role['id']]) )
				{
					$values[$perm['id']][$role['id']] = perm::no;
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
			$app->POST_html_safe['name'] = $roles[$id]['name'];

			break;
		case 'remove':
			if ( isset($app->GET_raw['user_id']) && $userId = ( int ) $app->GET_raw['user_id'] && $app->perm->check('admin perm edit') )
			{
				if ( !$app->POST_valid['confirm'] )
				{
					$app->confirm($app->t('Are you sure you wish to remove this user from this role?'));
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
			if ( $app->perm->check('admin perm delete') )
			{
				if ( !$app->POST_valid['confirm'] )
				{
					$app->confirm($app->t('Are you sure you wish to delete this role?'));
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

$app->POST_html_safe['value'] = $values;

$view->id          = $id;
$view->action      = $action;
$view->users       = $users;
$view->permsGroups = $permsGroups;
$view->roles       = $roles;

$view->load('admin/perms.html.php');

$app->end();