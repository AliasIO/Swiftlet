<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Menu
 * @abstract
 */
class Menu_Controller extends Controller
{
	public
		$pageTitle    = 'Menu',
		$dependencies = array('db', 'input', 'menu', 'node', 'permission'),
		$inAdmin      = TRUE
		;

	function init()
	{
		$this->app->input->validate(array(
			'form-submit' => 'bool',
			'items'       => '/.*/',
			));

		if ( !$this->app->permission->check('admin menu access') )
		{
			header('Location: ' . $this->view->route('login?ref=' . $this->request, FALSE));

			$this->app->end();
		}

		if ( $this->app->input->POST_valid['form-submit'] )
		{
			if ( $this->app->input->errors )
			{
				$this->view->error = $this->view->t('Please correct the errors below.');
			}
			else
			{
				$items = array();

				foreach ( explode("\n", $this->app->input->POST_html_safe['items']) as $item )
				{
					if ( trim($item) !== '' )
					{
						$nodeId = '';
						$title  = '';
						$path   = trim($item);

						if ( strstr($item, '|') )
						{
							list($title, $path) = explode('|', $item);
						}

						if ( preg_match('/^node\/([0-9]+)$/', trim($path), $m) )
						{
							$nodeId = ( int ) $m[1];
							$path   = '';
						}

						if ( $path !== '' )
						{
							$this->app->db->sql('
								SELECT
									`id`
								FROM {nodes}
								WHERE
									`path` = "' . $this->app->db->escape($path) . '"
								LIMIT 1
								');

							if ( $r = $this->app->db->result )
							{
								$nodeId = $r[0]['id'];
								$path   = '';
							}
						}

						if ( $nodeId != Node_Plugin::ROOT_ID )
						{
							$items[] = array(
								'node_id' => ( int ) $nodeId,
								'title'   => trim($title),
								'path'    => trim($path)
								);
						}
					}
				}

				$this->app->db->sql('
					UPDATE {menu} SET
						`items` = "' . $this->app->db->escape(serialize($items)) . '"
					LIMIT 1
					');

				if ( $this->app->db->result )
				{
					header('Location: ?notice=success', FALSE);

					$this->app->end();
				}
			}
		}
		else if ( isset($this->app->input->GET_raw['notice']) )
		{
			switch ( $this->app->input->GET_raw['notice'] )
			{
				case 'success':
					$this->view->notice = $this->view->t('The changes have been saved.');

					break;
			}
		}

		$this->app->db->sql('
			SELECT
				`items`
			FROM {menu}
			LIMIT 1
			');

		if ( $r = $this->app->db->result )
		{
			if ( $items = @unserialize($r[0]['items']) )
			{
				$nodeIds = array();
				$paths   = array();

				foreach ( $items as $item )
				{
					if ( ( int ) $item['node_id'] )
					{
						$nodeIds[] = ( int ) $item['node_id'];
					}
				}

				if ( $nodeIds )
				{
					$this->app->db->sql('
						SELECT
							`id`,
							`path`
						FROM {nodes}
						WHERE
							`id` IN ( ' . implode(',', $nodeIds) . ' )
						LIMIT ' . count($nodeIds) . '
						');

					if ( $r = $this->app->db->result )
					{
						foreach ( $r as $d )
						{
							$paths[$d['id']] = $d['path'];
						}
					}
				}

				$v = '';

				foreach ( $items as $item )
				{
					if ( ( in_array($item['node_id'], $nodeIds) && isset($paths[$item['node_id']]) ) || !in_array($item['node_id'], $nodeIds) )
					{
						$path = !empty($paths[$item['node_id']]) ? $paths[$item['node_id']] : ( $item['path'] !== '' ? $item['path'] : 'node/' . $item['node_id'] );

						$v .= $item['title'] . ( $item['title'] ? '|' : '' ) . $path . "\n";
					}
				}

				$this->app->input->POST_html_safe['items'] = trim($v);
			}
		}

		$this->view->load('admin/menu.html.php');
	}
}
