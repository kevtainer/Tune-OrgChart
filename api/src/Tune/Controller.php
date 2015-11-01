<?php
namespace Tune;

use Tune\OrgChart\Employees;

class Controller {
    /**
     * @throws \Exception
     */
    public function employees() {
        $employees = new Employees();

        $this->outputJSON(json_encode($employees->get()));
    }

    /**
     * Creates origin and content-type headers then dumps JSON data
     *
     * @param string $json
     * @throws \Exception
     */
    protected function outputJSON($json) {
        if (!is_string($json)) {
            throw new \Exception("This method will only accept a string parameter.");
        }
        array_map(function($origin) {
            header(sprintf("Access-Control-Allow-Origin: %s", $origin));
        }, Config::get('origins'));

        // @todo find out why nginx flat out ignores this
        header('Content-Type: application/json');
        echo ($json);
    }
}