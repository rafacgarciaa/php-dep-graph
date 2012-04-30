<?php
/**
 * DepGraph - Simple dependency graph management in PHP.
 *
 * @author      Rafael García <rafaelgarcia@profesionaldiacronos.com>
 * @copyright   2011 Rafael García
 * @link        http://depgraph.profesionaldiacronos.com
 * @license     http://depgraph.profesionaldiacronos.com/license
 * @version     1.0.0
 * @package     DepGraph
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace diacronos\DepGraph;

/**
 * DepGraph
 *
 * Say you have a set of resources that depend on each other in some way.
 * These resources can be anything—files, chains of command, plot twists on *Lost*—whatever.
 * All that matters is that each one has a unique string identifier, and a list of direct dependencies.
 *
 * DepGraph makes it easy to compute "chains" of dependencies, with guaranteed logical ordering and no duplicates.
 * That's trivial in most cases, but if A depends on B and B depends on A, a naïve dependency graph would get trapped in an infinite loop.
 * DepGraph throws an error if any such "cycles" are detected.
 * 
 * The class provides this interface:
 *
 * add( string $id, mixed $object )
 * getChain( string $id )
 * 
 * DepGraph is a PHP port from the excelent library dep-graph {@link http://github.com/TrevorBurnham/dep-graph},
 * written in CoffeeScript for Node.JS, by Trevor Burnham {https://github.com/TrevorBurnham}. All credits for him. 
 * 
 * @package DepGraph
 * @author  Rafael García
 * @since   1.0.0
 */
class DepGraph
{
	/**
	 * @var array
	 */
	private $_map;
	
	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct()
	{
		// The internal representation of the dependency graph in the format
    	// `id: [ids]`, indicating only *direct* dependencies.
		$this->_map = array();
	}
	
	/**
	 * Add a direct dependency. Returns `false` if that dependency is a duplicate.
	 * @param   string   $id		dependency identifier
	 * @param   mixed    $object
	 * @return  bool
	 */
 	public function add($id, $object)
 	{
 		if (isset($this->_map[$id]) == 0) {
 			$this->_map[$id] = array();
 		}
 		
		if (array_search($object, $this->_map[$id]) !== false) {
			return false;
		}
		
		array_push($this->_map[$id], $object);
		return true;
	}
	
	/**
	 * Generate a list of all dependencies (direct and indirect) for the given id,
	 * in logical order with no duplicates.
	 * @param   string   $id	dependency identifier
	 * @return  array
	 * @throws \Exception if a cyclic dependecy is detected
	 */
	public function getChain($id, array $traversedPaths = array(), array $traversedBranch = array())
	{
		array_unshift($traversedPaths, $id);
		array_unshift($traversedBranch, $id);
		
		if (isset($this->_map[$id]) == 0) {
			return array();
		}

		$_ref1 = array_reverse(array_slice($this->_map[$id], 0));
		foreach ($_ref1 as $depId) {
			if (array_search($depId, $traversedBranch) !== false) { # cycle
				throw new \Exception("Cyclic dependency from {$id} to {$depId}");
			}
			
			$depIdIndex = array_search($depId, $traversedPaths);
			if ($depIdIndex !== false) { # duplicate, push to front
				array_splice($traversedPaths, $depIdIndex, 1);
				array_unshift($traversedPaths, $depId);
				continue;
			}
			 
			$this->getChain($depId, &$traversedPaths, $traversedBranch);
		}
		
		return array_slice($traversedPaths, 0, -1);
	}	
}