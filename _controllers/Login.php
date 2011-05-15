<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Login
 * @abstract
 */
class Login_Controller extends Controller
{
	public
		$pageTitle    = 'Log in',
		$dependencies = array('db', 'session', 'user', 'input')
		;

	function init()
	{
		$this->app->input->validate(array(
			'form-submit' => 'bool',
			'username'    => 'string',
			'password'    => 'string',
			'remember'    => 'bool'
			));

		if ( $this->action == 'logout' )
		{
			$this->pageTitle = 'Log out';

			if ( !$this->app->input->POST_valid['confirm'] )
			{
				$this->app->input->confirm($this->view->t('Do you want to log out?'));
			}
			else
			{
				$this->app->user->logout();

				header('Location: ' . $this->view->route($this->path . '?notice=logout', FALSE));

				$this->app->end();
			}
		}

		if ( $this->app->input->POST_valid['form-submit'] )
		{
			if ( $this->app->input->errors )
			{
				$this->view->error = $this->view->t('Please provide a username and password.');
			}
			else
			{
				/*
				 * Limit the number of login attempts to 1 per 3 seconds
				 */
				$this->app->db->sql('
					SELECT
						`date_login_attempt`
					FROM {users}
					WHERE
						`username` = "' . $this->app->input->POST_db_safe['username'] . '"
					LIMIT 1
					');

				if ( isset($this->app->db->result[0]) && $r = $this->app->db->result[0] )
				{
					if ( strtotime($r['date_login_attempt']) > gmdate('U') - 3 )
					{
						$this->view->error = $this->view->t('Only one log in attempt per 3 seconds allowed, please try again.');
					}
					else
					{
						$r = $this->app->user->login($this->app->input->POST_raw['username'], $this->app->input->POST_raw['password'], $this->app->input->POST_raw['remember']);

						if ( $r )
						{
							if ( !empty($this->app->input->GET_raw['ref']) )
							{
								/*
								 * Header injection is not an issue here, header()
								 * prevents more than one header to be sent at once
								 */
								header('Location: ' . $this->view->route($this->app->input->GET_raw['ref'], FALSE));

								$this->app->end();
							}

							header('Location: ' . $this->view->route($this->path . '?notice=login', FALSE));

							$this->app->end();
						}
						else
						{
							$this->view->error = $this->view->t('Incorrect password, try again.');
						}
					}
				}
				else
				{
					$this->view->error = $this->view->t('Sorry, we have no record of that username.');
				}

			}
		}

		if ( isset($this->app->input->GET_raw['ref']) && empty($this->view->error) )
		{
			$this->view->notice = $this->view->t('Please log in with an authenticated account.');
		}

		if ( !$this->app->session->id )
		{
			$this->app->db->sql('
				SELECT
					`date_login_attempt`
				FROM {users}
				WHERE
					`date_login_attempt` AND
					`id` = 1
				LIMIT 1
				');

			if ( empty($this->app->db->result) )
			{
				$this->view->notice = $this->view->t('An account has been created with username "Admin" and the system password (%1$s in %2$s).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
			}
		}

		if ( isset($this->app->input->GET_raw['notice']) )
		{
			switch ( $this->app->input->GET_raw['notice'] )
			{
				case 'login':
					if ( $this->app->session->id )
					{
						$this->view->notice = $this->view->t('Hello %1$s, you are now logged in.', $this->app->session->get('user username'));
					}

					break;
				case 'logout':
					if ( !$this->app->session->id )
					{
						$this->view->notice = $this->view->t('You are now logged out.');
					}

					break;
			}
		}

		$this->view->load('login.html.php');
	}
}
