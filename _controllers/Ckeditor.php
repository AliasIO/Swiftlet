<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

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
