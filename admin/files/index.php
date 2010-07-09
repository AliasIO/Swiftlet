<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Files',
	'inAdmin'   => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'form', 'perm'));

$model->form->validate(array(
	'form-submit' => 'bool',
	'title'       => 'string, empty'
	));

$id       = isset($model->GET_raw['id']) && ( int ) $model->GET_raw['id'] ? ( int ) $model->GET_raw['id'] : FALSE;
$action   = isset($model->GET_raw['action'])   ? $model->GET_raw['action']   : FALSE;
$callback = isset($model->GET_raw['callback']) ? $model->GET_raw['callback'] : FALSE;

if ( !$model->perm->check('admin file access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

if ( $model->POST_valid['form-submit'] )
{
	if ( isset($_FILES['file']) )
	{
		$uploads = array();

		if ( !is_writable($contr->rootPath . 'uploads/files/') )
		{
			$model->error(FALSE, 'Directory "/uploads/files/" is not writable.', __FILE__, __LINE__);
		}

		for ( $i = 0; $i < count($_FILES['file']['name']); $i ++ )
		{
			switch ( $_FILES['file']['error'][$i] )
			{
				case UPLOAD_ERR_OK:
					$filename = sha1(file_get_contents($_FILES['file']['tmp_name'][$i]) . time());

					$r = @move_uploaded_file($_FILES['file']['tmp_name'][$i], $file = $contr->rootPath . 'uploads/files/' . $filename);

					if ( $r )
					{
						$width  = '';
						$height = '';

						if ( $image = @getimagesize($file) )
						{
							list($width, $height) = $image;

							$model->file->thumb($filename, $_FILES['file']['type'][$i], $width, $height);
						}

						$extension = strtolower(strrchr($_FILES['file']['name'][$i], '.'));

						$title = $model->POST_valid['title'][$i] ? $model->POST_html_safe['title'][$i] : $model->h(basename($_FILES['file']['name'][$i], $extension));

						$model->db->sql('
							INSERT INTO `' . $model->db->prefix . 'files` (
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
								"' . $model->db->escape($title)                              . '",
								"' . $model->db->escape($extension)                          . '",
								 ' . ( !empty($image) ? 1 : 0 )                              . ',
								"' . $model->db->escape($filename)                           . '",
								"' . $model->db->escape($_FILES['file']['type'][$i])         . '",
								 ' . ( int ) $width                                          . ',
								 ' . ( int ) $height                                         . ',
								 ' . ( int ) $model->db->escape($_FILES['file']['size'][$i]) . ',
								"' . gmdate('Y-m-d H:i:s')                                   . '",
								"' . gmdate('Y-m-d H:i:s')                                   . '"
								)
							;');

						if ( $id = $model->db->result )
						{
							$uploads[] = '<a href="' . $model->route('file/' . $id . $extension) . '" onclick="callback(\'' . $model->route('file/' . $id . $extension) . '\');">' . $title . '</a>';
						}
					}
					else
					{
						$model->form->errors['file'][$i] = $model->t('Could not move file to destined location.');
					}

					break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$model->form->errors['file'][$i] = $model->t('The file is to big.');

					break;
				case UPLOAD_ERR_PARTIAL:
					$model->form->errors['file'][$i] = $model->t('Upload failed, try again.');

					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$model->form->errors['file'][$i] = $model->t('Upload failed, missing a temporary folder.');

					break;
				case UPLOAD_ERR_CANT_WRITE:
					$model->form->errors['file'][$i] = $model->t('Upload failed, could not write to disk.');

					break;
				case UPLOAD_ERR_EXTENSION:
					$model->form->errors['file'][$i] = $model->t('File upload stopped by extension.');

					break;
			}
		}

		if ( $uploads )
		{
			$view->notice = $model->t('The following files have been uploaded:%1$s', '<br/><br/>' . implode('<br/>', $uploads));
		}
	}
}
else if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'deleted':
			$view->notice = $model->t('The file has been deleted.');

			break;
	}
}

if ( ( int ) $id )
{
	switch ( $action )
	{
		case 'delete':
			if ( !$model->POST_valid['confirm'] )
			{
				$model->confirm($model->t('Are you sure you wish to delete this file?'));
			}
			else
			{
				// Delete file
				$model->db->sql('
					SELECT
						`filename`
					FROM `' . $model->db->prefix . 'files`
					WHERE
						`id` = ' . ( int ) $id . '
					LIMIT 1
					;');
				
				if ( $r = $model->db->result )
				{
					$filename = $r[0]['filename'];

					if ( is_file($file = $contr->rootPath . 'uploads/files/' . $filename) )
					{
						unlink($file);
					}

					if ( is_file($file = $contr->rootPath . 'uploads/thumbs/' . $filename) )
					{
						unlink($file);
					}

					$model->db->sql('
						DELETE
						FROM `' . $model->db->prefix . 'files`
						WHERE
							`id` = ' . ( int ) $id . '
						LIMIT 1
						;');

					if ( $model->db->result )
					{
						header('Location: ?callback=' . rawurlencode($callback) . '&notice=deleted');

						$model->end();
					}
				}
			}

			break;
	}
}

// Create a list of all files
$files = array();

$model->db->sql('
	SELECT
		*
	FROM `' . $model->db->prefix . 'files`
	ORDER BY `date` DESC
	;');

$files = $model->db->result;

$pagination = $model->paginate('files', count($files), 10);

$view->files           = array_splice($files, $pagination['from'], 10);
$view->filesPagination = $pagination;
$view->callback        = $model->h($callback);
$view->id              = $id;
$view->action          = $action;

$view->load('admin/files.html.php');

$model->end();
