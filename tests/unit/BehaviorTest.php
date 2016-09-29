<?php

namespace ischenko\yii2\jsloader\tests\unit;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\Behavior;

class BehaviorTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \ischenko\yii2\jsloader\tests\UnitTester
     */
    protected $tester;

    /**
     * @var Behavior
     */
    protected $behavior;

    protected function _before()
    {
        parent::_before();

        $this->behavior = new Behavior();
    }

    /** Tests go below */

    public function testInstance()
    {
        verify($this->behavior)->isInstanceOf('yii\base\Behavior');
    }

    public function testEventsDeclaration()
    {
        $events = $this->behavior->events();

        verify($events)->internalType('array');

        verify($events)->hasKey(\yii\web\View::EVENT_END_BODY);
        verify($events[\yii\web\View::EVENT_END_BODY])->notEmpty();

        verify($events)->hasKey(\yii\web\View::EVENT_END_PAGE);
        verify($events[\yii\web\View::EVENT_END_PAGE])->notEmpty();
    }

    public function testEnsureView()
    {
        $t = $this->tester;
        $view = $t->mockView();
        $ensureView = $t->getMethod($this->behavior, 'ensureView');

        $actual = $ensureView->invokeArgs($this->behavior, [$view]);

        verify($actual)->same($view);

        $t->expectException('yii\base\InvalidConfigException', function () use ($ensureView) {
            $ensureView->invokeArgs($this->behavior, [new \stdClass()]);
        });
    }

    /**
     * @depends ischenko\yii2\jsloader\tests\unit\RequireJsTest:testInstance
     */
    public function testLoaderProperty()
    {
        verify_that($this->behavior->canGetProperty('loader'));
        verify_that($this->behavior->canSetProperty('loader'));

        $this->specify('it allows to read and write value of "loader" property', function () {
            $stub = $this->tester->mockLoaderInterface();

            $this->behavior->setLoader($stub);

            verify($this->behavior->getLoader())->same($stub);
        });

        $this->behavior->attach($this->tester->mockView());

        $this->specify('it accepts array as argument', function() {
            $stub = $this->tester->mockBaseLoader();

            $this->behavior->setLoader(['class' => get_class($stub)]);

            verify($this->behavior->getLoader())->isInstanceOf(get_class($stub));
        });

        $this->specify('setter throws an exception', function () {
            $this->specify('if argument is not an array or an object that implements loader interface', function ($value) {
                $this->behavior->setLoader($value);
            }, [
                'examples' => [
                    ['string'],
                    [123],
                    [$this->behavior],
                    [function () {
                    }]
                ],
                'throws' => 'yii\base\InvalidParamException'
            ]);
        });

        $this->specify('getter throws an exception if object does not implement LoaderInterface', function () {
            $this->behavior->setLoader(['class' => '\ArrayObject']);
            $this->behavior->getLoader();
        }, ['throws' => 'yii\base\InvalidConfigException']);

        $this->specify('getter returns an instance of requirejs loader by default', function () {
            verify($this->behavior->getLoader())->isInstanceOf('ischenko\yii2\jsloader\RequireJs');
        });
    }

    public function testProcessBundles()
    {
        $this->specify('it throws an exception if owner is not an instance of yii\web\View object', function () {
            $this->behavior->processBundles();
        }, ['throws' => 'yii\base\InvalidConfigException']);

        $this->specify('it walks through a list of asset bundles and passes their names to a js loader',
            function ($bundles) {
                $this->behavior->attach($this->tester->mockView());

                $view = $this->behavior->owner;
                $view->assetBundles = $bundles;

                $loader = $this->tester->mockLoaderInterface([
                    'registerAssetBundle' => Stub::exactly(count($bundles))
                ], $this);

                $this->behavior->setLoader($loader);
                $this->behavior->processBundles();

                $this->verifyMockObjects();

            }, ['examples' => [
                [[]],
                [['bundle' => false]],
                [['bundle_1' => false, 'bundle_2' => false]],
            ]]);

    }

    public function testProcessAssets()
    {
        $loader = $this->tester->mockLoaderInterface([
            'processAssets' => Stub::once()
        ], $this);

        $this->behavior->setLoader($loader);
        $this->behavior->processAssets();
    }
}
