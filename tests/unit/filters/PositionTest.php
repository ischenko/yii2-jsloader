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
        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\base\Filter');
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
            [1, Stub::makeEmpty('ischenko\yii2\jsloader\ModuleInterface', ['getOptions' => []]), false],
            [1, Stub::makeEmpty('ischenko\yii2\jsloader\ModuleInterface', ['getOptions' => ['position' => 1]]), true],
        ];
    }
}
