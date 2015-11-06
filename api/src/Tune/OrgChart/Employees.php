<?php
namespace Tune\OrgChart;

use Tune\Repository;
use Tune\Repository\EmployeesInterface;
use Tune\Repository\Factory;

class Employees {
    /** @var $repository EmployeesInterface */
    protected $repository;
    protected $tree = [];
    protected $assoc = [];
    protected $output = [];

    public function __construct(EmployeesInterface $repository = null) {
        if (is_null($repository)) {
            $this->init();
        }
    }

    public function init() {
        $factory = new Factory('\Tune\Repository\Provider::pdo');
        $this->repository = $factory->create('\Tune\Repository\PDO\Employees');
    }

    /**
     * Delivers stored and derived employee data
     *
     * @return array
     */
    public function get() {
        /** @var \Tune\Repository\PDO\Employees $resource */
        $resource = $this->repository->getEmployees();

        /** START makeTree */
        \Tune\StatsD::client()->startMemoryProfile('makeTree');
        \Tune\StatsD::client()->startTiming('makeTree');

        while ($row = $resource->fetch()) {
            $this->makeTree($row);
        }

        \Tune\StatsD::client()->endTiming('makeTree');
        \Tune\StatsD::client()->endMemoryProfile('makeTree');
        /** END makeTree */

        /** START countTree */
        \Tune\StatsD::client()->startMemoryProfile('countTree');
        \Tune\StatsD::client()->startTiming('countTree');

        $this->countTree($this->tree);

        \Tune\StatsD::client()->endTiming('countTree');
        \Tune\StatsD::client()->endMemoryProfile('countTree');
        /** END countTree */

        /** START treeDepthOutput */
        \Tune\StatsD::client()->startMemoryProfile('treeDepthOutput');
        \Tune\StatsD::client()->startTiming('treeDepthOutput');

        $this->treeDepthOutput(1, $this->tree[1]);

        \Tune\StatsD::client()->endTiming('treeDepthOutput');
        \Tune\StatsD::client()->endMemoryProfile('treeDepthOutput');
        /** END treeDepthOutput */

        return $this->output;
    }

    /**
     * Iterates over the tree map and assigns data to the output array while deriving the
     * number of subordinates for each parent
     *
     * @param $key
     * @param $tree
     * @param int $depth
     */
    public function treeDepthOutput($key, $tree, $depth = 0) {
        $this->output[] = [
            $this->assoc[$key]['id'],
            $this->assoc[$key]['name'],
            $this->assoc[$key]['parent_name'],
            $depth++,
            $this->assoc[$key]['child_ct']
        ];

        foreach($tree as $id => $branch) {
            $this->treeDepthOutput($id, $branch, $depth);
        }
    }

    /**
     * Derives the number of children each node in tree has
     *
     * @param $tree
     */
    public function countTree($tree) {
        foreach ($tree as $id => $assoc) {
            $this->assoc[$id]['child_ct'] = count($assoc, COUNT_RECURSIVE);
        }
    }

    /**
     * Constructs an associative tree using references
     *
     * @param $row
     */
    public function makeTree($row) {
        $this->assoc[$row['id']] = $row;
        $row['children'] = array(); // here we go

        $row_reference = "row" . $row['id']; // assign this row an id
        $$row_reference = [];

        $parent = "parent" . $row['parent_id']; // assign this row a parent

        if(isset($this->tree[$row['parent_id']])) {
            $$parent = $this->tree[$row['parent_id']]; // parent exists, use that
        } else {
            $$parent = [];
            $this->tree[$row['parent_id']] = &$$parent; // create a new parent record, we'll need it
        }

        ${$parent}[$row['id']] = &$$row_reference; // add child row to the parent
        $this->tree[$row['parent_id']] = $$parent; // update parent with the latest child
        $this->tree[$row['id']] = &$$row_reference; // update row with associated data
    }
}