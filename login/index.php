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
		$model->confirm('Do you want to logout?');
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
		$view->error = 'Please provide a username and password.';
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
			$view->error = 'Incorrect username/password combination.';
		}
	}
}

if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'login':
			$view->notice = 'Hello ' . $model->session->get('user username') . ', you are now logged in.';
		
			break;
		case 'logout':
			$view->notice = 'You are now logged out.';

			break;
	}
}

$view->load('login.html.php');

$model->end();