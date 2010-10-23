<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Upload
 * @abstract
 */
class Upload_Controller extends Controller
{
	public
		$pageTitle    = 'Uploads',
		$dependencies = array('db', 'upload', 'input', 'permission'),
		$inAdmin      = TRUE
		;

	function init()
	{
		if ( !$this->app->permission->check('admin upload access') )
		{
			header('Location: ' . $this->view->route('login?ref=' . $this->request));

			$this->app->end();
		}

		$this->app->input->validate(array(
			'form-submit' => 'bool',
			'title'       => 'string, empty'
			));

		$callback = isset($this->app->input->GET_raw['callback']) ? $this->app->input->GET_raw['callback'] : FALSE;

		if ( $this->app->input->POST_valid['form-submit'] )
		{
			if ( isset($_FILES['file']) )
			{
				$uploads = array();

				if ( !is_writable('uploads/files/') )
				{
					$this->app->error(FALSE, 'Directory "/uploads/files/" is not writable.', __FILE__, __LINE__);
				}

				for ( $i = 0; $i < count($_FILES['file']['name']); $i ++ )
				{
					switch ( $_FILES['file']['error'][$i] )
					{
						case UPLOAD_ERR_OK:
							$filename = sha1(uniqid(mt_rand(), TRUE));

							$r = @move_uploaded_file($_FILES['file']['tmp_name'][$i], $file = $this->rootPath . 'uploads/files/' . $filename);

							if ( $r )
							{
								$width  = '';
								$height = '';

								if ( $image = @getimagesize($file) )
								{
									list($width, $height) = $image;

									$this->app->upload->thumb($filename, $_FILES['file']['type'][$i], $width, $height);
								}

								$extension = strtolower(strrchr($_FILES['file']['name'][$i], '.'));

								$title = $this->app->input->POST_valid['title'][$i] ? $this->app->input->POST_html_safe['title'][$i] : $this->view->h(basename($_FILES['file']['name'][$i], $extension));

								$this->app->db->sql('
									INSERT INTO `' . $this->app->db->prefix . 'uploads` (
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
										"' . $this->app->db->escape($title)                              . '",
										"' . $this->app->db->escape($extension)                          . '",
										 ' . ( !empty($image) ? 1 : 0 )                                  . ',
										"' . $this->app->db->escape($filename)                           . '",
										"' . $this->app->db->escape($_FILES['file']['type'][$i])         . '",
										 ' . ( int ) $width                                              . ',
										 ' . ( int ) $height                                             . ',
										 ' . ( int ) $this->app->db->escape($_FILES['file']['size'][$i]) . ',
										"' . gmdate('Y-m-d H:i:s')                                       . '",
										"' . gmdate('Y-m-d H:i:s')                                       . '"
										)
									;');

								if ( $this->view->id = $this->app->db->result )
								{
									$uploads[] = '<a href="' . $this->view->route('file/' . $this->view->id . $extension) . '" onclick="callback(\'' . $this->view->route('file/' . $this->view->id . $extension) . '\');">' . $title . '</a>';
								}
							}
							else
							{
								$this->app->input->errors['file'][$i] = $this->view->t('Could not move file to destined location.');
							}

							break;
						case UPLOAD_ERR_INI_SIZE:
						case UPLOAD_ERR_FORM_SIZE:
							$this->app->input->errors['file'][$i] = $this->view->t('The file is to big.');

							break;
						case UPLOAD_ERR_PARTIAL:
							$this->app->input->errors['file'][$i] = $this->view->t('Upload failed, try again.');

							break;
						case UPLOAD_ERR_NO_TMP_DIR:
							$this->app->input->errors['file'][$i] = $this->view->t('Upload failed, missing a temporary folder.');

							break;
						case UPLOAD_ERR_CANT_WRITE:
							$this->app->input->errors['file'][$i] = $this->view->t('Upload failed, could not write to disk.');

							break;
						case UPLOAD_ERR_EXTENSION:
							$this->app->input->errors['file'][$i] = $this->view->t('File upload stopped by extension.');

							break;
					}
				}

				if ( $uploads )
				{
					$this->view->notice = $this->view->t('The following files have been uploaded:%1$s', '<br/><br/>' . implode('<br/>', $uploads));
				}
			}
		}
		else if ( isset($this->app->input->GET_raw['notice']) )
		{
			switch ( $this->app->input->GET_raw['notice'] )
			{
				case 'deleted':
					$this->view->notice = $this->view->t('The file has been deleted.');

					break;
			}
		}

		switch ( $this->method )
		{
			case 'delete':
				if ( !$this->app->input->POST_valid['confirm'] )
				{
					$this->app->input->confirm($this->view->t('Are you sure you wish to delete this file?'));
				}
				else
				{
					$this->app->db->sql('
						SELECT
							`filename`
						FROM `' . $this->app->db->prefix . 'uploads`
						WHERE
							`id` = ' . ( int ) $this->id . '
						LIMIT 1
						;');

					if ( $r = $this->app->db->result )
					{
						$filename = $r[0]['filename'];

						if ( is_file($file = 'uploads/files/' . $filename) )
						{
							unlink($file);
						}

						if ( is_file($file = 'uploads/thumbs/' . $filename) )
						{
							unlink($file);
						}

						$this->app->db->sql('
							DELETE
							FROM `' . $this->app->db->prefix . 'uploads`
							WHERE
								`id` = ' . ( int ) $this->id . '
							LIMIT 1
							;');

						if ( $this->app->db->result )
						{
							header('Location: ' . $this->app->view->route($this->path . '?callback=' . rawurlencode($callback) . '&notice=deleted'));

							$this->app->end();
						}
					}
				}

				break;
		}

		// Create a list of all files
		$files = array();

		$this->app->db->sql('
			SELECT
				*
			FROM `' . $this->app->db->prefix . 'uploads`
			ORDER BY `date` DESC
			;');

		$files = $this->app->db->result;

		$pagination = $this->view->paginate('files', count($files), 10);

		$this->view->files           = array_splice($files, $pagination['from'], 10);
		$this->view->filesPagination = $pagination;
		$this->view->callback        = $this->view->h($callback);

		$this->view->load('admin/upload.html.php');
	}
}
