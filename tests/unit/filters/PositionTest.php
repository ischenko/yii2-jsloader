<?php

namespace ischenko\yii2\jsloader\tests\unit\filters;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\filters\Position as PositionFilter;

class PositionTest extends \Codeception\Test\Unit
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
        $filter = new PositionFilter(null);

        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\FilterInterface');
    }

    public function testPositionProperty()
    {
        $position = new PositionFilter(1);

        verify($position->getPosition())->equals(1);

        $position->setPosition(2);

        verify($position->getPosition())->equals(2);
    }

    /**
     * @dataProvider providerPositionsData
     */
    public function testMatch($filterValue, $data, $expected)
    {
        verify((new PositionFilter($filterValue))->match($data))->equals($expected);
    }

    public function providerPositionsData()
    {
        return [
            [1, [], false],
            [1, [1], false],
            [1, ['position' => ''], false],
            [1, ['position' => 1], true],
            [1, Stub::makeEmpty('ischenko\yii2\jsloader\ModuleInterface',['getOptions' => []]), false],
            [1, Stub::makeEmpty('ischenko\yii2\jsloader\ModuleInterface',['getOptions' => ['position' => 1]]), true],
        ];
    }
}
