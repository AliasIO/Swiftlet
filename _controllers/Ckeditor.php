<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * CKEditor
 * @abstract
 */

class Ckeditor_Controller extends Controller
{
	public
		$pageTitle  = 'CKEditor configuration',
		$standAlone = TRUE
		;

	function init()
	{
		header('Content-type: text/javascript');

		$this->view->load('scripts/ckeditor.js.php');
	}
}
