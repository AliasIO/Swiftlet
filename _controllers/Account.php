<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Account
 * @abstract
 */
class Account_Controller extends Controller
{
	public
		$pageTitle    = 'Account settings',
		$dependencies = array('db', 'session', 'user', 'input')
		;

	function init()
	{
		// Get preferences
		$prefsValidate = array();

		if ( $this->app->user->prefs )
		{
			foreach ( $this->app->user->prefs as $d )
			{
				$prefsValidate['pref-' . $d['id']] = $d['match'];
			}
		}

		$this->app->input->validate(array(
			'form-submit'          => 'bool',
			'username'             => 'string, empty',
			'password'             => 'string, empty',
			'new_password'         => 'string, empty',
			'new_password_confirm' => 'string, empty',
			'email'                => 'email,  empty',
			'owner'                => 'bool'
			) + $prefsValidate);

		if ( $this->app->session->get('user id') == User_Plugin::GUEST_ID )
		{
			header('Location: ' . $this->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

			$this->app->end();
		}

		$id     = isset($this->app->input->GET_raw['id']) && ( int ) $this->app->input->GET_raw['id'] ? ( int ) $this->app->input->GET_raw['id'] : FALSE;
		$action = isset($this->app->input->GET_raw['action']) ? $this->app->input->GET_raw['action'] : 'edit';

		if ( $action != 'edit' && !$this->app->session->get('user is owner') )
		{
			$action = 'edit';
		}

		if ( $id && ( $action == 'edit' || $action == 'delete' ) && $this->app->session->get('user is owner') )
		{
			$this->app->db->sql('
				SELECT
					*
				FROM `' . $this->app->db->prefix . 'users`
				WHERE
					`id` = ' . $id . '
				LIMIT 1
				;');

			if ( $r = $this->app->db->result )
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
						'id'       => $this->app->session->get('user id'),
						'username' => $this->app->session->get('user username'),
						'email'    => $this->app->session->get('user email'),
						'owner'    => $this->app->session->get('user is owner')
						);
			}
		}

		// Get user's preferences
		foreach ( $this->app->user->prefs as $pref )
		{
			$user['pref-' . $pref['id']] = '';
		}

		if ( $user['id'] )
		{
			$userprefs = $this->app->user->get_pref_values($user['id']);

			if ( $userprefs )
			{
				foreach ( $userprefs as $k => $v )
				{
					$user['pref-' . $this->app->user->prefs[$k]['id']] = $v;
				}
			}
		}

		if ( $this->app->input->POST_valid['form-submit'] )
		{
			if ( $action == 'edit' )
			{
				if ( !$this->app->session->get('user is owner') || !$id || $this->app->session->get('user id') == $id )
				{
					if ( !$this->app->user->validate_password($this->app->session->get('user username'), $this->app->input->POST_raw['password']) )
					{
						$this->app->input->errors['password'] = $this->view->t('Incorrect password, try again');
					}
				}
			}

			if ( $action == 'create' && !$this->app->input->POST_valid['new_password'] )
			{
				$this->app->input->errors['new_password'] = $this->view->t('Please provide a password');
			}

			if ( $this->app->input->POST_valid['new_password'] || $this->app->input->POST_valid['new_password_confirm'] )
			{
				if ( $this->app->input->POST_valid['new_password'] != $this->app->input->POST_valid['new_password_confirm'] )
				{
					$this->app->input->errors['new_password_repeat'] = $this->view->t('Passwords do not match');
				}
			}

			if ( $this->app->session->get('user is owner') )
			{
				if ( !$this->app->input->POST_valid['username'] )
				{
					$this->app->input->errors['username'] = $this->view->t('Please provide a username');
				}

				if ( strtolower($this->app->input->POST_html_safe['username']) != strtolower($user['username']) )
				{
					$this->app->db->sql('
						SELECT
							`id`
						FROM `' . $this->app->db->prefix . 'users`
						WHERE
							`username` = "' . $this->app->input->POST_db_safe['username'] . '"
						LIMIT 1
						;');

					if ( $this->app->db->result )
					{
						$this->app->input->errors['username'] = $this->view->t('Username has already been taken');
					}
				}
			}

			if ( $this->app->input->errors )
			{
				$this->view->error = $this->view->t('Please correct the errors below.');
			}
			else
			{
				$username = $user['username'];
				$owner    = $user['owner'];

				if ( $this->app->session->get('user is owner') )
				{
					$username = $this->app->input->POST_db_safe['username'];

					if ( $this->app->session->get('user id') != $user['id'] )
					{
						$owner = $this->app->input->POST_valid['owner'];
					}
				}

				$password = $this->app->input->POST_valid['new_password'] ? $this->app->input->POST_valid['new_password'] : $this->app->input->POST_raw['password'];

				$passHash = $this->app->user->make_pass_hash($username, $password);

				$email = $this->app->input->POST_valid['email'] ? $this->app->input->POST_db_safe['email'] : FALSE;

				switch ( $action )
				{
					case 'create':
						$this->app->db->sql('
							INSERT INTO `' . $this->app->db->prefix . 'users` (
								`username`,
								`email`,
								`owner`,
								`date`,
								`date_edit`,
								`pass_hash`
								)
							VALUES (
								"' . $this->app->db->escape($username) . '",
								"' . $email . '",
								' . ( int ) $owner . ',
								"' . gmdate('Y-m-d H:i:s') . '",
								"' . gmdate('Y-m-d H:i:s') . '",
								"' . $passHash . '"
								)
								;');

						if ( $newId = $this->app->db->result )
						{
							foreach ( $this->app->user->prefs as $pref )
							{
								$this->app->user->save_pref_value(array(
									'user_id' => ( int ) $newId,
									'pref'    => $pref['pref'],
									'value'   => $this->app->input->POST_db_safe['pref-' . $pref['id']]
									));
							}

							header('Location: ?id=' . $this->app->db->result . '&notice=created');

							$this->app->end();
						}

						break;
					case 'edit':
						$this->app->db->sql('
							UPDATE `' . $this->app->db->prefix . 'users` SET
								`username`  = "' . $this->app->db->escape($username) . '",
								`email`     = "' . $email                        . '",
								`owner`     =  ' . ( int ) $owner                . ',
								`date_edit` = "' . gmdate('Y-m-d H:i:s')         . '",
								`pass_hash` = "' . $passHash                     . '"
							WHERE
								`id` = ' . ( int ) $user['id'] . '
							LIMIT 1
							;');

						if ( $this->app->db->result )
						{
							if ( $this->app->session->get('user id') == $user['id'] )
							{
								$this->app->session->put(array(
									'user username' => $username,
									'user email'    => $email,
									'user is owner' => ( int ) $owner
									));
							}

							foreach ( $this->app->user->prefs as $pref )
							{
								$this->app->user->save_pref_value(array(
									'user_id' => ( int ) $user['id'],
									'pref'    => $pref['pref'],
									'value'   => $this->app->input->POST_db_safe['pref-' . $pref['id']]
									));
							}

							header('Location: ?id=' . ( int ) $user['id'] . '&notice=saved');

							$this->app->end();
						}
						else
						{
							$this->view->notice = $this->view->t('There were no changes.');
						}
				}
			}
		}
		else
		{
			/**
			 * Default form values
			 */
			$this->app->input->POST_html_safe['username'] = $user['username'];
			$this->app->input->POST_html_safe['email']    = $user['email'];
			$this->app->input->POST_html_safe['owner']    = ( int ) $user['owner'];

			foreach ( $this->app->user->prefs as $d )
			{
				$this->app->input->POST_html_safe['pref-' . $d['id']] = $user['pref-' . $d['id']];
			}
		}

		switch ( $action )
		{
			case 'delete':
				if ( $user && $this->app->session->get('user is owner') )
				{
					if ( !$this->app->input->POST_valid['confirm'] )
					{
						$this->app->input->confirm($this->view->t('Are you sure you wish to delete this account?'));
					}
					else
					{
						// Delete account
						$this->app->db->sql('
							DELETE
							FROM `' . $this->app->db->prefix . 'users`
							WHERE
								`id` = ' . ( int ) $id . '
							LIMIT 1
							;');

						if ( $this->app->db->result )
						{
							$this->app->db->sql('
								DELETE
								FROM `' . $this->app->db->prefix . 'user_prefs_xref`
								WHERE
									`user_id` = ' . ( int ) $id . '
								;');

							header('Location: ?notice=deleted');

							$this->app->end();
						}
					}
				}

				break;
		}

		if ( isset($this->app->input->GET_raw['notice']) )
		{
			switch ( $this->app->input->GET_raw['notice'] )
			{
				case 'saved':
					$this->view->notice = $this->view->t('Your changes have been saved.');

					break;
				case 'created':
					$this->view->notice = $this->view->t('The account has been created.');

					break;
				case 'deleted':
					$this->view->notice = $this->view->t('The account has been deleted.');

					break;
			}
		}

		$this->view->userId       = $user['id'];
		$this->view->userUsername = $user['username'];

		if ( $this->app->session->get('user is owner') )
		{
			$this->app->db->sql('
				SELECT
					COUNT(`id`) as `count`
				FROM `' . $this->app->db->prefix . 'users`
				;');

			if ( $r = $this->app->db->result )
			{
				$usersPagination = $this->view->paginate('users', $r[0]['count'], 25);

				$this->app->db->sql('
					SELECT
						`id`,
						`username`
					FROM `' . $this->app->db->prefix . 'users`
					ORDER BY `username`
					LIMIT ' . $usersPagination['from'] . ', 25
					;');

				if ( $r = $this->app->db->result )
				{
					$this->view->users = array();

					foreach ( $r as $i => $d )
					{
						$this->view->users[$d['id']] = $d['username'];
					}

					asort($this->view->users);
				}

				$this->view->usersPagination = $usersPagination;
			}
		}

		$this->view->prefs   = $this->app->user->prefs;
		$this->view->id      = $id;
		$this->view->action  = $action;

		$this->view->load('account.html.php');
	}
}
