<?php

namespace ischenko\yii2\jsloader\tests\unit\requirejs;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\requirejs\Config;

class ConfigTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \ischenko\yii2\jsloader\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();
    }

    /** Tests go below */

    public function testInstance()
    {
        $config = new Config();

        verify($config)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
        verify($config)->isInstanceOf('ischenko\yii2\jsloader\requirejs\Config');
    }

    public function testToArray()
    {
        $this->markTestIncomplete('TBD');
    }
}
