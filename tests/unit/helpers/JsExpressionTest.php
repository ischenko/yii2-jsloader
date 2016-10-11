<?php

namespace ischenko\yii2\jsloader\tests\unit\filters;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\helpers\JsExpression;

class JsExpressionTest extends \Codeception\Test\Unit
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

        $this->specify('it throws an exception if expression is not a string or JsExpression object', function ($value) {
            $expression = new JsExpression();
            $expression->setExpression($value);
        }, ['throws' => 'yii\base\InvalidParamException', 'examples' => [
            [[]],
            [false],
            [$this]
        ]]);

        $this->specify('it throws an exception if expression is self-reference', function () {
            $expression = new JsExpression();
            $expression->setExpression($expression);
        }, ['throws' => 'yii\base\InvalidParamException']);
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

        $this->specify('it throws an exception if dependency does not implement ModuleInterface', function ($value) {
            $expression = new JsExpression();
            $expression->setDependencies([$value]);
        }, ['throws' => 'yii\base\InvalidParamException', 'examples' => [
            [[]],
            ['dep'],
            [false],
            [$this]
        ]]);
    }

    public function testRender()
    {
        $this->specify('it passes self-reference to renderer and returns render JS string', function () {
            $renderer = Stub::makeEmpty('ischenko\yii2\jsloader\JsRendererInterface', [
                'renderJsExpression' => Stub::once(function ($expression) {
                    return $expression->getExpression();
                })
            ], $this);

            $expression = new JsExpression('test');

            verify($expression->render($renderer))->equals('test');

            $this->verifyMockObjects();
        });
    }
}
