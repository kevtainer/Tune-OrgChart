<?php
namespace Tune\Repository;

interface AdapterInterface {
    public function get($query);
    public function fetchAll();
    public function fetch();
}
