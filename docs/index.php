<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Documenation'
	);

require($controllerSetup['rootPath'] . 'init.php');

$file = 'intro.html';

if ( isset($app->routeParts[1]) )
{
	$file = './' . implode('/', array_slice($app->routeParts, 1)) . '.html';
}

$contents = '';

if ( is_file ( $file ) )
{
	$contents = @file_get_contents($file);

	preg_match('/<h2>(.+?)<\/h2>/', $contents, $m);

	if ( $m )
	{
		$view->pageTitle = $app->h($m[1]);
	}

	/*
	 * Code syntax markup
	 */
	preg_match_all('/<pre>(.+?)<\/pre>/s', $contents, $m);

	if ( $m )
	{
		foreach ( $m[0] as $v )
		{
			$code = highlight_string(preg_replace('/<\/?pre>\r*\n*/', '', $v), TRUE);

			$gutter = '';

			for ( $i = 1; $i <= ( $lines = substr_count($code, '<br />') ); $i ++ )
			{
				$gutter .= sprintf('%0' . strlen($lines) . 'd', $i) . '<br/>';
			}

			$code = '<div class="syntax"><div class="gutter">' . $gutter . '</div><div class="code">' . $code . '</div></div>';

			$contents = str_replace($v, $code, $contents);
		}
	}
}

$view->contents = $contents;

$view->load('docs.html.php');

$app->end();
