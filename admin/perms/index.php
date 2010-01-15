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

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'form', 'perm'));

$model->form->validate(array(
	'form-submit'   => 'bool',
	'name'          => 'string',
	'form-submit-2' => 'bool',
	'user'          => 'int',
	'form-submit-3' => 'bool',
	'value'         => 'int'
	));

$id     = isset($model->GET_raw['id']) && ( int ) $model->GET_raw['id'] ? ( int ) $model->GET_raw['id'] : FALSE;
$action = isset($model->GET_raw['action']) ? $model->GET_raw['action'] : FALSE;

/*
 * Get permissions
 */
$model->db->sql('
	SELECT
		*
	FROM `' . $model->db->prefix . 'perms`
	ORDER BY `name` ASC
	;');

$perms = $model->db->result;

/*
 * Get roles
 */
$model->db->sql('
	SELECT
		`id`,
		`name`
	FROM `' . $model->db->prefix . 'perms_roles`
	ORDER BY `name` ASC
	;');

if ( $r = $model->db->result )
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
$model->db->sql('
	SELECT
		prux.`role_id`,
		u.`id`,
		u.`username`
	FROM      `' . $model->db->prefix . 'perms_roles_users_xref` AS prux
	LEFT JOIN `' . $model->db->prefix . 'users`                  AS u    ON prux.`user_id` = u.`id`
	ORDER BY `username` ASC
	;');

if ( $r = $model->db->result )
{
	foreach ( $r as $d )
	{
		$roles[$d['role_id']]['users'][] = array(
			'id'       => $d['id'],
			'username' => $d['username'],
			);
	}
}

if ( !$model->perm->check('admin perm access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

if ( $model->POST_valid['form-submit'] )
{
	if ( !$model->POST_valid['name'] )
	{
		$model->form->errors['name'] = $model->t('Please provide a name');
	}

	if ( $model->form->errors )
	{
		$view->error = $model->t('Please correct the errors below.');
	}
	else
	{
		$model->db->sql('
			INSERT IGNORE INTO `' . $model->db->prefix . 'perms_roles` (
				`name`
			)
			VALUES (
				"' . $model->POST_db_safe['name'] . '"
			)
			;');
		
		if ( $model->db->result )
		{
			header('Location: ?notice=created');

			$model->end();
		}
	}
}

if ( $model->POST_valid['form-submit-2'] && $id )
{
	if ( !$model->form->errors )
	{
		$model->db->sql('
			INSERT IGNORE INTO `' . $model->db->prefix . 'perms_roles_users_xref` (
				`role_id`,
				`user_id`
				)
			VALUES (
				' . ( int ) $id . ',
				' . ( int ) $model->POST_db_safe['user'] . '
				)
			;');

		header('Location: ?notice=added');

		$model->end();
	}
}

if ( $model->POST_valid['form-submit-3'] )
{
	if ( !$model->form->errors )
	{
		foreach ( $perms as $perm )
		{
			foreach ( $roles as $role )
			{
				$model->db->sql('
					INSERT INTO `' . $model->db->prefix . 'perms_roles_xref` (
						`perm_id`,
						`role_id`,
						`value`
						)
					VALUES (
						' . ( int ) $perm['id'] . ',
						' . ( int ) $role['id'] . ',
						' . ( int ) $model->POST_db_safe['value'][$perm['id']][$role['id']] . '
						)
					ON DUPLICATE KEY UPDATE
						`value` = ' . ( int ) $model->POST_db_safe['value'][$perm['id']][$role['id']] . '
					;');
			}
		}
		
		header('Location: ?notice=perms_updated');

		$model->end();
	}
}
else
{
	if ( isset($model->GET_raw['notice']) )
	{
		switch ( $model->GET_raw['notice'] )
		{
			case 'added':
				$view->notice = $model->t('The user has been added to the role.');

				break;
			case 'removed':
				$view->notice = $model->t('The user has been removed to the role.');

				break;
			case 'created':
				$view->notice = $model->t('The role has been created.');

				break;
			case 'updated':
				$view->notice = $model->t('The role has been updated.');

				break;
			case 'deleted':
				$view->notice = $model->t('The role has been deleted.');

				break;
			case 'perms_updated':
				$view->notice = $model->t('The permissions have been updated.');

				break;
		}
	}

	/*
	 * Get values
	 */
	$values = array();

	$model->db->sql('
		SELECT
			*
		FROM `' . $model->db->prefix . 'perms_roles_xref`
		;');

	if ( $r = $model->db->result )
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
		case 'remove':
			if ( isset($model->GET_raw['user_id']) && $userId = ( int ) $model->GET_raw['user_id'] )
			{
				if ( !$model->POST_valid['confirm'] )
				{
					$model->confirm($model->t('Are you sure you wish to remove this user from this role?'));
				}
				else
				{
					$model->db->sql('
						DELETE
						FROM `' . $model->db->prefix . 'perms_roles_users_xref`
						WHERE
							`user_id` = ' . ( int ) $userId . ' AND
							`role_id` = ' . ( int ) $id . '
						;');

					if ( $model->db->result )
					{
						header('Location: ?notice=removed');

						$model->end();
					}
				}
			}

			break;
		case 'delete':
			if ( !$model->POST_valid['confirm'] )
			{
				$model->confirm($model->t('Are you sure you wish to delete this role?'));
			}
			else
			{
				$model->db->sql('
					DELETE
						pr, prx, prux
					FROM      `' . $model->db->prefix . 'perms_roles`            AS pr
					LEFT JOIN `' . $model->db->prefix . 'perms_roles_xref`       AS prx  ON pr.`id`       = prx.`role_id`
					LEFT JOIN `' . $model->db->prefix . 'perms_roles_users_xref` AS prux ON prx.`role_id` = prux.`role_id`
					WHERE
						pr.`id` = ' . ( int ) $id . '
					;');

				if ( $model->db->result )
				{
					header('Location: ?notice=deleted');

					$model->end();
				}
			}

			break;
	}
}

$model->POST_html_safe['value'] = $values;

$view->id     = $id;
$view->action = $action;
$view->perms  = $perms;
$view->roles  = $roles;

$view->load('admin/perms.html.php');

$model->end();