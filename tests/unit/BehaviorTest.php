<?php

namespace ischenko\yii2\jsloader\tests\unit;

use Codeception\AssertThrows;
use Codeception\Specify;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use ischenko\yii2\jsloader\Behavior;
use ischenko\yii2\jsloader\tests\UnitTester;
use stdClass;
use yii\web\View;

class BehaviorTest extends Unit
{
    use AssertThrows;
    use Specify;

    /**
     * @var UnitTester
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

        verify($events)->array();

        verify($events)->hasKey(View::EVENT_END_BODY);
        verify($events[View::EVENT_END_BODY])->notEmpty();

        verify($events)->hasKey(View::EVENT_END_PAGE);
        verify($events[View::EVENT_END_PAGE])->notEmpty();
    }

    public function testEnsureView()
    {
        $t = $this->tester;
        $view = $t->mockView();
        $ensureView = $t->getMethod($this->behavior, 'ensureView');

        $actual = $ensureView->invokeArgs($this->behavior, [$view]);

        verify($actual)->same($view);

        $t->expectException('yii\base\InvalidConfigException', function () use ($ensureView) {
            $ensureView->invokeArgs($this->behavior, [new stdClass()]);
        });
    }

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

        $this->specify('it accepts array as argument', function () {
            $stub = $this->tester->mockBaseLoader();

            $this->behavior->setLoader(['class' => get_class($stub)]);

            verify($this->behavior->getLoader())->isInstanceOf(get_class($stub));
        });

        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('if argument is not an array or an object that implements loader interface',
                function ($value) {
                    $this->behavior->setLoader($value);
                }, [
                    'examples' => [
                        ['string'],
                        [123],
                        [$this->behavior],
                        [
                            function () {
                            }
                        ]
                    ]
                ]);
        });

        // getter throws an exception if object does not implement LoaderInterface
        $this->assertThrows('yii\base\InvalidConfigException', function () {
            $this->behavior->setLoader(['class' => '\ArrayObject']);
            $this->behavior->getLoader();
        });
    }

    public function testProcessBundles()
    {
        // it throws an exception if owner is not an instance of yii\web\View object
        $this->assertThrows('yii\base\InvalidConfigException', function () {
            $this->behavior->processBundles();
        });

        $this->specify('it walks through a list of asset bundles and passes their names to a js loader',
            function ($bundles) {
                $this->behavior->attach($this->tester->mockView());

                $view = $this->behavior->owner;
                $view->assetBundles = $bundles;

                $loader = $this->tester->mockLoaderInterface([
                    'registerAssetBundle' => Expected::exactly(count($bundles))
                ], $this);

                $this->behavior->setLoader($loader);
                $this->behavior->processBundles();
            }, [
                'examples' => [
                    [[]],
                    [['bundle' => false]],
                    [['bundle_1' => false, 'bundle_2' => false]],
                ]
            ]);
    }

    public function testProcessAssets()
    {
        $loader = $this->tester->mockLoaderInterface([
            'processAssets' => Expected::once()
        ], $this);

        $this->behavior->setLoader($loader);
        $this->behavior->processAssets();
    }
}
