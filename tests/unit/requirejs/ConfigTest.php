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

        $this->config = new Config();
    }

    /** Tests go below */

    public function testInstance()
    {
        verify($this->config)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
        verify($this->config)->isInstanceOf('ischenko\yii2\jsloader\requirejs\Config');
    }

    public function testToArray()
    {
        $this->markTestIncomplete('TBD');
    }

    public function testStorageGetter()
    {
        $getStorage = $this->tester->getMethod($this->config, 'getStorage');
        $storage = $getStorage->invoke($this->config);

        verify($storage)->isInstanceOf('ArrayObject');
        verify($storage)->same($getStorage->invoke($this->config));
    }

    public function testPathsSetter()
    {
        $this->getStorage = $this->tester->getMethod($this->config, 'getStorage');
        $storage = $this->getStorage->invoke($this->config);

        verify($storage)->hasntKey('paths');

        $this->config->setPaths(['test' => ['test.js']]);

        verify($storage)->hasKey('paths');
        verify($storage->paths)->equals(['test' => ['test.js']]);

        $this->config->setPaths(['test' => ['test2.js']]);
        verify($storage->paths)->equals(['test' => ['test.js', 'test2.js']]);

        $this->config->setPaths(['test' => ['test2.js']], false);
        verify($storage->paths)->equals(['test' => ['test2.js']]);

        $this->config->setPaths(['test' => 'test2.js'], false);
        verify($storage->paths)->equals(['test' => 'test2.js']);

        $this->config->setPaths(['test' => 'test3.js']);
        verify($storage->paths)->equals(['test' => 'test3.js']);

        verify($this->config->setPaths([]))->same($this->config);
    }

    public function testShimSetter()
    {
        $this->getStorage = $this->tester->getMethod($this->config, 'getStorage');
        $storage = $this->getStorage->invoke($this->config);

        verify($storage)->hasntKey('shim');

        $this->config->setShim(['test' => ['deps' => ['test.js']]]);

        verify($storage)->hasKey('shim');
        verify($storage->shim)->equals(['test' => ['deps' => ['test.js']]]);

        $this->config->setShim(['test' => ['deps' => ['test2.js']]]);
        verify($storage->shim)->equals(['test' => ['deps' => ['test.js', 'test2.js']]]);

        $this->config->setShim(['test' => ['deps' => ['test3.js']]], false);
        verify($storage->shim)->equals(['test' => ['deps' => ['test3.js']]]);

        verify($this->config->setShim([]))->same($this->config);
    }

    public function testAddData()
    {
        $this->getStorage = $this->tester->getMethod($this->config, 'getStorage');

        $this->specify('it adds js code blocks into "jsCode" section of internal storage', function ($code, $opts, $expected) {
            $storage = $this->getStorage->invoke($this->config);

            verify($storage->jsCode)->isEmpty();
            $this->config->addCodeBlock($code, $opts);
            verify($storage->jsCode)->equals($expected);
        }, ['examples' => [
            ['code1', ['test'], [['code' => 'code1', 'depends' => ['test']]]],
            ['code2', ['test2' => 'test'], [['code' => 'code2', 'depends' => ['test2' => 'test']]]],
        ]]);

        $this->specify('it forwards data of jsFile section to the setPaths method', function () {
            $config = Stub::construct($this->config, [], [
                'setPaths' => Stub::once(function ($data) {
                    verify($data)->hasKey('test');
                    verify($data['test'])->equals('file1');
                })
            ], $this);

            $config->addFile('file1.js', [], 'test');

            $this->verifyMockObjects();
        });

        $this->specify('it shifts a file from the paths section to the shim section as a dependency for the new file', function () {
            $config = $this->config;

            $config->addFile('file1.js', [], 'test');

            verify($config->getPaths())->equals(['test' => 'file1']);
            verify($config->getShim())->equals([]);

            $config->addFile('file2.js', [], 'test');

            verify($config->getPaths())->equals(['test' => 'file2']);
            verify($config->getShim())->equals(['test' => ['deps' => ['file1']]]);
        });

        $this->specify('it transforms and forwards data of jsDeps section to the setShim method', function () {
            $config = Stub::construct($this->config, [], [
                'setShim' => Stub::once(function ($data) {
                    verify($data)->hasKey('test');
                    verify($data['test'])->equals(['deps' => ['dep']]);
                })
            ], $this);

            $config->addDependency('test', 'dep');

            $this->verifyMockObjects();
        });
    }
}
