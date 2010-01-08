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

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'session', 'user', 'form'));

// Get preferences
$prefsValidate = array();

if ( $model->user->prefs )
{
	foreach ( $model->user->prefs as $d )
	{
		$prefsValidate['pref-' . $d['id']] = $d['match'];
	}
}

$model->form->validate(array(
	'form-submit'      => 'bool',
	'username'         => 'string, empty',
	'password'         => 'string, empty',
	'password_confirm' => 'string, empty',
	'email'            => 'email,  empty',
	'auth'             => 'int,    empty'
	) + $prefsValidate);

if ( $model->session->get('user id') == user::guestId )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

$id     = isset($model->GET_raw['id']) && ( int ) $model->GET_raw['id'] ? ( int ) $model->GET_raw['id'] : FALSE;
$action = isset($model->GET_raw['action']) ? $model->GET_raw['action'] : 'edit';

if ( $action != 'edit' && $model->session->get('user auth') < user::admin )
{
	$action = 'edit';
}

if ( $id && ( $action == 'edit' || $action == 'delete' ) && $model->session->get('user auth') >= user::admin )
{
	$model->db->sql('
		SELECT
			*
		FROM `' . $model->db->prefix . 'users`
		WHERE
			`id` = ' . $id . '
		LIMIT 1
		;');

	if ( $r = $model->db->result )
	{
		$user = array(
			'id'       => $r[0]['id'],
			'username' => $r[0]['username'],
			'email'    => $r[0]['email'],
			'auth'     => $r[0]['auth']
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
				'auth'     => ''
				);

			break;
		case 'edit':
			$user = array(
				'id'       => $model->session->get('user id'),
				'username' => $model->session->get('user username'),
				'email'    => $model->session->get('user email'),
				'auth'     => $model->session->get('user auth')
				);
	}
}

// Get preference values for user
foreach ( $model->user->prefs as $pref )
{
	$user['pref-' . $pref['id']] = '';
}

if ( $user['id'] )
{
	$userprefs = $model->user->get_pref_values($user['id']);

	if ( $userprefs )
	{
		foreach ( $userprefs as $k => $v )
		{
			$user['pref-' . $model->user->prefs[$k]['id']] = $v;
		}
	}
}

if ( $model->POST_valid['form-submit'] )
{
	if ( $action == 'create' && !$model->POST_valid['password'] )
	{
		$model->form->errors['password'] = 'Please provide a password';
	}

	if ( $model->POST_valid['password'] || $model->POST_valid['password_confirm'] )
	{
		if ( $model->POST_valid['password'] != $model->POST_valid['password_confirm'] )
		{
			$model->form->errors['password_repeat'] = 'Passwords do not match';
		}
	}

	if ( $model->session->get('user auth') >= user::admin )
	{
		if ( !$model->POST_valid['username'] )
		{
			$model->form->errors['username'] = 'Please provide a username';
		}

		if ( strtolower($model->POST_raw['username']) != strtolower($user['username']) )
		{
			if ( !$model->POST_valid['password'] )
			{
				$model->form->errors['password'] = 'Please provide a password to change the username';
			}

			$model->db->sql('
				SELECT
					`id`
				FROM `' . $model->db->prefix . 'users`
				WHERE
					`username` = "' . $model->POST_db_safe['username'] . '"
				LIMIT 1
				;');

			if ( $model->db->result )
			{
				$model->form->errors['username'] = 'Username has already been taken';
			}
		}
	}

	if ( $model->form->errors )
	{
		$view->error = 'Please correct the errors below.';
	}
	else
	{
		$username = $user['username'];
		$auth     = $user['auth'];

		if ( $model->session->get('user auth') >= user::admin )
		{
			$username = $model->POST_db_safe['username'];

			if ( $model->session->get('user auth') > $user['auth'] && $model->session->get('user auth') > $model->POST_valid['auth'] )
			{
				$auth = $model->POST_db_safe['auth'];
			}
		}

		$passHash = $model->POST_valid['password'] ? sha1('swiftlet' . strtolower($username) . $model->POST_raw['password']) : FALSE;
		$email    = $model->POST_valid['email']    ? $model->POST_db_safe['email'] : FALSE;

		switch ( $action )
		{
			case 'create':
				$model->db->sql('
					INSERT INTO `' . $model->db->prefix . 'users` (
						`username`,
						`pass_hash`,
						`email`,
						`auth`,
						`date`,
						`date_edit`
						)
					VALUES (
						"' . $username . '",
						"' . $passHash . '",
						"' . $email . '",
						' . $auth . ',
						"' . gmdate('Y-m-d H:i:s') . '",
						"' . gmdate('Y-m-d H:i:s') . '"
						)
						;');

				if ( $newId = $model->db->result )
				{
					foreach ( $model->user->prefs as $pref )
					{
						$model->user->save_pref_value(array(
							'user_id' => $newId,
							'pref'    => $pref['pref'],
							'value'   => $model->POST_db_safe['pref-' . $pref['id']]
							));
					}

					header('Location: ?id=' . $model->db->result . '&notice=created');

					$model->end();
				}
			
				break;
			case 'edit':
				$model->db->sql('
					UPDATE `' . $model->db->prefix . 'users` SET
						`username`  = "' . $username . '",
						' . ( $passHash ? '`pass_hash` = "' . $passHash . '",' : '' ) . '
						`email`     = "' . $email . '",
						`auth`      = ' . $auth . ',
						`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
					WHERE
						`id` = ' . $user['id'] . '
					LIMIT 1
					;');

				if ( $model->db->result )
				{
					if ( $model->session->get('user id') == $user['id'] )
					{
						$model->session->put(array(
							'user username' => $username,
							'user email'    => $email,
							'user auth'     => $auth
							));
					}

					foreach ( $model->user->prefs as $pref )
					{
						$model->user->save_pref_value(array(
							'user_id' => $user['id'],
							'pref'    => $pref['pref'],
							'value'   => $model->POST_db_safe['pref-' . $pref['id']]
							));
					}

					header('Location: ?id=' . $user['id'] . '&notice=saved');
					
					$model->end();
				}
				else
				{
					$view->notice = 'There were no changes.';
				}
		}
	}
}
else
{
	/**
	 * Default form values
	 */
	$model->POST_html_safe['username'] = $model->h($user['username']);
	$model->POST_html_safe['email']    = $model->h($user['email']);
	$model->POST_html_safe['auth']     = $model->h($user['auth']);

	foreach ( $model->user->prefs as $d )
	{
		$model->POST_html_safe['pref-' . $d['id']] = $model->h($user['pref-' . $d['id']]);
	}
}

switch ( $action )
{
	case 'delete':
		if ( $user && $model->session->get('user auth') > $user['auth'] )
		{
			if ( !$model->POST_valid['confirm'] )
			{
				$model->confirm('Are you sure you wish to delete this account?');
			}
			else
			{
				// Delete account
				$model->db->sql('
					DELETE
					FROM `' . $model->db->prefix . 'users`
					WHERE
						`id` = ' . ( int ) $id . '
					LIMIT 1
					;');

				if ( $model->db->result )
				{
					$model->db->sql('
						DELETE
						FROM `' . $model->db->prefix . 'user_prefs_xref`
						WHERE
							`user_id` = ' . ( int ) $id . '
						;');

					header('Location: ?notice=deleted');

					$model->end();
				}
			}
		}

		break;
}

if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'saved':
			$view->notice = 'Your changes have been saved.';

			break;
		case 'created':
			$view->notice = 'The account has been created.';

			break;
		case 'deleted':
			$view->notice = 'The account has been deleted.';

			break;
	}
}

$view->userId       = $user['id'];
$view->userUsername = $model->h($user['username']);
$view->userAuth     = $user['auth'];

if ( $model->session->get('user auth') >= user::admin )
{
	$model->db->sql('
		SELECT
			`id`,
			`username`
		FROM `' . $model->db->prefix . 'users`
		;');

	if ( $r = $model->db->result )
	{
		$view->users = array();
		
		foreach ( $r as $i => $d )
		{
			$view->users[$d['id']] = $d['username'];
		}
		
		asort($view->users);
	}
}

$view->authLevels = array(
	-1 => 'Banned',
	0  => 'Guest',
	1  => 'User',
	2  => 'Editor',
	3  => 'Administrator',
	4  => 'Owner',
	5  => 'Developer'
	);

$view->prefs   = $model->user->prefs;
$view->id      = $id;
$view->action  = $action;

$view->load('account.html.php');

$model->end();