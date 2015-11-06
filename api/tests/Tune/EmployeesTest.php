<?php

namespace Tune;
use Mockery as m;
use Tune\OrgChart\Employees;
class EmployeesTest extends \PHPUnit_Framework_TestCase {

    protected $repositoryData;
    protected $repositoryMock;

    public function setup() {
        $this->repositoryMock = m::mock('Tune\Repository\EmployeesInterface');
        $this->repositoryData = json_decode(file_get_contents(__DIR__ . "/EmployeesTestData.json"), true);

        $statsdMock = m::mock('\Domnikl\StatsD\Client')->shouldIgnoreMissing();
        StatsD::$client = $statsdMock;

        $inputArray = new \ArrayIterator($this->repositoryData['input']);

        $resourceMock = m::mock('Tune\Repository\AdapterInterface');
        $iteration = function() use ($inputArray) {
            if ($inputArray->valid()) {
                $response = $inputArray->current();
                $inputArray->next();
            }

            return isset($response) ? $response : false;
        };

        $resourceMock->shouldReceive('fetch')->times(21)->andReturnUsing($iteration);
        $this->repositoryMock->shouldReceive('getEmployees')->once()->andReturn($resourceMock);
    }

    public function tearDown() {
        m::close();
    }

    public function testEmployeesGetShouldReturnDerivedData()
    {
        $employees = new Employees($this->repositoryMock);
        $response = $employees->get();
        usort($response, function($a, $b) {
            return $a[0] - $b[0];
        });

        $this->assertCount(20, $response);
        $this->assertEquals(md5(serialize($this->repositoryData['output'])), md5(serialize($response)));
    }
}