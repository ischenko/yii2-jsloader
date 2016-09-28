<?php

namespace ischenko\yii2\jsloader\tests\unit;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\RequireJs;
use yii\helpers\Html;
use yii\web\View;

class RequireJsTest extends \Codeception\Test\Unit
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

    protected function mockLoader($params = [], $testCase = false)
    {
        if (isset($params['view'])) {
            $view = $params['view'];
            unset($params['view']);
        } else {
            $view = $this->tester->mockView();
        }

        return Stub::construct('ischenko\yii2\jsloader\RequireJs', [$view], $params, $testCase);
    }

    /** Tests go below */

    public function testInstance()
    {
        $loader = $this->mockLoader();

        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\base\Loader');
        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\LoaderInterface');
    }

    public function testConfigGetter()
    {
        $loader = $this->mockLoader();
        $config = $loader->getConfig();

        verify($config)->notEmpty();
        verify($config)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
        verify($config)->isInstanceOf('ischenko\yii2\jsloader\requirejs\Config');
        verify($config)->same($loader->getConfig());
    }

    public function testRenderRequireBlock()
    {
        $loader = $this->mockLoader();
        $renderRequireBlock = $this->tester->getMethod($loader, 'renderRequireBlock');

        verify($renderRequireBlock->invokeArgs($loader, ['', []]))->equals('');
        verify($renderRequireBlock->invokeArgs($loader, ['test;', []]))
            ->equals("requirejs([], function() {\ntest;\n});");

        verify($renderRequireBlock->invokeArgs($loader, ['test;', ['test']]))
            ->equals("requirejs([\"test\"], function() {\ntest;\n});");

        verify($renderRequireBlock->invokeArgs($loader, ['test;', ['test' => 'testing']]))
            ->equals("requirejs([\"test\"], function(testing) {\ntest;\n});");

        verify($renderRequireBlock->invokeArgs($loader, ['test;', ['test' => 'testing', 'test2']]))
            ->equals("requirejs([\"test\", \"test2\"], function(testing) {\ntest;\n});");
    }

    public function testDoRender()
    {
        $codeBlocks = [
            View::POS_END => [
                'code' => 'end code block',
                'depends' => []
            ],
            View::POS_LOAD => [
                'code' => 'load code block',
                'depends' => ['/file1']
            ],
            View::POS_BEGIN => [
                'code' => 'begin code block',
            ],
            View::POS_READY => [
                'depends' => ['/file1']
            ],
        ];

        $loader = $this->mockLoader([
            'renderRequireBlock' => Stub::exactly(2, function ($code, $depends) use (&$codeBlocks) {
                unset($codeBlocks[View::POS_READY], $codeBlocks[View::POS_BEGIN]);

                krsort($codeBlocks);

                $data = array_shift($codeBlocks);

                verify($code)->equals($data['code'] . "\n");
                verify($depends)->equals($data['depends']);
            }),
            'publishRequireJs' => Stub::once()
        ], $this);

        $doRender = $this->tester->getMethod($loader, 'doRender');

        $doRender->invokeArgs($loader, [$codeBlocks]);

        $this->verifyMockObjects();
    }

    public function testPublishRequireJs()
    {
        $this->specify('it publishes and registers the requirejs library from bower package', function () {
            $loader = $this->mockLoader([
                'main' => false,
                'view' => $this->tester->mockView([
                    'registerJsFile' => Stub::once(function($path, $options) {
                        verify($path)->equals('/require.js');
                        verify($options)->hasKey('position');
                        verify($options['position'])->equals(View::POS_END);
                    }),
                    'assetManager' => Stub::makeEmpty('yii\web\AssetManager', [
                        'publish' => Stub::once(function ($path) {
                            verify($path)->equals('@bower/requirejs/require.js');
                            return [null, '/require.js'];
                        })
                    ], $this)
                ], $this)
            ]);

            $publishRequireJs = $this->tester->getMethod($loader, 'publishRequireJs');

            $publishRequireJs->invokeArgs($loader, ['code']);

            $this->verifyMockObjects();
        });

        $this->specify('it does not publish the requirejs library if the libraryUrl property is set', function () {
            $loader = $this->mockLoader([
                'main' => false,
                'libraryUrl' => '/requirejs.js',
                'view' => $this->tester->mockView([
                    'registerJsFile' => Stub::once(function($path, $options) {
                        verify($path)->equals('/requirejs.js');
                    }),
                    'assetManager' => Stub::makeEmpty('yii\web\AssetManager', [
                        'publish' => Stub::never()
                    ], $this)
                ], $this)
            ]);

            $publishRequireJs = $this->tester->getMethod($loader, 'publishRequireJs');

            $publishRequireJs->invokeArgs($loader, ['code']);

            $this->verifyMockObjects();
        });

        $this->specify('it registers previously rendered JS code', function() {
            $data = [
                [View::POS_END, "code"],
                [View::POS_HEAD, "var requirejs = {};"]
            ];

            $loader = $this->mockLoader([
                'main' => false,
                'view' => $this->tester->mockView([
                    'registerJs' => Stub::exactly(2, function($code, $position) use (&$data) {
                        $expected = array_shift($data);

                        verify($position)->equals($expected[0]);
                        verify($code)->equals($expected[1]);
                    }),
                    'assetManager' => Stub::makeEmpty('yii\web\AssetManager', [
                        'publish' => Stub::once(function () {
                            return [null, '/require.js'];
                        })
                    ], $this)
                ], $this)
            ]);


            $publishRequireJs = $this->tester->getMethod($loader, 'publishRequireJs');

            $publishRequireJs->invokeArgs($loader, ['code']);

            $this->verifyMockObjects();
        });

        $this->specify('it writes generated code into a file and then sets it as data-main entry', function() {
            $loader = $this->mockLoader([
                'libraryUrl' => '/require.js',
                'view' => $this->tester->mockView([
                    'registerJs' => Stub::once(),
                    'registerJsFile' => Stub::once(function($file, $options) {
                        verify($file)->equals('/require.js');
                        verify($options)->hasKey('position');
                        verify($options['position'])->equals(View::POS_END);
                        verify($options)->hasKey('data-main');
                        verify($options['data-main'])->equals('/requirejs-main.js');
                    }),
                    'assetManager' => Stub::makeEmpty('yii\web\AssetManager', [
                        'publish' => Stub::once(function ($path) {
                            verify($path)->equals(\Yii::getAlias('@runtime/jsloader/requirejs-main.js'));
                            verify("code")->equalsFile(\Yii::getAlias($path));
                            return [null, '/' . basename($path)];
                        })
                    ], $this)
                ], $this)
            ]);

            $publishRequireJs = $this->tester->getMethod($loader, 'publishRequireJs');

            $publishRequireJs->invokeArgs($loader, ['code']);

            $this->verifyMockObjects();
        });
    }

    /**
     * @dataProvider providerRenderRequireConfigOptions
     */
    public function testRenderRequireConfig($config, $expected)
    {
        $loader = $this->mockLoader([
            'getConfig' => Stub::once(function() use ($config) {
                return $this->tester->mockConfigInterface([
                    'toArray' => Stub::once(function() use ($config) {
                        return $config;
                    })
                ], $this);
            })
        ], $this);

        verify($this->tester->getMethod($loader, 'renderRequireConfig')->invoke($loader))->equals($expected);
    }

    public function providerRenderRequireConfigOptions()
    {
        return [
            [[], 'var requirejs = {};'],
            [['paths' => []], 'var requirejs = {};'],
            [['paths' => ['test' => 'file']], 'var requirejs = {"paths":{"test":"file"}};'],
            [['paths' => ['test' => 'file'], 'shim' => ['test' => ['deps' => ['file2']]]], 'var requirejs = {"paths":{"test":"file"},"shim":{"test":{"deps":["file2"]}}};'],
        ];
    }
}
