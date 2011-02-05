<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Doc
 * @abstract
 */
class Doc_Controller extends Controller
{
	public
		$pageTitle = 'Documentation'
		;

	function init()
	{
		$file = 'intro.html';

		if ( isset($this->app->input->args[1]) )
		{
			$file = implode('/', $this->app->input->args) . '.html';
		}

		$contents = '';

		if ( is_file('docs/' . $file) )
		{
			$contents = @file_get_contents('docs/' . $file);

			preg_match('/<h2>(.+?)<\/h2>/', $contents, $m);

			if ( $m )
			{
				$this->pageTitle = $m[1];
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

		$this->view->contents = $contents;

		$this->view->load('doc.html.php');
	}
}
