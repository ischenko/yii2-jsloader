<?php

namespace ischenko\yii2\jsloader\tests\unit\base;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\base\Module;

class ModuleTest extends \Codeception\Test\Unit
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

    protected function mockModule($name = null, $params = [], $testCase = false)
    {
        $name = $name ?: uniqid();
        $params = array_merge([], $params);

        return $config = Stub::construct('ischenko\yii2\jsloader\base\Module', [$name], $params, $testCase);
    }

    /** Tests go below */

    public function testInstance()
    {
        $module = $this->mockModule();

        verify($module)->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');
    }

    public function testConstruct()
    {
        $module = new Module('test');

        verify($module->getName())->equals('test');

        $this->specify('it throws an exception if name is not a string or is an empty string', function ($name) {
            new Module($name);
        }, ['throws' => 'yii\base\InvalidParamException', 'examples' => [
            [null],
            [['array']],
            [''],
            [$module]
        ]]);
    }

    public function testAddFile()
    {
        $this->module = $this->mockModule();

        $this->specify('it returns self-reference', function () {
            verify($this->module->addFile('file'))->same($this->module);
        });

        $this->specify('it throws an exception if filename is not a string or is an empty string', function ($file) {
            $this->module->addFile($file);
        }, [
            'examples' => [
                [['array']], [$this->module], [''], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it throws an exception if options is not an array', function ($options) {
            $this->module->addFile('file', $options);
        }, [
            'examples' => [
                ['string'], [1], [$this->module]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it inserts file into the internal storage and provides access to them through getter', function () {
            $module = $this->module;

            verify($module->getFiles())->internalType('array');
            verify($module->getFiles())->equals([]);

            $module->addFile('file1');

            verify($module->getFiles(true))->equals(['file1']);
            verify($module->getFiles(false))->equals(['file1' => []]);

            $module->addFile('file2', ['option' => 1]);

            verify($module->getFiles(false))->equals(['file1' => [], 'file2' => ['option' => 1]]);

            $module->addFile('file1', ['option' => 2]);

            verify($module->getFiles(false))->equals(['file1' => ['option' => 2], 'file2' => ['option' => 1]]);
        });
    }

    public function testAddDependency()
    {
        $this->module = $this->mockModule();

        $this->specify('it returns self-reference', function () {
            verify($this->module->addDependency($this->mockModule()))->same($this->module);
        });

        $this->specify('it inserts dependencies data into the internal storage and provides access to them through getter', function () {
            $module = $this->module;

            verify($module->getDependencies())->internalType('array');
            verify($module->getDependencies())->equals([]);

            $module->addDependency($dep1 = $this->mockModule());

            verify($module->getDependencies())->equals([$dep1]);
        });
    }

    public function testOptionsProperty()
    {
        $module = $this->mockModule();
        verify($module->getOptions())->equals([]);
        $module->setOptions(['test' => 1]);
        verify($module->getOptions())->equals(['test' => 1]);
    }

    public function testClearFiles()
    {
        $module = $this->mockModule();

        verify($module->getFiles())->equals([]);

        $module->addFile('file1');
        $module->addFile('file2');

        verify($module->getFiles(false))->equals(['file1' => [], 'file2' => []]);
        verify($module->clearFiles())->same($module);
        verify($module->getFiles(false))->equals([]);
    }

    public function testClearDependencies()
    {
        $module = $this->mockModule();

        verify($module->getDependencies())->equals([]);

        $module->addDependency($dep1 = $this->mockModule());

        verify($module->getDependencies())->equals([$dep1]);
        verify($module->clearDependencies())->same($module);
        verify($module->getDependencies())->equals([]);
    }
}
