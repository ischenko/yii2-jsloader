<?php

namespace ischenko\yii2\jsloader\tests\unit\base;

use Codeception\Stub\Expected;
use Codeception\Util\Stub;

class FilterTest extends \Codeception\Test\Unit
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

    protected function mockFilter($params = [], $construct = [], $testCase = false)
    {
        $params = array_merge([
            'match' => false
        ], $params);

        return $config = Stub::construct('ischenko\yii2\jsloader\base\Filter', $construct, $params, $testCase);
    }

    /** Tests go below */

    public function testInstance()
    {
        $filter = $this->mockFilter();

        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\FilterInterface');
    }

    public function testFilter()
    {
        $data = [
            '1',
            'test',
            'hello',
            '23',
            'world'
        ];

        $filter = $this->mockFilter([
            'match' => Expected::exactly(5, function ($data) {
                return !is_numeric($data);
            })
        ]);

        verify($filter->filter($data))->equals(['test', 'hello', 'world']);
    }

    public function testValueProperty()
    {
        $position = $this->mockFilter([], [1]);

        verify($position->getValue())->equals(1);

        $position->setValue(2);

        verify($position->getValue())->equals(2);
    }
}
