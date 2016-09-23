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

    protected function mockConfig($params = [])
    {
        $params = array_merge([
            'toArray' => []
        ], $params);

        return Stub::construct('ischenko\yii2\jsloader\base\Config', [], $params);
    }

    /** Tests go below */

    public function testInstance()
    {
        $loader = $this->mockConfig();

        verify($loader)->isInstanceOf('yii\base\Object');
        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
    }

    public function testStorageGetter()
    {
        $config = $this->mockConfig();
        $storage = $this->tester->getMethod($config, 'getStorage');
        $storageObject = $storage->invoke($config);

        verify($storageObject)->isInstanceOf('ArrayObject');
        verify($storage->invoke($config))->same($storageObject);
        verify($storageObject)->hasKey('files');
        verify($storageObject)->hasKey('depends');
        verify($storageObject)->hasKey('codeBlocks');
    }

    public function testAddFile()
    {
        $this->config = $this->mockConfig();
        $storageMethod = $this->tester->getMethod($this->config, 'getStorage');

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

        $this->specify('it adds file to "files" section', function () use ($storageMethod) {
            $storage = $storageMethod->invoke($this->config);

            verify($storage->files)->isEmpty();

            $this->config->addFile('file1', [], 'test');

            verify($storage->files)->notEmpty();
            verify($storage->files)->equals(['test' => ['file1' => []]]);

            $this->config->addFile('file2', ['option' => 1], 'test');

            verify($storage->files)->equals(['test' => ['file1' => [], 'file2' => ['option' => 1]]]);

            $this->config->addFile('1_file', [], 'test');

            verify($storage->files)->equals(['test' => ['file1' => [], 'file2' => ['option' => 1], '1_file' => []]]);
        });

        $this->specify('it generates section name if key is not provided', function () use ($storageMethod) {
            $storage = $storageMethod->invoke($this->config);

            verify($storage->files)->isEmpty();

            $this->config->addFile('file1');

             verify($storage->files)->notEmpty();
             verify($storage->files)->equals([md5('file1') => ['file1' => []]]);
        });
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

        $this->specify('it throws an exception if dependency is not a string or is an empty string', function ($key) {
            $this->config->addDependency('test', $key);
        }, [
            'examples' => [
                [['array']], [$this->config], [''], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it adds dependency to "depends" section', function () {
            $storageMethod = $this->tester->getMethod($this->config, 'getStorage');
            $storage = $storageMethod->invoke($this->config);

            verify($storage->depends)->isEmpty();

            $this->config->addDependency('file1', 'test');

            verify($storage->depends)->notEmpty();
            verify($storage->depends)->equals(['file1' => ['test']]);

            $this->config->addDependency('file1', 'test2');

            verify($storage->depends)->equals(['file1' => ['test', 'test2']]);
        });

        $this->specify('it returns self-reference', function () {
            verify($this->config->addDependency('file', 'dep'))->same($this->config);
        });
    }

    public function testAddCodeBlock()
    {
        $this->config = $this->mockConfig();

        $this->specify('it throws an exception if code is not a string or is an empty string', function ($key) {
            $this->config->addCodeBlock($key);
        }, [
            'examples' => [
                [['array']], [$this->config], [''], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it throws an exception if dependencies is not an array', function ($deps) {
            $this->config->addCodeBlock('test', $deps);
        }, [
            'examples' => [
                [$this->config], [''], [false], [1]
            ],
            'throws' => 'yii\base\InvalidParamException'
        ]);

        $this->specify('it adds code to "codeBlocks" section', function () {
            $storageMethod = $this->tester->getMethod($this->config, 'getStorage');
            $storage = $storageMethod->invoke($this->config);

            verify($storage->codeBlocks)->isEmpty();

            $this->config->addCodeBlock('code1', ['test']);

            verify($storage->codeBlocks)->notEmpty();
            verify($storage->codeBlocks)->equals([['code' => 'code1', 'deps' => ['test']]]);

            $this->config->addCodeBlock('code2', ['test2' => 'test']);

            verify($storage->codeBlocks)->equals([
                ['code' => 'code1', 'deps' => ['test']],
                ['code' => 'code2', 'deps' => ['test2' => 'test']]
            ]);
        });

        $this->specify('it returns self-reference', function () {
            verify($this->config->addCodeBlock('code'))->same($this->config);
        });
    }

    public function testMergeWith()
    {
        $this->markTestIncomplete('TBD');
    }
}
