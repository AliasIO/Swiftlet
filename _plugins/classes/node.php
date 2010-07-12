<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->app) ) die('Direct access to this file is not allowed');

/**
 * Nodes
 * @abstract
 */
class node
{
	public
		$ready
		;

	const
		rootId = 1
		;

	private
		$paths = array(),

		$app,
		$view,
		$contr
		;

	/**
	 * Initialize nodes
	 * @param object $this->app
	 */
	function __construct($app)
	{
		$this->app  = $app;
		$this->view  = $app->view;
		$this->contr = $app->contr;

		if ( !empty($app->db->ready) )
		{
			/**
			 * Check if the nodes table exists
			 */
			if ( in_array($app->db->prefix . 'nodes', $app->db->tables) )
			{
				$app->db->sql('
					SELECT
						`id`,
						`path`
					FROM `' . $app->db->prefix . 'nodes`
					WHERE
						`path` != ""
					;');

				if ( $r = $app->db->result )
				{
					foreach ( $r as $d )
					{
						$this->paths[$d['id']] = $d['path'];
					}
				}

				$this->ready = TRUE;
			}
		}
	}

	/**
	 * Get the path for a route
	 * @param array $params
	 * @return string
	 */
	function route($params)
	{
		$nodeId = array_search(implode('/', $params['parts']), $this->paths);

		if ( !$nodeId && count($params['parts']) == 2 && $params['parts'][0] == 'node' && ( int ) $params['parts'][1] )
		{
			$nodeId = ( int ) $params['parts'][1];
		}

		if ( $nodeId )
		{
			$this->app->db->sql('
				SELECT
					`type`
				FROM `' . $this->app->db->prefix . 'nodes`
				WHERE
					`id` = ' . ( int ) $nodeId . '
				LIMIT 1
				;');

			if ( $r = $this->app->db->result )
			{
				$params = array(
					'type' => $r['0']['type'],
					'path' => ''
					);

				$this->app->hook('display_node', $params);

				if ( $params['path'] )
				{
					return array(
						'parts' => array('node', $nodeId),
						'path'  => $params['path']
						);
				}
			}
		}

		return $params;
	}

	/**
	 * Create a node
	 * @param string $title
	 * @param int $parentId
	 * @param bool $move
	 * @return int
	 */
	function create($title, $type, $parentId)
	{
		if ( !$parentId )
		{
			return FALSE;
		}

		$this->app->db->sql('
			SELECT
				`left_id`,
				`right_id`
			FROM `' . $this->app->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $parentId . '
			LIMIT 1
			;');
		
		if ( $this->app->db->result )
		{
			$parentNode = $this->app->db->result[0];
			
			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'nodes` SET
					`left_id`   = `left_id` + 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`left_id` > ' . ( int ) $parentNode['left_id'] . '
				;');

			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'nodes` SET
					`right_id`  = `right_id` + 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`right_id` > ' . ( int ) $parentNode['left_id'] . '
				;');

			$this->app->db->sql('
				INSERT INTO `' . $this->app->db->prefix . 'nodes` (
					`left_id`,
					`right_id`,
					`type`,
					`title`,
					`date`,
					`date_edit`
					)
				VALUES (
					 ' . ( ( int ) $parentNode['left_id'] + 1 )            . ',
					 ' . ( ( int ) $parentNode['left_id'] + 2 )            . ',
					"' . $this->app->db->escape($type)                   . '",
					"' . $this->app->db->escape($this->app->h($title)) . '",
					"' . gmdate('Y-m-d H:i:s')                             . '",
					"' . gmdate('Y-m-d H:i:s')                             . '"
					)
				;');

			return $this->app->db->result;
		}

		return FALSE;
	}

	/**
	 * Move a branch
	 * @param int $id
	 * @param int $parentId
	 * @return bool
	 */
	function move($id, $parentId)
	{
		// Root node can not be moved
		if ( $id == node::rootId )
		{
			return;
		}

		$node       = $this->get_children($id);
		$parentNode = $this->get($parentId);

		// Node can not be moved to a decendant of its own
		if ( $node[0]['left_id'] <= $parentNode['left_id'] && $node[0]['right_id'] >= $parentNode['right_id'] )
		{
			return;
		}

		$diff = count($node['all']) * 2;

		// Sync parents
		$this->app->db->sql('
			UPDATE `' . $this->app->db->prefix . 'nodes` SET
				`right_id` = `right_id` - ' . $diff . '
			WHERE
				`left_id`  < ' . ( int ) $node[0]['right_id'] . ' AND
				`right_id` > ' . ( int ) $node[0]['right_id'] . '
			;');

		// Sync righthand side of tree
		$this->app->db->sql('
			UPDATE `' . $this->app->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  - ' . ( int ) $diff . ',
				`right_id` = `right_id` - ' . ( int ) $diff . '
			WHERE
				`left_id` > ' . ( int ) $node[0]['right_id'] . '
			;');

		$parentNode = $this->get($parentId);

		// Sync new parents
		$this->app->db->sql('
			UPDATE `' . $this->app->db->prefix . 'nodes` SET
				`right_id` = `right_id` + ' . $diff . '
			WHERE
				' . $parentNode['right_id'] . ' BETWEEN `left_id` AND `right_id` AND
				`id` NOT IN ( ' . implode(', ', $node['all']) . ' )
			;');

		// Sync righthand side of tree
		$this->app->db->sql('
			UPDATE `' . $this->app->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  + ' . ( int ) $diff . ',
				`right_id` = `right_id` + ' . ( int ) $diff . '
			WHERE
				`left_id` > ' . ( int ) $parentNode['right_id'] . ' AND
				`id` NOT IN ( ' . implode(', ', $node['all']) . ' )
			;');

		// Sync moved branch
		$parentNode['right_id'] += $diff;
		
		if ( $parentNode['right_id'] > $node[0]['right_id'] )
		{
			$diff = '+ ' . ( ( int ) $parentNode['right_id'] - ( int ) $node[0]['right_id'] - 1 );
		}
		else
		{
			$diff = '- ' . abs(( int ) $parentNode['right_id'] - ( int ) $node[0]['right_id'] - 1);
		}

		$this->app->db->sql('
			UPDATE `' . $this->app->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  ' . $diff . ',
				`right_id` = `right_id` ' . $diff . '
			WHERE
				`id` IN ( ' . implode(', ', $node['all']) . ' )
			;');
	}

	/**
	 * Delete a node
	 * @param int $parentId
	 * @param bool $soft
	 * @return int
	 */
	function delete($id)
	{
		$this->app = $this->app;

		$this->app->db->sql('
			SELECT
				`left_id`,
				`right_id`
			FROM `' . $this->app->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( $this->app->db->result )
		{
			$leftId  = ( int ) $this->app->db->result[0]['left_id'];
			$rightId = ( int ) $this->app->db->result[0]['right_id'];

			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'nodes` SET
					`left_id`   = `left_id` - 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`left_id`  > ' . ( int ) $leftId  . ' AND
					`right_id` > ' . ( int ) $rightId . '
				;');

			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'nodes` SET
					`right_id`  = `right_id` - 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`right_id` > ' . ( int ) $rightId . '
				;');

			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'nodes` SET
					`left_id`   = `left_id`  - 1,
					`right_id`  = `right_id` - 1,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`left_id`  > ' . ( int ) $leftId  . ' AND
					`right_id` < ' . ( int ) $rightId . '
				;');

			$this->app->db->sql('
				DELETE
				FROM `' . $this->app->db->prefix . 'nodes`
				WHERE
					`id` = ' . ( int ) $id . '
				LIMIT 1
				;');

			return $this->app->db->result;
		}
	}

	/**
	 * Get a node
	 * @param int $id
	 * @return array
	 */
	function get($id)
	{
		$node = array();	

		$this->app->db->sql('
			SELECT
				*
			FROM `' . $this->app->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( isset($this->app->db->result[0]) )
		{
			$node = $this->app->db->result[0];
		}

		return $node;
	}

	/**
	 * Get a node and its parents
	 * @param int $id
	 * @return array
	 */
	function get_parents($id)
	{
		$node = array();	

		if ( $node = $this->get($id) )
		{
			$node['parents'] = array();
			
			$this->app->db->sql('
				SELECT
					*
				FROM `' . $this->app->db->prefix . 'nodes`
				WHERE
					`left_id`  < ' . ( int ) $node['left_id']  . ' AND
					`right_id` > ' . ( int ) $node['right_id'] . '
				ORDER BY `left_id` ASC
				;');

			if ( $this->app->db->result )
			{
				foreach ( $this->app->db->result as $d )
				{
					$node['parents'][] = $d;
				}
			}
		}

		return $node;
	}
	
	/**
	 * Get a node and its children
	 * @param int $id
	 * @return array
	 */
	function get_children($id, $type = '')
	{
		if ( $node = $this->get($id) )
		{
			$node['children'] = array();

			$this->app->db->sql('
				SELECT
					*
				FROM `' . $this->app->db->prefix . 'nodes`
				WHERE
					`id` = ' . ( int ) $id . ' OR (
						`left_id` BETWEEN ' . ( int ) $node['left_id']  . ' AND ' . ( int ) $node['right_id'] . '
						' . ( $type ? 'AND `type` = "' . $this->app->db->escape($type) . '"' : '' ) . '
						)
				ORDER BY `left_id` ASC
				;');

			if ( $this->app->db->result )
			{
				$nodes    = array();
				$children = array();

				foreach ( $this->app->db->result as $d )
				{
					$children[] = ( int ) $d['id'];
				}

				foreach ( $this->app->db->result as $d )
				{
					$nodes[] = $d;
				}
				
				$nodes = $this->tree($nodes);

				$nodes['all'] = $children;

				return $nodes;
			}
		}
	}

	/**
	 * Create a structured array of nodes
	 * @param array $nodes
	 * @return array
	 */
	function tree($nodes)
	{
		$stack = array();

		for ( $i = 0; $i < count($nodes); $i ++ )
		{
			$node = &$nodes[$i];
			
			while ( count($stack) > 0 && $stack[count($stack) - 1]['right_id'] < $node['right_id'] )
			{
				array_pop($stack);
			}

			$node['level'] = count($stack);
				
			if ( count($stack) > 0 )
			{
				$stack[count($stack) - 1]['children'][] = &$node;
			}

			$stack[] = &$node;
		}

		return array($stack[0]);
	}

	/**
	 * Turn an array of nodes into a flat list
	 * @param array $nodes
	 * @param array $list
	 */
	function nodes_to_array($nodes, &$list)
	{
		unset($nodes['all']);
		
		if ( $nodes )
		{
			foreach ( $nodes as $node )
			{
				$list[] = $node;

				if ( !empty($node['children']) )
				{
					$this->nodes_to_array($node['children'], $list);
				}
			}
		}
	}
}
