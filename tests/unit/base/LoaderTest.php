<?php

namespace ischenko\yii2\jsloader\tests\unit\base;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\base\Loader;
use yii\helpers\Html;
use yii\web\AssetBundle;
use yii\web\View;

class LoaderTest extends \Codeception\Test\Unit
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
        $loader = $this->tester->mockBaseLoader();

        verify($loader)->isInstanceOf('yii\base\Object');
        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\LoaderInterface');
    }

    public function testViewProperty()
    {
        $loader = $this->tester->mockBaseLoader();

        verify('it provides getter for view property', $loader->canGetProperty('view'))->true();
        verify('it provides setter for view property', $loader->canSetProperty('view'))->false();
        verify('getter returns an instance of view object', $loader->getView())->isInstanceOf(View::className());
    }

    public function testConfigSetter()
    {
        $loader = $this->tester->mockBaseLoader();

        $loader->setConfig(['prop' => 'val']);

        verify_that(property_exists($loader->getConfig(), 'prop'));

        $config = clone $loader->getConfig();

        $config->prop2 = 123;

        $loader->setConfig($config);

        verify_that(property_exists($loader->getConfig(), 'prop2'));
    }

    /**
     * @_depends ischenko\yii2\jsloader\tests\unit\base\ConfigTest:testAddModule
     */
    public function testRegisterAssetBundle()
    {
        $this->specify('it does not process bundles that are not registered in a view', function () {
            verify($this->tester->mockBaseLoader()->registerAssetBundle('test'))->false();
        });


        $this->specify('it does not process bundle if it is not an instance of AssetBundle object', function ($value) {
            $loader = $this->tester->mockBaseLoader([
                'getConfig' => Stub::never()
            ], $this);

            $loader->getView()->assetBundles['test'] = $value;

            verify($loader->registerAssetBundle('test'))->false();
        }, ['examples' => [
            ['string'],
            [['array']],
            [(new \stdClass())],
            [123]
        ]]);

        $this->specify('it will create a module for asset bundle and return it', function () {
            $loader = $this->tester->mockBaseLoader([
                'getConfig' => $this->tester->mockConfigInterface([
                    'getModule' => Stub::once(function ($name) {
                        verify($name)->equals('test');
                        return $this->tester->mockModuleInterface();
                    })
                ], $this)
            ]);

            $loader->getView()->assetBundles['test'] = $bundle = Stub::makeEmpty(AssetBundle::className());

            verify($loader->registerAssetBundle('test'))->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');
        });

        $this->specify('it will register dependencies recursively', function () {
            $loader = $this->tester->mockBaseLoader([
                'getConfig' => $this->tester->mockConfigInterface([
                    'getModule' => $this->tester->mockModuleInterface([
                        'addDependency' => Stub::exactly(3)
                    ], $this)
                ])
            ]);

            $loader->getView()->assetBundles = [
                'test' => Stub::makeEmpty(AssetBundle::className(), [
                    'depends' => [
                        'dep1',
                        'dep2',
                        'dep3',
                        'depN'
                    ]
                ]),
                'dep2' => Stub::makeEmpty(AssetBundle::className(), ['depends' => ['dep1']]),
                'depN' => Stub::makeEmpty(AssetBundle::className(), ['depends' => ['dep2']])
            ];

            verify($loader->registerAssetBundle('test'))->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

            $this->verifyMockObjects();
        });

        $this->specify('it loads files from asset bundle', function () {
            $bundle = Stub::makeEmpty(AssetBundle::className(), [
                'js' => [
                    'file1.js',
                    'file2.js',
                    'file3.js',
                    'fileN.js'
                ]
            ]);

            $loader = $this->tester->mockBaseLoader([
                'getConfig' => $this->tester->mockConfigInterface([
                    'getModule' => $this->tester->mockModuleInterface([
                        'addFile' => Stub::exactly(4, function ($file) use ($bundle) {
                            verify($bundle->js)->contains($file);
                        })
                    ], $this)
                ])
            ]);

            $loader->getView()->assetBundles['test'] = $bundle;

            verify($loader->registerAssetBundle('test'))->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

            $this->verifyMockObjects();
        });

        $this->specify('it clears js files in an asset bundle after loading', function () {
            $bundle = Stub::makeEmpty(AssetBundle::className(), [
                'js' => [
                    'file1.js',
                    'file2.js',
                    'file3.js',
                    'fileN.js'
                ]
            ]);

            $loader = $this->tester->mockBaseLoader([
                'getConfig' => $this->tester->mockConfigInterface([
                    'getModule' => $this->tester->mockModuleInterface()
                ])
            ]);

            $loader->getView()->assetBundles['test'] = $bundle;

            verify($loader->registerAssetBundle('test'))->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

            verify($bundle->js)->equals([]);
        });

        $this->specify('it loads settings from asset bundle', function () {
            $bundle = Stub::makeEmpty(AssetBundle::className(), [
                'js' => [
                ],
                'jsOptions' => [
                    'option1' => 'value1',
                    'option2' => 'value2',
                    'optionN' => 'valueN',
                ],
            ]);

            $loader = $this->tester->mockBaseLoader([
                'getConfig' => $this->tester->mockConfigInterface([
                    'getModule' => $this->tester->mockModuleInterface([
                        'setOptions' => Stub::once(function ($options) use ($bundle) {
                            verify($options)->equals($bundle->jsOptions);
                        })
                    ], $this)
                ])
            ]);

            $loader->getView()->assetBundles['test'] = $bundle;

            verify($loader->registerAssetBundle('test'))->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

            $this->verifyMockObjects();
        });

        $this->specify('each file can have its own settings',
            function ($js, $expectedFile, $expectedOptions) {
                $bundle = Stub::makeEmpty(AssetBundle::className(), [
                    'js' => [
                        $js
                    ]
                ]);

                $loader = $this->tester->mockBaseLoader([
                    'getConfig' => $this->tester->mockConfigInterface([
                        'getModule' => $this->tester->mockModuleInterface([
                            'addFile' => Stub::once(function ($file, $options) use ($expectedFile, $expectedOptions) {
                                verify($file)->internalType('string');
                                verify($file)->equals($expectedFile);
                                verify($options)->internalType('array');
                                verify($options)->equals($expectedOptions);
                            }, $this)
                        ])
                    ])
                ]);

                $loader->getView()->assetBundles['test'] = $bundle;

                verify($loader->registerAssetBundle('test'))->isInstanceOf('ischenko\yii2\jsloader\ModuleInterface');

                $this->verifyMockObjects();
            },
            ['examples' => [
                ['file1.js', 'file1.js', []],
                [['file2.js', 'option' => 'value'], 'file2.js', ['option' => 'value']],
                [['file3.js'], 'file3.js', []],
                [['fileN.js', 'option'], 'fileN.js', ['option']]
            ]]);
    }

    /**
     * @depends testRegisterAssetBundle
     */
    public function testProcessAssets()
    {

        $this->view = $this->tester->mockView();

        $this->view->js = [
            View::POS_READY => [
                'test' => 'ready code block'
            ],
            View::POS_HEAD => [
                'test' => 'head code block'
            ],
            View::POS_END => [
                'test' => 'end code block'
            ],
            View::POS_LOAD => [
                'test' => 'load code block'
            ],
            View::POS_BEGIN => [
                'test' => 'begin code block'
            ],
        ];

        $this->specify('it collects and clears JS blocks (except head blocks) registered in the view', function () {
            unset(
                $this->view->js[View::POS_READY],
                $this->view->js[View::POS_LOAD]
            );

            $loader = $this->tester->mockBaseLoader([
                'view' => $this->view,
                'doRender' => Stub::once(function ($codeBlocks) {
                    verify($codeBlocks)->internalType('array');
                    verify($codeBlocks)->equals([
                        View::POS_END => [
                            'code' => "end code block",
                            'depends' => []
                        ],
                        View::POS_BEGIN => [
                            'code' => "begin code block",
                            'depends' => []
                        ],
                    ]);
                }),
            ], $this);

            $loader->processAssets();

            verify($loader->getView()->js)->equals([
                View::POS_HEAD => [
                    'test' => 'head code block'
                ]
            ]);

            $this->verifyMockObjects();
        });

        $this->specify('it skips empty sections', function () {
            $loader = $this->tester->mockBaseLoader([
                'view' => $this->view
            ]);

            $loader->getView()->js[View::POS_LOAD] = [];

            $loader->processAssets();

            verify($loader->getView()->js)->equals([
                View::POS_HEAD => [
                    'test' => 'head code block'
                ],
                View::POS_LOAD => []
            ]);
        });

        $this->specify('it will add JqueryAsset as a dependency for code blocks from POS_READY and POS_LOAD sections', function () {
            $this->view->js = [
                View::POS_READY => [
                    'test' => 'ready code block'
                ],
                View::POS_LOAD => [
                    'test' => 'load code block'
                ],
            ];

            $this->module = $this->tester->mockModuleInterface();

            $loader = $this->tester->mockBaseLoader([
                'view' => $this->view,
                'doRender' => Stub::once(function ($codeBlocks) {
                    verify($codeBlocks)->internalType('array');
                    verify($codeBlocks)->hasKey(View::POS_LOAD);
                    verify($codeBlocks[View::POS_LOAD])->hasKey('depends');
                    verify($codeBlocks[View::POS_LOAD]['depends'])->contains($this->module);
                    verify($codeBlocks)->hasKey(View::POS_READY);
                    verify($codeBlocks[View::POS_READY])->hasKey('depends');
                    verify($codeBlocks[View::POS_READY]['depends'])->contains($this->module);
                }),
                'getConfig' => $this->tester->mockConfigInterface([
                    'getModule' => Stub::exactly(2, function ($name) {
                        verify($name)->equals('yii\web\JqueryAsset');
                        return $this->module;
                    })
                ], $this)
            ], $this);

            $loader->processAssets();

            $this->verifyMockObjects();
        });

        $this->specify('it registers modules for js files and adds them as dependencies for appropriate code block', function () {
            $this->view->jsFiles = [
                View::POS_BEGIN => [
                    Html::jsFile('/file1.js'),
                    Html::jsFile('/file2.js')
                ],
                View::POS_END => [
                    Html::jsFile('/file3.js'),
                    Html::jsFile('/file4.js')
                ],
            ];

            $this->modules = [
                '/file1.js' => $this->tester->mockModuleInterface(),
                '/file2.js' => $this->tester->mockModuleInterface(),
                '/file3.js' => $this->tester->mockModuleInterface(),
                '/file4.js' => $this->tester->mockModuleInterface(),
            ];

            $this->module = $this->tester->mockModuleInterface([
                'addFile' => Stub::exactly(4, function ($name) {
                    return $this->modules[$name];
                })
            ], $this);

            $loader = $this->tester->mockBaseLoader([
                'view' => $this->view,
                'getConfig' => $this->tester->mockConfigInterface([
                    'addModule' => Stub::exactly(4, function ($name) {
                        return $this->module;
                    })
                ], $this),
                'doRender' => Stub::once(function ($codeBlocks) {
                    verify($codeBlocks)->internalType('array');
                    verify($codeBlocks)->hasKey(View::POS_BEGIN);
                    verify($codeBlocks[View::POS_BEGIN])->hasKey('depends');
                    verify($codeBlocks[View::POS_BEGIN]['depends'])->equals([$this->modules['/file1.js'], $this->modules['/file2.js']]);
                    verify($codeBlocks)->hasKey(View::POS_END);
                    verify($codeBlocks[View::POS_END])->hasKey('depends');
                    verify($codeBlocks[View::POS_END]['depends'])->equals([$this->modules['/file1.js'], $this->modules['/file4.js']]);
                })
            ], $this);

            $loader->processAssets();

            verify($this->view->jsFiles)->equals([]);

            $this->verifyMockObjects();
        });
    }
}
