<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Unit tests'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'session', 'user'));

$model->form->validate(array(
	));

if ( $model->session->get('user auth') < user::editor )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

if ( !$model->POST_valid['confirm'] )
{
	$model->confirm('Do you want run unit tests?');
}
else
{
	$tests = array();

	/**
	 * /_config.php
	 */
	$tests[] = array(
		'test' => '<em>For distributors only</em> &mdash; <code>sysPassword</code> should be empty <code>/_config.php</code>.',
		'pass' => $model->sysPassword == ''
		);

	$tests[] = array(
		'test' => '<em>For distributors only</em> &mdash; <code>dbName</code> should be empty <code>/_config.php</code>.',
		'pass' => $model->dbName == ''
		);

	$tests[] = array(
		'test' => '<em>For distributors only</em> &mdash; <code>debugMode</code> should be set to <code>TRUE</code> in <code>/_config.php</code>.',
		'pass' => $model->debugMode == TRUE
		);

	$tests[] = array(
		'test' => '<em>For distributors only</em> &mdash; <code>urlRewrite</code> should be set to <code>TRUE</code> <code>/_config.php</code>.',
		'pass' => $model->urlRewrite == TRUE
		);

	/**
	 * Forms
	 */
	$params = array(
		'foo' => 'bar'
		);

	$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'index.php', $params);

	$tests[] = array(
		'test' => 'Submitting a form without authenticity token should result in an error (503).',
		'pass' => $r['info']['http_code'] == '503'
		);

	$model->hook('unit_tests', $tests);

	$passes   = 0;
	$failures = 0;
	
	foreach ( $tests as $test => $d )
	{
		if ( $d['pass'] )
		{
			$passes ++;
		}
		else
		{
			$failures ++;
		}
	}

	$view->tests    = $tests;
	$view->passes   = $passes;
	$view->failures = $failures;

	$view->load('unit_tests.html.php');
}

$model->end();

/**
 * Make a POST request
 * @param string $url
 * @param array $params
 * @return array
 */
function post_request($url, $params, $guest = FALSE)
{
	global $model;

	if ( function_exists('curl_init') )
	{
		$cookies = '';

		if ( !$guest )
		{
			// Let's hijack your session for this request
			if ( !empty($_COOKIE) )
			{
				foreach ( $_COOKIE as $k => $v )
				{
					$cookies .= $k . '=' . $v . ';';
				}
			}
		}

		$handle = curl_init();

		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_COOKIE         => $cookies,
			CURLOPT_USERAGENT      => $guest ? '' : $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_HEADER         => FALSE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_FOLLOWLOCATION => FALSE,
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_POST           => TRUE,
			CURLOPT_POSTFIELDS     => $params
			);

		curl_setopt_array($handle, $options);

		// We can't use the same session twice, close it first
		session_commit();

		$output = curl_exec($handle);

		session_start();

		$result = array(
			'output' => $output,			
			'info'   => curl_getinfo($handle)
			);

		curl_close($handle);

		unset($output);

		return $result;
	}
	else
	{
		$model->error(FALSE, 'Your PHP installation does not support cURL, a requirement for some unit tests.');
	}
}