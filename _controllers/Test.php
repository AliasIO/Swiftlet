<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Unit tests
 * @abstract
 */
class Test_Controller extends Controller
{
	public
		$pageTitle    = 'Unit tests',
		$dependencies = array('db', 'session', 'user')
		;

	function init()
	{
		$this->app->input->validate(array(
			));

		if ( !$this->app->session->get('user is owner') )
		{

			header('Location: ' . $this->view->route('login?ref=' . $this->request, FALSE));

			$this->app->end();
		}

		if ( !$this->app->input->POST_valid['confirm'] )
		{
			$this->app->input->confirm($this->view->t('Do you want run unit tests?'));
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

			$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->absPath . 'index.php', $params);

			$tests[] = array(
				'test' => 'Submitting a form without authenticity token should result in an error (503).',
				'pass' => $r['info']['http_code'] == '503'
				);

			$this->app->hook('unit_tests', $tests);

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

			$this->view->tests    = $tests;
			$this->view->passes   = $passes;
			$this->view->failures = $failures;

			$this->view->load('test.html.php');
		}
	}
}
