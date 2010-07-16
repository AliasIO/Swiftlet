<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Files',
	'inAdmin'   => TRUE
	);

require($controllerSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'input', 'permission'));

$app->input->validate(array(
	'form-submit' => 'bool',
	'title'       => 'string, empty'
	));

$callback = isset($app->input->GET_raw['callback']) ? $app->input->GET_raw['callback'] : FALSE;

if ( !$app->permission->check('admin file access') )
{
	header('Location: ' . $controller->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

if ( $app->input->POST_valid['form-submit'] )
{
	if ( isset($_FILES['file']) )
	{
		$uploads = array();

		if ( !is_writable($controller->rootPath . 'uploads/files/') )
		{
			$app->error(FALSE, 'Directory "/uploads/files/" is not writable.', __FILE__, __LINE__);
		}

		for ( $i = 0; $i < count($_FILES['file']['name']); $i ++ )
		{
			switch ( $_FILES['file']['error'][$i] )
			{
				case UPLOAD_ERR_OK:
					$filename = sha1(file_get_contents($_FILES['file']['tmp_name'][$i]) . time());

					$r = @move_uploaded_file($_FILES['file']['tmp_name'][$i], $file = $controller->rootPath . 'uploads/files/' . $filename);

					if ( $r )
					{
						$width  = '';
						$height = '';

						if ( $image = @getimagesize($file) )
						{
							list($width, $height) = $image;

							$app->file->thumb($filename, $_FILES['file']['type'][$i], $width, $height);
						}

						$extension = strtolower(strrchr($_FILES['file']['name'][$i], '.'));

						$title = $app->input->POST_valid['title'][$i] ? $app->input->POST_html_safe['title'][$i] : $view->h(basename($_FILES['file']['name'][$i], $extension));

						$app->db->sql('
							INSERT INTO `' . $app->db->prefix . 'files` (
								`title`,
								`extension`,
								`image`,
								`filename`,
								`mime_type`,
								`width`,
								`height`,
								`size`,
								`date`,
								`date_edit`
								)
							VALUES (
								"' . $app->db->escape($title)                              . '",
								"' . $app->db->escape($extension)                          . '",
								 ' . ( !empty($image) ? 1 : 0 )                              . ',
								"' . $app->db->escape($filename)                           . '",
								"' . $app->db->escape($_FILES['file']['type'][$i])         . '",
								 ' . ( int ) $width                                          . ',
								 ' . ( int ) $height                                         . ',
								 ' . ( int ) $app->db->escape($_FILES['file']['size'][$i]) . ',
								"' . gmdate('Y-m-d H:i:s')                                   . '",
								"' . gmdate('Y-m-d H:i:s')                                   . '"
								)
							;');

						if ( $view->id = $app->db->result )
						{
							$uploads[] = '<a href="' . $view->route('file/' . $view->id . $extension) . '" onclick="callback(\'' . $view->route('file/' . $view->id . $extension) . '\');">' . $title . '</a>';
						}
					}
					else
					{
						$app->input->errors['file'][$i] = $view->t('Could not move file to destined location.');
					}

					break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$app->input->errors['file'][$i] = $view->t('The file is to big.');

					break;
				case UPLOAD_ERR_PARTIAL:
					$app->input->errors['file'][$i] = $view->t('Upload failed, try again.');

					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$app->input->errors['file'][$i] = $view->t('Upload failed, missing a temporary folder.');

					break;
				case UPLOAD_ERR_CANT_WRITE:
					$app->input->errors['file'][$i] = $view->t('Upload failed, could not write to disk.');

					break;
				case UPLOAD_ERR_EXTENSION:
					$app->input->errors['file'][$i] = $view->t('File upload stopped by extension.');

					break;
			}
		}

		if ( $uploads )
		{
			$view->notice = $view->t('The following files have been uploaded:%1$s', '<br/><br/>' . implode('<br/>', $uploads));
		}
	}
}
else if ( isset($app->input->GET_raw['notice']) )
{
	switch ( $app->input->GET_raw['notice'] )
	{
		case 'deleted':
			$view->notice = $view->t('The file has been deleted.');

			break;
	}
}

if ( ( int ) $view->id )
{
	switch ( $view->action )
	{
		case 'delete':
			if ( !$app->input->POST_valid['confirm'] )
			{
				$app->input->confirm($view->t('Are you sure you wish to delete this file?'));
			}
			else
			{
				// Delete file
				$app->db->sql('
					SELECT
						`filename`
					FROM `' . $app->db->prefix . 'files`
					WHERE
						`id` = ' . ( int ) $view->id . '
					LIMIT 1
					;');

				if ( $r = $app->db->result )
				{
					$filename = $r[0]['filename'];

					if ( is_file($file = $controller->rootPath . 'uploads/files/' . $filename) )
					{
						unlink($file);
					}

					if ( is_file($file = $controller->rootPath . 'uploads/thumbs/' . $filename) )
					{
						unlink($file);
					}

					$app->db->sql('
						DELETE
						FROM `' . $app->db->prefix . 'files`
						WHERE
							`id` = ' . ( int ) $view->id . '
						LIMIT 1
						;');

					if ( $app->db->result )
					{
						header('Location: ?callback=' . rawurlencode($callback) . '&notice=deleted');

						$app->end();
					}
				}
			}

			break;
	}
}

// Create a list of all files
$files = array();

$app->db->sql('
	SELECT
		*
	FROM `' . $app->db->prefix . 'files`
	ORDER BY `date` DESC
	;');

$files = $app->db->result;

$pagination = $view->paginate('files', count($files), 10);

$view->files           = array_splice($files, $pagination['from'], 10);
$view->filesPagination = $pagination;
$view->callback        = $view->h($callback);

$view->load('admin/files.html.php');

$app->end();
