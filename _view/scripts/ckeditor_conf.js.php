<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'   => '../../',
	'pageTitle'  => 'CKEditor config',
	'standAlone' => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

?>
CKEDITOR.config.baseHref = 'http://<?php echo $_SERVER['SERVER_NAME'] . $contr->absPath ?>';
CKEDITOR.config.height   = '400';
CKEDITOR.config.toolbar  = [
	['Format'],
	['Bold', 'Italic', 'Underline', 'Strike'],
	['NumberedList', 'BulletedList'],
	['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
	['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord'],
	['RemoveFormat'],
	['Link', 'Unlink'],
	['Image', 'Flash', 'Table', 'SpecialChar'],
	['Source']
	];
<?php

$model->end();
?>