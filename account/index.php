<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Account settings'
	);

require($contrSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'session', 'user', 'form'));

// Get preferences
$prefsValidate = array();

if ( $app->user->prefs )
{
	foreach ( $app->user->prefs as $d )
	{
		$prefsValidate['pref-' . $d['id']] = $d['match'];
	}
}

$app->form->validate(array(
	'form-submit'          => 'bool',
	'username'             => 'string, empty',
	'password'             => 'string, empty',
	'new_password'         => 'string, empty',
	'new_password_confirm' => 'string, empty',
	'email'                => 'email,  empty',
	'owner'                => 'bool'
	) + $prefsValidate);

if ( $app->session->get('user id') == user::guestId )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

$id     = isset($app->GET_raw['id']) && ( int ) $app->GET_raw['id'] ? ( int ) $app->GET_raw['id'] : FALSE;
$action = isset($app->GET_raw['action']) ? $app->GET_raw['action'] : 'edit';

if ( $action != 'edit' && !$app->session->get('user is owner') )
{
	$action = 'edit';
}

if ( $id && ( $action == 'edit' || $action == 'delete' ) && $app->session->get('user is owner') )
{
	$app->db->sql('
		SELECT
			*
		FROM `' . $app->db->prefix . 'users`
		WHERE
			`id` = ' . $id . '
		LIMIT 1
		;');

	if ( $r = $app->db->result )
	{
		$user = array(
			'id'       => $r[0]['id'],
			'username' => $r[0]['username'],
			'email'    => $r[0]['email'],
			'owner'    => $r[0]['owner']
			);
	}
}

if ( !isset($user) )
{
	switch ( $action )
	{
		case 'create':
			$user = array(
				'id'       => '',
				'username' => '',
				'email'    => '',
				'owner'    => ''
				);

			break;
		case 'edit':
			$user = array(
				'id'       => $app->session->get('user id'),
				'username' => $app->session->get('user username'),
				'email'    => $app->session->get('user email'),
				'owner'    => $app->session->get('user is owner')
				);
	}
}

// Get user's preferences
foreach ( $app->user->prefs as $pref )
{
	$user['pref-' . $pref['id']] = '';
}

if ( $user['id'] )
{
	$userprefs = $app->user->get_pref_values($user['id']);

	if ( $userprefs )
	{
		foreach ( $userprefs as $k => $v )
		{
			$user['pref-' . $app->user->prefs[$k]['id']] = $v;
		}
	}
}

if ( $app->POST_valid['form-submit'] )
{
	if ( $action == 'edit' )
	{		
		if ( !$app->session->get('user is owner') || !$id || $app->session->get('user id') == $id )
		{
			if ( !$app->user->validate_password($app->session->get('user username'), $app->POST_raw['password']) )
			{
				$app->form->errors['password'] = $app->t('Incorrect password, try again');
			}
		}
	}

	if ( $action == 'create' && !$app->POST_valid['new_password'] )
	{
		$app->form->errors['new_password'] = $app->t('Please provide a password');
	}

	if ( $app->POST_valid['new_password'] || $app->POST_valid['new_password_confirm'] )
	{
		if ( $app->POST_valid['new_password'] != $app->POST_valid['new_password_confirm'] )
		{
			$app->form->errors['new_password_repeat'] = $app->t('Passwords do not match');
		}
	}

	if ( $app->session->get('user is owner') )
	{
		if ( !$app->POST_valid['username'] )
		{
			$app->form->errors['username'] = $app->t('Please provide a username');
		}
		
		if ( strtolower($app->POST_html_safe['username']) != strtolower($user['username']) )
		{
			$app->db->sql('
				SELECT
					`id`
				FROM `' . $app->db->prefix . 'users`
				WHERE
					`username` = "' . $app->POST_db_safe['username'] . '"
				LIMIT 1
				;');

			if ( $app->db->result )
			{
				$app->form->errors['username'] = $app->t('Username has already been taken');
			}
		}
	}

	if ( $app->form->errors )
	{
		$view->error = $app->t('Please correct the errors below.');
	}
	else
	{
		$username = $user['username'];
		$owner    = $user['owner'];

		if ( $app->session->get('user is owner') )
		{
			$username = $app->POST_db_safe['username'];

			if ( $app->session->get('user id') != $user['id'] )
			{
				$owner = $app->POST_valid['owner'];
			}
		}

		$password = $app->POST_valid['new_password'] ? $app->POST_valid['new_password'] : $app->POST_raw['password'];
		
		$passHash = $app->user->make_pass_hash($username, $password);

		$email = $app->POST_valid['email'] ? $app->POST_db_safe['email'] : FALSE;

		switch ( $action )
		{
			case 'create':
				$app->db->sql('
					INSERT INTO `' . $app->db->prefix . 'users` (
						`username`,
						`email`,
						`owner`,
						`date`,
						`date_edit`,
						`pass_hash`
						)
					VALUES (
						"' . $app->db->escape($username) . '",
						"' . $email . '",
						' . ( int ) $owner . ',
						"' . gmdate('Y-m-d H:i:s') . '",
						"' . gmdate('Y-m-d H:i:s') . '",
						"' . $passHash . '"
						)
						;');

				if ( $newId = $app->db->result )
				{
					foreach ( $app->user->prefs as $pref )
					{
						$app->user->save_pref_value(array(
							'user_id' => ( int ) $newId,
							'pref'    => $pref['pref'],
							'value'   => $app->POST_db_safe['pref-' . $pref['id']]
							));
					}

					header('Location: ?id=' . $app->db->result . '&notice=created');

					$app->end();
				}
			
				break;
			case 'edit':
				$app->db->sql('
					UPDATE `' . $app->db->prefix . 'users` SET
						`username`  = "' . $app->db->escape($username) . '",
						`email`     = "' . $email                        . '",
						`owner`     =  ' . ( int ) $owner                . ',
						`date_edit` = "' . gmdate('Y-m-d H:i:s')         . '",
						`pass_hash` = "' . $passHash                     . '"
					WHERE
						`id` = ' . ( int ) $user['id'] . '
					LIMIT 1
					;');

				if ( $app->db->result )
				{
					if ( $app->session->get('user id') == $user['id'] )
					{
						$app->session->put(array(
							'user username' => $username,
							'user email'    => $email,
							'user is owner' => ( int ) $owner
							));
					}

					foreach ( $app->user->prefs as $pref )
					{
						$app->user->save_pref_value(array(
							'user_id' => ( int ) $user['id'],
							'pref'    => $pref['pref'],
							'value'   => $app->POST_db_safe['pref-' . $pref['id']]
							));
					}

					header('Location: ?id=' . ( int ) $user['id'] . '&notice=saved');
					
					$app->end();
				}
				else
				{
					$view->notice = $app->t('There were no changes.');
				}
		}
	}
}
else
{
	/**
	 * Default form values
	 */
	$app->POST_html_safe['username'] = $user['username'];
	$app->POST_html_safe['email']    = $user['email'];
	$app->POST_html_safe['owner']    = ( int ) $user['owner'];

	foreach ( $app->user->prefs as $d )
	{
		$app->POST_html_safe['pref-' . $d['id']] = $user['pref-' . $d['id']];
	}
}

switch ( $action )
{
	case 'delete':
		if ( $user && $app->session->get('user is owner') )
		{
			if ( !$app->POST_valid['confirm'] )
			{
				$app->confirm($app->t('Are you sure you wish to delete this account?'));
			}
			else
			{
				// Delete account
				$app->db->sql('
					DELETE
					FROM `' . $app->db->prefix . 'users`
					WHERE
						`id` = ' . ( int ) $id . '
					LIMIT 1
					;');

				if ( $app->db->result )
				{
					$app->db->sql('
						DELETE
						FROM `' . $app->db->prefix . 'user_prefs_xref`
						WHERE
							`user_id` = ' . ( int ) $id . '
						;');

					header('Location: ?notice=deleted');

					$app->end();
				}
			}
		}

		break;
}

if ( isset($app->GET_raw['notice']) )
{
	switch ( $app->GET_raw['notice'] )
	{
		case 'saved':
			$view->notice = $app->t('Your changes have been saved.');

			break;
		case 'created':
			$view->notice = $app->t('The account has been created.');

			break;
		case 'deleted':
			$view->notice = $app->t('The account has been deleted.');

			break;
	}
}

$view->userId       = $user['id'];
$view->userUsername = $user['username'];

if ( $app->session->get('user is owner') )
{
	$app->db->sql('
		SELECT
			COUNT(`id`) as `count`
		FROM `' . $app->db->prefix . 'users`
		;');
	
	if ( $r = $app->db->result )
	{
		$usersPagination = $view->paginate('users', $r[0]['count'], 25);	
	
		$app->db->sql('
			SELECT
				`id`,
				`username`
			FROM `' . $app->db->prefix . 'users`
			ORDER BY `username`
			LIMIT ' . $usersPagination['from'] . ', 25
			;');

		if ( $r = $app->db->result )
		{
			$view->users = array();
			
			foreach ( $r as $i => $d )
			{
				$view->users[$d['id']] = $d['username'];
			}
			
			asort($view->users);
		}
		
		$view->usersPagination = $usersPagination;
	}
}

$view->prefs   = $app->user->prefs;
$view->id      = $id;
$view->action  = $action;

$view->load('account.html.php');

$app->end();
