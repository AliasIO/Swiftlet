<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Unit tests'
	);

require($controllerSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'session', 'user'));

$app->input->validate(array(
	));

if ( !$app->session->get('user is owner') )
{
	header('Location: ' . $controller->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

if ( !$app->input->POST_valid['confirm'] )
{
	$app->input->confirm($view->t('Do you want run unit tests?'));
}
else
{
	$tests = array();

	/**
	 * Forms
	 */
	$params = array(
		'foo' => 'bar'
		);

	$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'index.php', $params);

	$tests[] = array(
		'test' => 'Submitting a form without authenticity token should result in an error (503).',
		'pass' => $r['info']['http_code'] == '503'
		);

	$app->hook('unit_tests', $tests);

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

$app->end();

/**
 * Make a POST request
 * @param string $url
 * @param array $params
 * @return array
 */
function post_request($url, $params, $guest = FALSE)
{
	global $app;

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
		$app->error(FALSE, 'Your PHP installation does not support cURL, a requirement for some unit tests.');
	}
}
