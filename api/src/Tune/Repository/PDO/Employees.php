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
     * IMPORTANT :: MySQL doesn't support RECURSIVE natively. Hijacked from
     * http://guilhembichot.blogspot.co.uk/2013/11/with-recursive-and-mysql.html
     * and only slightly modified.
     *
     * @todo find out why the root node is calculating about ~1.85 million more
     * reports than it should
     *
     * @todo associate the name field for each results parent_id
     *
     * @return $this
     */
    public function getEmployees() {
        $this->get(<<< SQL
CALL WITH_EMULATOR("employees_extended","
  SELECT id, name, bossId as parent_id, 0 AS reports
  FROM employees
  WHERE id NOT IN (SELECT bossId FROM employees WHERE id != 1)
","
  SELECT m.id, m.name, m.bossId as parent_id, SUM(1+e.reports) AS reports
  FROM employees m JOIN employees_extended e ON m.id=e.parent_id
  GROUP BY m.id, m.name, parent_id
","
  SELECT id, name, parent_id, SUM(reports) as reports
  FROM employees_extended
  GROUP BY id, name, parent_id
", 0, "");
SQL
);

        return $this;
    }
}