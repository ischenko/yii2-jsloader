<?php

namespace ischenko\yii2\jsloader\tests\unit\filters;

use ischenko\yii2\jsloader\filters\ClassName as ClassNameFilter;

class ClassNameTest extends \Codeception\Test\Unit
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
        $filter = new ClassNameFilter(null);

        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\FilterInterface');
        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\base\Filter');
    }

    /**
     * @dataProvider providerNamesData
     */
    public function testMatch($filterValue, $data, $expected)
    {
        verify((new ClassNameFilter($filterValue))->match($data))->equals($expected);
    }

    public function providerNamesData()
    {
        return [
            ['test', 'test', true],
            [['test1', 'test2'], 'test', false],
            [['test1', 'test2'], 'test1', true],
            [['ischenko\yii2\jsloader\filters\ClassName'], new ClassNameFilter(null), true],
        ];
    }
}
