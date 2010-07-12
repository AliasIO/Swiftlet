<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => ( isset($_GET['logout']) ? 'Log out' : 'Log in' )
	);

require($contrSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'session', 'user', 'form'));

$app->form->validate(array(
	'form-submit' => 'bool',
	'username'    => 'string',
	'password'    => 'string',
	'action'      => 'string'
	));

if ( isset($app->GET_raw['logout']) )
{
	if ( !$app->POST_valid['confirm'] )
	{
		$app->confirm($app->t('Do you want to log out?'));
	}
	else
	{
		$app->user->logout();

		header('Location: ?notice=logout');

		$app->end();
	}
}

if ( $app->POST_valid['form-submit'] )
{
	if ( $app->form->errors )
	{
		$view->error = $app->t('Please provide a username and password.');
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
				`username` = "' . $app->POST_html_safe['username'] . '"
			LIMIT 1
			;');

		if ( isset($app->db->result[0]) && $r = $app->db->result[0] )
		{
			if ( strtotime($r['date_login_attempt']) > gmmktime() - 3 )
			{
				$view->error = $app->t('Only one log in attempt per 3 seconds allowed, please try again.');
			}
			else
			{
				$r = $app->user->login($app->POST_html_safe['username'], $app->POST_raw['password']);

				if ( $r )
				{
					if ( !empty($app->GET_raw['ref']) )
					{
						/*
						 * Header injection is not an issue here, header()
						 * prevents more than one header to be sent at once
						 */
						header('Location: ' . $app->GET_raw['ref']);

						$app->end();
					}

					header('Location: ?notice=login');

					$app->end();
				}
				else
				{
					$view->error = $app->t('Incorrect password, try again.');
				}
			}
		}
		else
		{
			$view->error = $app->t('Sorry, we have no record of that username.');
		}

	}
}

if ( isset($app->GET_raw['ref']) && empty($view->error) )
{
	$view->notice = $app->t('Please log in with an authenticated account.');
}

if ( $app->session->get('user id') == user::guestId )
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
		$view->notice = $app->t('An account has been created with username "Admin" and the system password (%1$s in %2$s).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
	}
}

if ( isset($app->GET_raw['notice']) )
{
	switch ( $app->GET_raw['notice'] )
	{
		case 'login':
			if ( $app->session->get('user id') != user::guestId )
			{
				$view->notice = $app->t('Hello %1$s, you are now logged in.', $app->session->get('user username'));
			}

			break;
		case 'logout':
			if ( $app->session->get('user id') == user::guestId )
			{
				$view->notice = $app->t('You are now logged out.');
			}

			break;
	}
}

$view->load('login.html.php');

$app->end();
