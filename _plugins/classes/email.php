<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * E-mail
 * @abstract
 */
class Email
{
	public
		$ready = FALSE
		;

	private
		$app,
		$view,
		$controller
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		$this->ready = TRUE;
	}

	/**
	 * Send an e-mail
	 */
	function send($params)
	{
		$headers = array(
			'To'           => '<' . $params['to'] . '>',
			'From'         => $this->app->siteName . ' <' . $this->app->adminEmail . '>',
			'MIME-Version' => '1.0',
			'Content-type' => 'text/html; charset=UTF-8',
			'X-Mailer'     => 'Swiftlet - http://swiftlet.org'
			);

		$headers = array_merge($headers, $params['headers']);

		$head = '';

		foreach ( $headers as $k => $v )
		{
			$head .= $k . ': ' . $v . "\r\n";
		}

		return mail($params['to'], $params['subject'], $params['body'], $head);
	}
}
