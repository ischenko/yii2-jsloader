<?php

namespace ischenko\yii2\jsloader\tests\unit\base;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\base\Config;
use ischenko\yii2\jsloader\filters\Position;

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

    protected function mockConfig($params = [], $testCase = false)
    {
         $params = array_merge([
            'toArray' => []
        ], $params);

        return $config = Stub::construct('ischenko\yii2\jsloader\base\Config', [], $params, $testCase);
    }

    /** Tests go below */

    public function testInstance()
    {
        $config = $this->mockConfig();

        verify($config)->isInstanceOf('yii\base\Object');
        verify($config)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
    }

    public function testAddModule()
    {
        $config = $this->mockConfig();
        $module = $config->addModule('test');

        verify($module)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

        $this->tester->expectException('yii\base\InvalidParamException', function() use ($config) {
            $config->addModule('');
        });

        verify($config->getModule('test'))->same($module);
        verify($config->getModule('test2'))->null();

        $module2 = clone $module;

        verify($config->addModule($module2))->same($module2);
        verify($config->getModule('test'))->same($module2);

        verify($config->getModules())->equals(['test' => $module2]);
    }

    public function testModuleGetter()
    {
        $config = $this->mockConfig();

        $module1 = $config->addModule('test1');
        $module2 = $config->addModule('test2');
        $module3 = $config->addModule('test3');
        $module4 = $config->addModule('test4');

        verify($module1)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');
        verify($module2)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');
        verify($module3)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');
        verify($module4)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

        $module1->setOptions(['position' => 1]);
        $module2->setOptions(['position' => 3]);
        $module3->setOptions(['position' => 3]);

        verify($config->getModules(new Position(1)))->equals([$module1]);
        verify($config->getModules(new Position(2)))->equals([]);
        verify($config->getModules(new Position(3)))->equals([$module2, $module3]);
    }
}
