<?php
namespace Tune;

use Slim\Slim;

class Router {
    protected $slim;

    public function __construct(Slim $slim) {
        $this->slim = $slim;
    }

    /**
     * Declare routes and actions
     *
     * @return $this
     */
    public function routes() {
        $this->slim->get('/employees', function() {
            $controller = new Controller();
            $controller->employees();
        });

        return $this;
    }

    /**
     * 5k
     */
    public function run() {
        $this->slim->run();
    }
}