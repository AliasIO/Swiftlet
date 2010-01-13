<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => ( isset($_GET['logout']) ? 'Logout' : 'Login' )
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'session', 'user', 'form'));

$model->form->validate(array(
	'form-submit' => 'bool',
	'username'    => 'string',
	'password'    => 'string',
	'action'      => 'string'
	));

if ( isset($model->GET_raw['logout']) )
{
	if ( !$model->POST_valid['confirm'] )
	{
		$model->confirm($model->t('Do you want to logout?'));
	}
	else
	{
		$model->user->logout();

		header('Location: ?notice=logout');

		$model->end();
	}
}

if ( $model->POST_valid['form-submit'] )
{
	if ( $model->form->errors )
	{
		$view->error = $model->t('Please provide a username and password.');
	}
	else
	{
		$r = $model->user->login($model->POST_raw['username'], $model->POST_raw['password']);

		if ( $r )
		{
			if ( !empty($model->GET_raw['ref']) )
			{
				header('Location: ' . $model->GET_raw['ref']);

				$model->end();
			}
			
			header('Location: ?notice=login');

			$model->end();
		}
		else
		{
			$view->error = $model->t('Incorrect username/password combination.');
		}
	}
}

if ( isset($model->GET_raw['ref']) )
{
	$view->notice = $model->t('Please login with an authenticated account.');
}

if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'login':
			$view->notice = $model->t('Hello %1$s, you are now logged in.', $model->session->get('user username'));
		
			break;
		case 'logout':
			$view->notice = $model->t('You are now logged out.');

			break;
	}
}

$view->load('login.html.php');

$model->end();