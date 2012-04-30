# DepGraph

Simple dependency graph management in PHP.

## What's it for?

Say you have a set of resources that depend on each other in some way. These resources can be anything—files, chains of command, plot twists on *Lost*—whatever. All that matters is that each one has a unique string identifier, and a list of direct dependencies.

`DepGraph` makes it easy to compute "chains" of dependencies, with guaranteed logical ordering and no duplicates. That's trivial in most cases, but if `A` depends on `B` and `B` depends on `A`, a naïve dependency graph would get trapped in an infinite loop. `DepGraph` throws an error if any such "cycles" are detected.

`DepGraph` is a PHP port from the excelent library [dep-graph] (link http://github.com/TrevorBurnham/dep-graph), written in CoffeeScript for Node.JS by [Trevor Burnham] (https://github.com/TrevorBurnham). All credits for him.

## API

    /** @see \diacronos\DepGraph\DepGraph */
    require_once '../libs/diacronos/DepGraph/DepGraph.php';
    use \diacronos\DepGraph\DepGraph;

    $deps = new DepGraph();
    $deps->add('A', 'B');  // => A requires B
    $deps->add('B', 'C');  // => B requires C
    $deps->getChain('A');  // => array('C', 'B', 'A')
