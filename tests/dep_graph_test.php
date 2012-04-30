<?php

/** @see \diacronos\DepGraph\DepGraph */
require_once '../libs/diacronos/DepGraph/DepGraph.php';
use \diacronos\DepGraph\DepGraph;

require_once('../libs/simpletest/autorun.php');

/**
 * Tests for \DepGraph\DepGraph class
 *
 * @package DepGraph
 * @author  Rafael García
 * @since   1.0.0
 */
class TestOfDepGraph extends UnitTestCase
{
	/**
	 * @var \DepGraph\DepGraph
	 */
	private $dp;
	
	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dp = new DepGraph();
	}
	
	public function testDirectDependenciesAreChainedInOriginalOrder()
	{
		$this->dp->add('0', '1');
		$this->dp->add('0', '2');
		$this->dp->add('0', '3');
		
		$this->assertEqual(
			json_encode($this->dp->getChain('0')),
			'["1","2","3"]',
			'Direct dependencies are chained in original order'
		);
	}
	
	public function testIndirectDependenciesAreChainedBeforeTheirDependents()
	{
		$this->dp->add('2', 'A');
		$this->dp->add('2', 'B');
				
		$this->assertEqual(
			json_encode($this->dp->getChain('0')),
			'["1","A","B","2","3"]',
			'Indirect dependencies are chained before their dependents'
		);
	}
	
	public function testGetChainCanSafelyBeCalledForUnknownResources()
	{
		$this->assertEqual(
			json_encode($this->dp->getChain('Z')),
			'[]',
			'getChain can safely be called for unknown resources'
		);
	}
	
	public function testCyclicDependenciesAreDetected()
	{
		$this->expectException('Exception', 'Cyclic dependencies are detected');
		
		$this->dp->add('yin', 'yang');
		$this->dp->add('yang', 'yin');
		
		$this->dp->getChain('yin');
		$this->dp->getChain('yang');
	}
	
	public function testArcDirectionIsTakenIntoAccount()
	{
		$this->dp->add("MAIN", "One");
		$this->dp->add("MAIN", "Three");
		$this->dp->add("One", "Two");
		$this->dp->add("Two", "Three");
		
		$this->assertEqual(
			json_encode($this->dp->getChain('MAIN')),
			'["Three","Two","One"]',
			'Arc direction is taken into account'
		);
	}
}