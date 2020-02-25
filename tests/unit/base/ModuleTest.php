<?php

namespace ischenko\yii2\jsloader\tests\unit\base;

use Codeception\AssertThrows;
use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use ischenko\yii2\jsloader\base\Module;
use ischenko\yii2\jsloader\ConfigInterface;
use ischenko\yii2\jsloader\tests\UnitTester;
use stdClass;

class ModuleTest extends Unit
{
    use AssertThrows;
    use Specify;

    /**
     * @var Module
     * @specify
     */
    public $module;

    /**
     * @var UnitTester
     */
    protected $tester;

    /** Tests go below */

    public function testInstance()
    {
        $module = $this->mockModule();

        verify($module)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');
        verify($module)->isInstanceOf('yii\base\BaseObject');
    }

    public function testConstruct()
    {
        $module = new Module('test', $this->makeEmpty(ConfigInterface::class));

        verify($module->getName())->equals('test');

        $this->assertThrows('yii\base\InvalidArgumentException', function () use ($module) {
            $this->specify('it throws an exception if name is not a string or is an empty string', function ($name) {
                new Module($name, $this->makeEmpty(ConfigInterface::class));
            }, [
                'examples' => [
                    [null],
                    [['array']],
                    [''],
                    [$module]
                ]
            ]);
        });
    }

    public function testAddFile()
    {
        $this->module = $this->mockModule();

        $this->specify('it returns self-reference', function () {
            verify($this->module->addFile('file'))->same($this->module);
        });

        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('it throws an exception if filename is not a string or is an empty string',
                function ($file) {
                    $this->module->addFile($file);
                }, [
                    'examples' => [
                        [['array']],
                        [$this->module],
                        [''],
                        [false],
                        [1]
                    ]
                ]);
        });

        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('it throws an exception if options is not an array', function ($options) {
                $this->module->addFile('file', $options);
            }, [
                'examples' => [
                    ['string'],
                    [1],
                    [$this->module]
                ]
            ]);
        });

        $this->specify('it inserts file into the internal storage and provides access to them through getter',
            function () {
                $module = $this->module;

                verify($module->getFiles())->array();
                verify($module->getFiles())->equals([]);

                $module->addFile('file1');

                verify($module->getFiles())->equals(['file1' => []]);

                $module->addFile('file2', ['option' => 1]);

                verify($module->getFiles())->equals(['file1' => [], 'file2' => ['option' => 1]]);

                $module->addFile('file1', ['option' => 2]);

                verify($module->getFiles())->equals(['file1' => ['option' => 2], 'file2' => ['option' => 1]]);
            });
    }

    public function testAddDependency()
    {
        $this->module = $this->mockModule();

        $this->specify('it returns self-reference', function () {
            verify($this->module->addDependency($this->mockModule()))->same($this->module);
        });

        $this->specify('it inserts dependencies data into the internal storage and provides access to them through getter',
            function () {
                $module = $this->module;

                verify($module->getDependencies())->array();
                verify($module->getDependencies())->equals([]);

                $module->addDependency($dep1 = $this->mockModule()->addFile('fiel'));

                verify($module->getDependencies())->equals([$dep1->getName() => $dep1]);

                $dep2 = $this->mockModule()->addDependency($dep1);

                $module->clearDependencies();
                $module->addDependency($dep2);

                verify($module->getDependencies())->equals([$dep1->getName() => $dep1]);
            });
    }

    public function testOptionsProperty()
    {
        $module = $this->mockModule();
        verify($module->getBaseUrl())->equals('');
        verify($module->setOptions(['baseUrl' => 'test']))->same($module);
        verify($module->getBaseUrl())->equals('test');
    }

    public function testBaseUrlGetter()
    {
        $module = $this->mockModule();
        verify($module->getOptions())->equals([]);
        verify($module->setOptions(['test' => 1]))->same($module);
        verify($module->getOptions())->equals(['test' => 1]);
    }

    public function testClearFiles()
    {
        $module = $this->mockModule();

        verify($module->getFiles())->equals([]);

        $module->addFile('file1');
        $module->addFile('file2');

        verify($module->getFiles())->equals(['file1' => [], 'file2' => []]);
        verify($module->clearFiles())->same($module);
        verify($module->getFiles())->equals([]);
    }

    public function testClearDependencies()
    {
        $module = $this->mockModule();

        verify($module->getDependencies())->equals([]);

        $module->addDependency($dep1 = $this->mockModule()->addFile('fiel'));

        verify($module->getDependencies())->equals([$dep1->getName() => $dep1]);
        verify($module->clearDependencies())->same($module);
        verify($module->getDependencies())->equals([]);
    }

    public function testAliasSetter()
    {
        $this->module = $this->mockModule();

        verify($this->module->getAlias())->equals($this->module->getName());
        verify($this->module->setAlias('alias'))->same($this->module);
        verify($this->module->getAlias())->equals('alias');
        verify($this->module->setAlias(''))->same($this->module);
        verify($this->module->getAlias())->equals($this->module->getName());

        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('it throws an exception if alias is not a string', function ($alias) {
                $this->module->setAlias($alias);
            }, [
                'examples' => [
                    [null],
                    [new stdClass()],
                    [[]]
                ]
            ]);
        });
    }

    protected function _before()
    {
        parent::_before();
    }

    protected function mockModule($name = null, $params = [], $testCase = false)
    {
        $name = $name ?: uniqid();
        $params = array_merge([], $params);

        return $config = Stub::construct('ischenko\yii2\jsloader\base\Module',
            [$name, $this->makeEmpty(ConfigInterface::class)], $params, $testCase);
    }
}
