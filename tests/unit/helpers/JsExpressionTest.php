<?php

namespace ischenko\yii2\jsloader\tests\unit\filters;

use Codeception\AssertThrows;
use Codeception\Specify;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\tests\UnitTester;

class JsExpressionTest extends Unit
{
    use AssertThrows;
    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();
    }

    /** Tests go below */

    /**
     * @depends testExpressionProperty
     */
    public function testConstruct()
    {
        $expression = new JsExpression();
        verify($expression->getExpression())->null();
        verify($expression->getDependencies())->equals([]);

        $expression = new JsExpression('test', [$m = $this->tester->mockModuleInterface()]);
        verify($expression->getExpression())->equals('test');
        verify($expression->getDependencies())->equals([$m]);
        verify((new JsExpression($expression))->getExpression())->same($expression);
    }

    public function testExpressionProperty()
    {
        $this->specify('value can be set via setter', function () {
            $expression = new JsExpression();
            verify($expression->getExpression())->null();
            verify($expression->setExpression('test'))->same($expression);
            verify($expression->getExpression())->equals('test');
            $expression->setExpression($e = new JsExpression());
            verify($expression->getExpression())->same($e);
        });

        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('it throws an exception if expression is not a string or JsExpression object',
                function ($value) {
                    $expression = new JsExpression();
                    $expression->setExpression($value);
                }, [
                    'examples' => [
                        [[]],
                        [false],
                        [$this]
                    ]
                ]);
        });

        // it throws an exception if expression is self-reference
        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $expression = new JsExpression();
            $expression->setExpression($expression);
        });
    }

    public function testDependenciesProperty()
    {
        $this->specify('dependencies can be set via setter', function () {
            $expression = new JsExpression();
            verify($expression->getDependencies())->equals([]);
            verify($expression->setDependencies([$m = $this->tester->mockModuleInterface()]))->same($expression);
            verify($expression->getDependencies())->equals([$m]);
            verify($expression->setDependencies([$m = $this->tester->mockModuleInterface()]))->same($expression);
            verify($expression->getDependencies())->equals([$m]);
        });

        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('it throws an exception if dependency does not implement ModuleInterface',
                function ($value) {
                    $expression = new JsExpression();
                    $expression->setDependencies([$value]);
                }, [
                    'examples' => [
                        [[]],
                        ['dep'],
                        [false],
                        [$this]
                    ]
                ]);
        });
    }

    public function testRender()
    {
        $this->specify('it passes self-reference to renderer and returns render JS string', function () {
            $renderer = Stub::makeEmpty('ischenko\yii2\jsloader\JsRendererInterface', [
                'renderJsExpression' => Expected::once(function ($expression) {
                    return $expression->getExpression();
                })
            ], $this);

            $expression = new JsExpression('test');

            verify($expression->render($renderer))->equals('test');
        });
    }
}
