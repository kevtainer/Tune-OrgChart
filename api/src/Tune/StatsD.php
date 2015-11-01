<?php
namespace Tune;

class StatsD {
    public static $connection;
    /** @var $statsd \Domnikl\StatsD\Client */
    public static $statsd;

    /**
     * Attempts to open a connection to the StatsD socket
     */
    public function connect() {
        try {
            static::$connection = new \Domnikl\Statsd\Connection\UdpSocket('graphite', 8125);
            static::$statsd = new \Domnikl\Statsd\Client(static::$connection, "tune.app");
        } catch (\Exception $e) {
            // normally this would be logged and passed up through escalation and a mock statsd would swallow requests
        }
    }

    /**
     * @return \Domnikl\StatsD\Client
     */
    public static function statsd() {
        return static::$statsd;
    }
}