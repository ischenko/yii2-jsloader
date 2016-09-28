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

    /**
     * @depends testBuild
     */
    public function testToArray()
    {
        $config = Stub::make($this->config, [
            'build' => Stub::once(function() {
                return new \ArrayObject();
            })
        ], $this);

        verify($config->toArray())->internalType('array');
    }

    public function testPathsSetter()
    {
        $this->getStorage = $this->tester->getMethod($this->config, 'getStorage');
        $storage = $this->getStorage->invoke($this->config);

        verify($storage)->hasntKey('paths');

        $this->config->setPaths(['test' => ['test.js']], true);

        verify($storage)->hasKey('paths');
        verify($storage->paths)->equals(['test' => ['test.js']]);

        $this->config->setPaths(['test' => ['test2.js']], true);
        verify($storage->paths)->equals(['test' => ['test.js', 'test2.js']]);

        $this->config->setPaths(['test' => ['test2.js']], false);
        verify($storage->paths)->equals(['test' => ['test2.js']]);

        $this->config->setPaths(['test' => 'test2.js'], false);
        verify($storage->paths)->equals(['test' => 'test2.js']);

        $this->config->setPaths(['test' => 'test3.js'], true);
        verify($storage->paths)->equals(['test' => 'test3.js']);

        verify($this->config->setPaths([]))->same($this->config);
    }

    public function testShimSetter()
    {
        $this->getStorage = $this->tester->getMethod($this->config, 'getStorage');
        $storage = $this->getStorage->invoke($this->config);

        verify($storage)->hasntKey('shim');

        $this->config->setShim(['test' => ['deps' => ['test.js']]], true);

        verify($storage)->hasKey('shim');
        verify($storage->shim)->equals(['test' => ['deps' => ['test.js']]]);

        $this->config->setShim(['test' => ['deps' => ['test2.js']]], true);
        verify($storage->shim)->equals(['test' => ['deps' => ['test.js', 'test2.js']]]);

        $this->config->setShim(['test' => ['deps' => ['test3.js']]], false);
        verify($storage->shim)->equals(['test' => ['deps' => ['test3.js']]]);

        verify($this->config->setShim([]))->same($this->config);
    }

    public function testBuild()
    {
        $build = $this->tester->getMethod($this->config, 'build');
        $getStorage = $this->tester->getMethod($this->config, 'getStorage');

        verify($build->invoke($this->config))->isInstanceOf('ArrayObject');
        verify($build->invoke($this->config))->same($getStorage->invoke($this->config));

        $storage = $getStorage->invoke($this->config);

        $storage->jsFiles = ['test' => []];

        $build->invoke($this->config);

        verify('It removes jsFiles section', $storage)->hasntKey('jsFiles');

        $this->specify('it adds files into the paths configuration without ".js" extension', function() {
            $storage = $this->tester->getMethod($this->config, 'getStorage')->invoke($this->config);

            $storage->jsFiles = [
                'test' => ['file1.js' => []],
                'test2' => ['file2.js' => []],
                'test3' => ['file3.sj' => []]
            ];

            $this->tester->getMethod($this->config, 'build')->invoke($this->config);

            verify($storage)->hasKey('paths');
            verify($storage->paths)->equals(['test' => 'file1', 'test2' => 'file2', 'test3' => 'file3.sj']);
        });

        $this->specify('it shifts file into dependencies if there is another file for the same section', function() {
            $storage = $this->tester->getMethod($this->config, 'getStorage')->invoke($this->config);

            $storage->jsFiles = [
                'test' => [
                    'file1.js' => [],
                    'file2.js' => [],
                ],
            ];

            $this->tester->getMethod($this->config, 'build')->invoke($this->config);

            verify($storage)->hasKey('paths');
            verify($storage->paths)->equals(['test' => 'file2']);

            verify($storage)->hasKey('shim');
            verify($storage->shim)->equals(['test' => ['deps' => ['file1']]]);
        });
    }
}
