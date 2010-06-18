<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->model) ) die('Direct access to this file is not allowed');

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
		$model,
		$view,
		$contr
		;

	/**
	 * Initialize nodes
	 * @param object $this->model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->view  = $model->view;
		$this->contr = $model->contr;

		if ( !empty($model->db->ready) )
		{
			/**
			 * Check if the nodes table exists
			 */
			if ( in_array($model->db->prefix . 'nodes', $model->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
	}

	/**
	 * Create a URL friendly title
	 * @param string $title
	 * @return string
	 */
	function permalink($title, $id = FALSE)
	{
		$permalink = trim(preg_replace('/__+/', '_', preg_replace('/[^a-z0-9_-]/', '_', strtolower($title))), '_');

		$i = 0;

		while ( TRUE )
		{
			$this->model->db->sql('
				SELECT
					`permalink`
				FROM `' . $this->model->db->prefix . 'nodes`
				WHERE
					`permalink` = "' . $permalink . ( $i ? '_' . $i : '' ) . '"' . ( $id ? ' AND `id` != ' . $id : '' ) . '
				LIMIT 1
				;');

			if ( !$this->model->db->result ) return $permalink . ( $i ? '_' . $i : '' );

			$i ++;
		}
	}

	/**
	 * Create a node
	 * @param string $title
	 * @param int $parentId
	 * @param bool $move
	 * @return int
	 */
	function create($title, $permalink, $parentId)
	{
		$this->model->db->sql('
			SELECT
				`left_id`,
				`right_id`
			FROM `' . $this->model->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $parentId . '
			LIMIT 1
			;');
		
		if ( $this->model->db->result )
		{
			$parentNode = $this->model->db->result[0];
			
			$this->model->db->sql('
				UPDATE `' . $this->model->db->prefix . 'nodes` SET
					`left_id`   = `left_id` + 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`left_id` > ' . ( int ) $parentNode['left_id'] . '
				;');

			$this->model->db->sql('
				UPDATE `' . $this->model->db->prefix . 'nodes` SET
					`right_id`  = `right_id` + 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`right_id` > ' . ( int ) $parentNode['left_id'] . '
				;');

			$this->model->db->sql('
				INSERT INTO `' . $this->model->db->prefix . 'nodes` (
					`left_id`,
					`right_id`,
					`title`,
					`permalink`,
					`date`,
					`date_edit`
					)
				VALUES (
					' . ( ( int ) $parentNode['left_id'] + 1 ) . ',
					' . ( ( int ) $parentNode['left_id'] + 2 ) . ',
					"' . $title . '",
					"' . $permalink . '",
					"' . gmdate('Y-m-d H:i:s') . '",
					"' . gmdate('Y-m-d H:i:s') . '"
					)
				;');

			return $this->model->db->result;
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
		if ( $node['left_id'] <= $parentNode['left_id'] && $node['right_id'] >= $parentNode['right_id'] )
		{
			return;
		}

		$diff = count($node['all']) * 2;

		// Sync parents
		$this->model->db->sql('
			UPDATE `' . $this->model->db->prefix . 'nodes` SET
				`right_id` = `right_id` - ' . $diff . '
			WHERE
				`left_id`  < ' . ( int ) $node['right_id'] . ' AND
				`right_id` > ' . ( int ) $node['right_id'] . '
			;');

		// Sync righthand side of tree
		$this->model->db->sql('
			UPDATE `' . $this->model->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  - ' . $diff . ',
				`right_id` = `right_id` - ' . $diff . '
			WHERE
				`left_id` > ' . $node['right_id'] . '
			;');

		$parentNode = $this->get($parentId);

		// Sync new parents
		$this->model->db->sql('
			UPDATE `' . $this->model->db->prefix . 'nodes` SET
				`right_id` = `right_id` + ' . $diff . '
			WHERE
				' . $parentNode['right_id'] . ' BETWEEN `left_id` AND `right_id` AND
				`id` NOT IN ( ' . implode(', ', $node['all']) . ' )
			;');

		// Sync righthand side of tree
		$this->model->db->sql('
			UPDATE `' . $this->model->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  + ' . $diff . ',
				`right_id` = `right_id` + ' . $diff . '
			WHERE
				`left_id` > ' . $parentNode['right_id'] . ' AND
				`id` NOT IN ( ' . implode(', ', $node['all']) . ' )
			;');

		// Sync moved branch
		$parentNode['right_id'] += $diff;
		
		if ( $parentNode['right_id'] > $node['right_id'] )
		{
			$diff = '+ ' . ( $parentNode['right_id'] - $node['right_id'] - 1 );
		}
		else
		{
			$diff = '- ' . abs($parentNode['right_id'] - $node['right_id'] - 1);
		}

		$this->model->db->sql('
			UPDATE `' . $this->model->db->prefix . 'nodes` SET
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
		$this->model = $this->model;

		$this->model->db->sql('
			SELECT
				`left_id`,
				`right_id`
			FROM `' . $this->model->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( $this->model->db->result )
		{
			$leftId  = $this->model->db->result[0]['left_id'];
			$rightId = $this->model->db->result[0]['right_id'];

			$this->model->db->sql('
				UPDATE `' . $this->model->db->prefix . 'nodes` SET
					`left_id`   = `left_id` - 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`left_id`  > ' . $leftId  . ' AND
					`right_id` > ' . $rightId . '
				;');

			$this->model->db->sql('
				UPDATE `' . $this->model->db->prefix . 'nodes` SET
					`right_id`  = `right_id` - 2,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`right_id` > ' . $rightId . '
				;');

			$this->model->db->sql('
				UPDATE `' . $this->model->db->prefix . 'nodes` SET
					`left_id`   = `left_id`  - 1,
					`right_id`  = `right_id` - 1,
					`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`left_id`  > ' . $leftId  . ' AND
					`right_id` < ' . $rightId . '
				;');

			$this->model->db->sql('
				DELETE
				FROM `' . $this->model->db->prefix . 'nodes`
				WHERE
					`id` = ' . ( int ) $id . '
				LIMIT 1
				;');

			return $this->model->db->result;
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

		$this->model->db->sql('
			SELECT
				*
			FROM `' . $this->model->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( isset($this->model->db->result[0]) )
		{
			$node = $this->model->db->result[0];
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
			
			$this->model->db->sql('
				SELECT
					*
				FROM `' . $this->model->db->prefix . 'nodes`
				WHERE
					`left_id`  < ' . ( int ) $node['left_id']  . ' AND
					`right_id` > ' . ( int ) $node['right_id'] . '
				ORDER BY `left_id` ASC
				;');

			if ( $this->model->db->result )
			{
				foreach ( $this->model->db->result as $d )
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
	function get_children($id)
	{
		if ( $node = $this->get($id) )
		{
			$node['children'] = array();

			$this->model->db->sql('
				SELECT
					*
				FROM `' . $this->model->db->prefix . 'nodes`
				WHERE
					`left_id` BETWEEN ' . ( int ) $node['left_id']  . ' AND ' . ( int ) $node['right_id'] . '
				ORDER BY `left_id` ASC
				;');

			if ( $this->model->db->result )
			{
				$nodes    = array();
				$children = array();

				foreach ( $this->model->db->result as $d )
				{
					$children[] = $d['id'];
				}

				foreach ( $this->model->db->result as $d )
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

			if ( count($stack) > 0 )
			{
				$node['level'] = count($stack);
				
				$stack[count($stack) - 1]['children'][] = &$node;
			}

			$stack[] = &$node;
		}

		return $stack[0];
	}

	/**
	 * Turn an array of nodes into a flat list
	 * @param array $nodes
	 * @param array $list
	 */
	function nodes_to_array($nodes, &$list)
	{
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
