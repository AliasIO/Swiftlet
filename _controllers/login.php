<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => '../',
	'pageTitle' => ( isset($_GET['logout']) ? 'Log out' : 'Log in' )
	);

require($controllerSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'session', 'user', 'input'));

$app->input->validate(array(
	'form-submit' => 'bool',
	'username'    => 'string',
	'password'    => 'string',
	'action'      => 'string'
	));

if ( isset($app->input->GET_raw['logout']) )
{
	if ( !$app->input->POST_valid['confirm'] )
	{
		$app->input->confirm($view->t('Do you want to log out?'));
	}
	else
	{
		$app->user->logout();

		header('Location: ?notice=logout');

		$app->end();
	}
}

if ( $app->input->POST_valid['form-submit'] )
{
	if ( $app->input->errors )
	{
		$view->error = $view->t('Please provide a username and password.');
	}
	else
	{
		/*
		 * Limit the number of login attempts to 1 per 3 seconds
 		 */
		$app->db->sql('
			SELECT
				`date_login_attempt`
			FROM `' . $app->db->prefix . 'users`
			WHERE
				`username` = "' . $app->input->POST_html_safe['username'] . '"
			LIMIT 1
			;');

		if ( isset($app->db->result[0]) && $r = $app->db->result[0] )
		{
			if ( strtotime($r['date_login_attempt']) > gmmktime() - 3 )
			{
				$view->error = $view->t('Only one log in attempt per 3 seconds allowed, please try again.');
			}
			else
			{
				$r = $app->user->login($app->input->POST_html_safe['username'], $app->input->POST_raw['password']);

				if ( $r )
				{
					if ( !empty($app->input->GET_raw['ref']) )
					{
						/*
						 * Header injection is not an issue here, header()
						 * prevents more than one header to be sent at once
						 */
						header('Location: ' . $app->input->GET_raw['ref']);

						$app->end();
					}

					header('Location: ?notice=login');

					$app->end();
				}
				else
				{
					$view->error = $view->t('Incorrect password, try again.');
				}
			}
		}
		else
		{
			$view->error = $view->t('Sorry, we have no record of that username.');
		}

	}
}

if ( isset($app->input->GET_raw['ref']) && empty($view->error) )
{
	$view->notice = $view->t('Please log in with an authenticated account.');
}

if ( $app->session->get('user id') == User::GUEST_ID )
{
	$app->db->sql('
		SELECT
			`date_login_attempt`
		FROM `' . $app->db->prefix . 'users`
		WHERE
			`date_login_attempt` AND
			`id` = 1
		LIMIT 1
		;');

	if ( empty($app->db->result) )
	{
		$view->notice = $view->t('An account has been created with username "Admin" and the system password (%1$s in %2$s).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
	}
}

if ( isset($app->input->GET_raw['notice']) )
{
	switch ( $app->input->GET_raw['notice'] )
	{
		case 'login':
			if ( $app->session->get('user id') != User::GUEST_ID )
			{
				$view->notice = $view->t('Hello %1$s, you are now logged in.', $app->session->get('user username'));
			}

			break;
		case 'logout':
			if ( $app->session->get('user id') == User::GUEST_ID )
			{
				$view->notice = $view->t('You are now logged out.');
			}

			break;
	}
}

$view->load('login.html.php');

$app->end();
