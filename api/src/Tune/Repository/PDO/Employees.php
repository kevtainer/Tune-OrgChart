<?php
namespace Tune\Repository\PDO;

use Tune\Repository\EmployeesInterface;

class Employees extends Adapter implements EmployeesInterface {
    public function __construct(\PDO $connection = null) {
        parent::__construct($connection);
    }

    /**
     * Grabs employee data from database.
     *
     * @return $this
     */
    public function getEmployees() {
        $this->get(<<< SQL
SELECT e.id as 'id', e.bossId as 'parent_id', m.name AS 'parent_name', e.name AS 'name'
FROM employees e LEFT JOIN employees m ON m.id = e.bossId
SQL
);

        return $this;
    }
}