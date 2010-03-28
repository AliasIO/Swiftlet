<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'   => '../',
	'pageTitle'  => 'CSS Parser',
	'standAlone' => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

/*
 * Parse CSS files so we can use variables
 */
if ( !empty($model->GET_raw['file']) && is_file($file = $contr->viewPath . $model->GET_raw['file'] . '.css') )
{
	$css = file_get_contents($file);

	preg_match('/@variables \{([^}]+)\}/s', $css, $m);

	if ( isset($m[1]) )
	{
		foreach ( explode(';', trim($m[1])) as $pair )
		{
			if ( strstr($pair, ':') )
			{
				list($k, $v) = explode(':', $pair);

				$css = str_replace('var(' . trim($k) . ')', trim($v), $css);
			}
		}

		header('Content-type: text/css');

		echo trim(str_replace($m[0], '', $css));
	}
}

$model->end();
