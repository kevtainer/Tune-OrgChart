<?php

namespace Tune;

use Tune\Config;
class ConfigTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->configInstance = new Config();
    }

    public function tearDown() {
        Config::$instance = null;
    }

    public function testConfigInstance() {
        $instance = new Config();
        $this->assertInstanceOf('Tune\Config', $instance);
    }

    public function testConfigSingletonInstance() {
        $instance = new Config();
        $instance->load(__DIR__ . '/ConfigTestData.json');
        $this->assertInstanceOf('Tune\Config', Config::$instance);
    }

    /**
     * @expectedException   \Exception
     * @expectedExceptionMessage    This method requires an array of configuration params to initialize.
     * @throws \Exception
     */
    public function testConfigInstanceThrowsExceptionForEmptyArray() {
        $instance = new Config();
        $instance->load(__DIR__ . '/ConfigTestDataEmpty.json');
    }

    /**
     * @expectedException   \Exception
     * @expectedExceptionMessage    This singleton may only be loaded once.
     * @throws \Exception
     */
    public function testAttemptedConfigInstanceReinitialization() {
        $instance = new Config();
        $instance->load(__DIR__ . '/ConfigTestData.json');
        $instance = new Config();
        $instance->load(__DIR__ . '/ConfigTestData.json');
    }

    public function testConfigReturnsExpectedData() {
        $instance = new Config();
        $instance->load(__DIR__ . '/ConfigTestData.json');

        $this->assertEquals([ 'a' => 'b', 'z' => 'y'], Config::get());
        $this->assertEquals('b', Config::get('a'));
        $this->assertEquals('y', Config::get('z'));
    }

    /**
     * @expectedException   \Exception
     * @expectedExceptionMessage    This method requires the Config singleton to be initialized.
     * @throws \Exception
     */
    public function testGetBeforeLoadThrowsException() {
        Config::get('foo');
    }
}