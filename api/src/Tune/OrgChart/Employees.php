<?php
namespace Tune\OrgChart;

use Tune\Repository;
use Tune\Repository\EmployeesInterface;
use Tune\Repository\Factory;

class Employees {
    /** @var $repository EmployeesInterface */
    protected $repository;
    protected $tree = [];
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
     * Processes employees
     *
     * @return array
     */
    public function get() {
        /** @var \Tune\Repository\PDO\Employees $resource */
        $resource = $this->repository->getEmployees();

        \Tune\StatsD::statsd()->startMemoryProfile('generateTree');
        \Tune\StatsD::statsd()->startTiming('generateTree');

        while ($row = $resource->fetch()) {
            $this->makeTree($row);
        }

        \Tune\StatsD::statsd()->endTiming('generateTree');
        \Tune\StatsD::statsd()->endMemoryProfile('generateTree');

        \Tune\StatsD::statsd()->startMemoryProfile('flattenTree');
        \Tune\StatsD::statsd()->startTiming('flattenTree');

        $this->flattenTree($this->tree[1]);

        \Tune\StatsD::statsd()->endTiming('flattenTree');
        \Tune\StatsD::statsd()->endMemoryProfile('flattenTree');

        return $this->output;
    }

    /**
     * Traverses and flattens tree branches, keeping track of depth along the way.
     *
     * @param $child
     * @param int $depth
     */
    public function flattenTree($child, $depth = 0) {
        $this->output[] = [
            $child['id'],
            $child['name'],
            $child['parent_name'],
            $depth++
        ];

        foreach($child['children'] as $children) {
            $this->flattenTree($children, $depth);
        }
    }

    /**
     * Constructs an associative tree using references
     *
     * @param $row
     */
    public function makeTree($row) {
        $row['children'] = array(); // here we go

        $row_reference = "row" . $row['id']; // assign this row an id
        $$row_reference = $row; // assign data so we can reference it later

        $parent = "parent" . $row['parent_id']; // assign this row a parent

        if(isset($this->tree[$row['parent_id']])) {
            $$parent = $this->tree[$row['parent_id']]; // parent exists, use that
        } else {
            $$parent = [
                'id' => $row['parent_id'],
                'parent_id' => null,
                'children' => array()
            ];
            $this->tree[$row['parent_id']] = &$$parent; // create a new parent record, we'll need it
        }

        ${$parent}['children'][] = &$$row_reference; // add child row to the parent
        $this->tree[$row['parent_id']] = $$parent; // update parent with the latest child
        $this->tree[$row['id']] = &$$row_reference; // update row with associated data
    }
}