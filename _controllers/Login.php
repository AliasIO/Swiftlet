V<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

class Login extends Controller
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
			'action'      => 'string'
			));

		if ( isset($this->app->input->GET_raw['logout']) )
		{
			if ( !$this->app->input->POST_valid['confirm'] )
			{
				$this->app->input->confirm($this->view->t('Do you want to log out?'));
			}
			else
			{
				$this->app->user->logout();

				header('Location: ?notice=logout');

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
					FROM `' . $this->app->db->prefix . 'users`
					WHERE
						`username` = "' . $this->app->input->POST_html_safe['username'] . '"
					LIMIT 1
					;');

				if ( isset($this->app->db->result[0]) && $r = $this->app->db->result[0] )
				{
					if ( strtotime($r['date_login_attempt']) > gmmktime() - 3 )
					{
						$this->view->error = $this->view->t('Only one log in attempt per 3 seconds allowed, please try again.');
					}
					else
					{
						$r = $this->app->user->login($this->app->input->POST_html_safe['username'], $this->app->input->POST_raw['password']);

						if ( $r )
						{
							if ( !empty($this->app->input->GET_raw['ref']) )
							{
								/*
								 * Header injection is not an issue here, header()
								 * prevents more than one header to be sent at once
								 */
								header('Location: ' . $this->app->input->GET_raw['ref']);

								$this->app->end();
							}

							header('Location: ?notice=login');

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

		if ( $this->app->session->get('user id') == User::GUEST_ID )
		{
			$this->app->db->sql('
				SELECT
					`date_login_attempt`
				FROM `' . $this->app->db->prefix . 'users`
				WHERE
					`date_login_attempt` AND
					`id` = 1
				LIMIT 1
				;');

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
					if ( $this->app->session->get('user id') != User::GUEST_ID )
					{
						$this->view->notice = $this->view->t('Hello %1$s, you are now logged in.', $this->app->session->get('user username'));
					}

					break;
				case 'logout':
					if ( $this->app->session->get('user id') == User::GUEST_ID )
					{
						$this->view->notice = $this->view->t('You are now logged out.');
					}

					break;
			}
		}

		$this->view->load('login.html.php');
	}
}
