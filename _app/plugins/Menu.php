<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Menu_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('node'),
		$hooks        = array('dashboard' => 3, 'init' => 5, 'install' => 1, 'menu' => 1, 'remove' => 1)
		;

	function install()
	{
		if ( !in_array($this->app->db->prefix . 'menu', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'menu` (
					`items` TEXT NULL
					) TYPE = INNODB
				;');

			$this->app->db->sql('
				INSERT INTO `' . $this->app->db->prefix . 'menu` (
					`items`
					)
				VALUES (
					""
					)
				;');
		}

		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->create('Menu', 'admin menu access', 'Manage menu items');
		}
	}

	function remove()
	{
		if ( in_array($this->app->db->prefix . 'menu', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE `' . $this->app->db->prefix . 'menu`;');
		}

		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->delete('admin menu access');
		}
	}

	function init()
	{
		if ( !empty($this->app->db->ready) )
		{
			/**
			 * Check if the menu table exists
			 */
			if ( in_array($this->app->db->prefix . 'menu', $this->app->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
	}

	function dashboard(&$params)
	{
		$params[] = array(
			'name'        => 'Menu',
			'description' => 'Add and remove menu items',
			'group'       => 'Content',
			'path'        => 'admin/menu',
			'permission'  => 'admin menu access'
			);
	}

	function menu(&$params)
	{
		if ( !empty($this->ready) )
		{
			$this->get_items($params);
		}
	}

	/* Get menu items
	 * @param array $params
	 */
	function get_items(&$params)
	{
		$this->app->db->sql('
			SELECT
				`items`
			FROM `' . $this->app->db->prefix . 'menu`
			LIMIT 1
			;');

		if ( $r = $this->app->db->result )
		{
			$items = @unserialize($r[0]['items']);

			if ( is_array($items) )
			{
				$nodeIds = array();
				$nodes   = array();

				foreach ( $items as $item )
				{
					if ( $item['node_id'] )
					{
						$nodeIds[] = ( int ) $item['node_id'];
					}
				}

				if ( $nodeIds )
				{
					$this->app->db->sql('
						SELECT
							`id`,
							`title`,
							`path`
						FROM `' . $this->app->db->prefix . 'nodes`
						WHERE
							`id` IN (' . implode(', ', $nodeIds) . ')
						LIMIT ' . count($nodeIds) .'
						;');

					if ( $r = $this->app->db->result )
					{
						foreach ( $r as $d )
						{
							$nodes[$d['id']] = array(
								'title' => $d['title'],
								'path'  => $d['path'] ? $d['path'] : 'node/' . $d['id']
								);
						}
					}
				}

				foreach ( $items as $item )
				{
					if ( ( in_array($item['node_id'], $nodeIds) && isset($nodes[$item['node_id']]) ) || !in_array($item['node_id'], $nodeIds) )
					{
						$path  = $item['path']  ? $item['path']  : ( !empty($nodes[$item['node_id']]['path'])  ? $nodes[$item['node_id']]['path']  : '' );
						$title = $item['title'] ? $item['title'] : ( !empty($nodes[$item['node_id']]['title']) ? $nodes[$item['node_id']]['title'] : $item['path'] );

						$params[$title] = $path;
					}
				}
			}
		}
	}
}
