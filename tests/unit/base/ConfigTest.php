<?php

namespace ischenko\yii2\jsloader\tests\unit\base;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\base\Config;

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
            'toArray' => [],
            'addData' => null
        ], $params);

        return $config = Stub::construct('ischenko\yii2\jsloader\base\Config', [], $params, $testCase);
    }

    /** Tests go below */

    public function testInstance()
    {
        $loader = $this->mockConfig();

        verify($loader)->isInstanceOf('yii\base\Object');
        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
    }

    public function testAddFile()
    {
        $this->config = $this->mockConfig();

        $this->specify('it returns self-reference', function () {
            verify($this->config->addFile('file'))->same($this->config);
        });

        $this->specify('it throws an exception if filename is not a string or is an empty string', function ($file) {
            $this->config->addFile($file);
        }, [
            'examples' => [
                [['array']], [$this->config], [''], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it throws an exception if options is not an array', function ($options) {
            $this->config->addFile('file', $options);
        }, [
            'examples' => [
                ['string'], [1], [$this->config]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it throws an exception if key is not a string or null', function ($key) {
            $this->config->addFile('file', [], $key);
        }, [
            'examples' => [
                [[]], [false], [$this->config]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it creates config array and passes it into merge method', function ($file, $options, $key, $expected) {
            $config = $this->mockConfig([
                'addData' => Stub::once(function ($key, $data) use ($expected) {
                    verify($key)->equals(array_shift($expected));
                    verify($data)->equals(array_shift($expected));
                })], $this);

            $config->addFile($file, $options, $key);

            $this->verifyMockObjects();
        }, ['examples' => [
            ['file1', [], 'test', ['jsFile', ['test' => ['file1', []]]]],
            ['file2', ['option' => 1], 'test', ['jsFile', ['test' => ['file2', ['option' => 1]]]]],
            ['file3', ['option' => 1], null, ['jsFile', [md5('file3') => ['file3', ['option' => 1]]]]],
        ]]);
    }

    public function testAddDependency()
    {
        $this->config = $this->mockConfig();

        $this->specify('it throws an exception if key is not a string or is an empty string', function ($key) {
            $this->config->addDependency($key, 'test');
        }, [
            'examples' => [
                [['array']], [$this->config], [''], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it throws an exception if dependency is not a string or array', function ($key) {
            $this->config->addDependency('test', $key);
        }, [
            'examples' => [
                [$this->config], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it accepts dependency as a string or array', function ($depends) {
            verify($this->config->addDependency('test', $depends))->same($this->config);
        }, [
            'examples' => [
                [['dependency']], ['dependency'], [''], [[]]
            ]
        ]);

        $this->specify('it creates config array and passes it into merge method', function () {
            $config = $this->mockConfig([
                'addData' => Stub::exactly(2, function ($key, $data) {
                    verify($key)->equals('jsDeps');
                    verify($data)->equals(['test' => ['dependency']]);
                })], $this);

            $config->addDependency('test', 'dependency');
            $config->addDependency('test', ['dependency']);

            $this->verifyMockObjects();
        });

        $this->specify('it returns self-reference', function () {
            verify($this->config->addDependency('file', 'dep'))->same($this->config);
        });
    }
}
