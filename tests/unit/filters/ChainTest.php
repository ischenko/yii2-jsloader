<?php

namespace ischenko\yii2\jsloader\tests\unit\filters;

use Codeception\AssertThrows;
use Codeception\Specify;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use ischenko\yii2\jsloader\filters\Chain as ChainFilter;
use ischenko\yii2\jsloader\tests\UnitTester;
use stdClass;

class ChainTest extends Unit
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

    protected function mockBaseFilter($params = [], $construct = [], $testCase = false)
    {
        $params = array_merge([
            'match' => false
        ], $params);

        return $config = Stub::construct('ischenko\yii2\jsloader\base\Filter', $construct, $params, $testCase);
    }

    /** Tests go below */

    public function testInstance()
    {
        $filter = new ChainFilter();

        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\FilterInterface');
    }

    public function testFiltersProperty()
    {
        $this->assertThrows('yii\base\InvalidArgumentException', function () {
            $this->specify('it throws an exception if argument is not an array of objects that implement FilterInterface',
                function ($value) {
                    $filter = new ChainFilter();
                    $filter->setFilters($value);
                }, [
                    'examples' => [
                        [['']],
                        [[new stdClass()]]
                    ]
                ]);
        });

        $filter = new ChainFilter([$f = new ChainFilter()]);
        verify($filter->getFilters())->equals([$f]);
    }

    public function testOperatorProperty()
    {
        $this->assertThrows('yii\base\InvalidArgumentException', function() {
            $this->specify('it throws an exception if argument is invalid value', function ($value) {
                $filter = new ChainFilter();
                $filter->setOperator($value);
            }, [
                'examples' => [
                    [5],
                    [['']],
                    [[new stdClass()]]
                ]
            ]);
        });

        $filter = new ChainFilter();

        verify($filter->getOperator())->equals(ChainFilter::LOGICAL_AND);

        $filter->setOperator(ChainFilter::LOGICAL_OR);

        verify($filter->getOperator())->equals(ChainFilter::LOGICAL_OR);
    }

    /**
     * @dataProvider matchTestDataProvider
     */
    public function testMatch($filters, $operator, $data, $expected)
    {
        $filter = new ChainFilter($filters, $operator);
        verify($filter->match($data))->notNull();
        verify($filter->match($data))->equals($expected);
    }

    public function matchTestDataProvider()
    {
        return [
            [[], ChainFilter::LOGICAL_OR, 'test', false],
            [[], ChainFilter::LOGICAL_AND, 'test', false],

            [
                [
                    $this->mockBaseFilter([
                        'match' => function ($v) {
                            return $v === 'test1';
                        }
                    ]),
                    $this->mockBaseFilter([
                        'match' => function ($v) {
                            return $v === 'test2';
                        }
                    ])
                ],
                ChainFilter::LOGICAL_OR,
                'test2',
                true
            ],

            [
                [
                    $this->mockBaseFilter([
                        'match' => function ($v) {
                            return $v === 'test1';
                        }
                    ]),
                    $this->mockBaseFilter([
                        'match' => function ($v) {
                            return $v === 'test2';
                        }
                    ])
                ],
                ChainFilter::LOGICAL_AND,
                'test1',
                false
            ],

            [
                [
                    $this->mockBaseFilter([
                        'match' => function ($v) {
                            return $v === 'test1';
                        }
                    ]),
                    $this->mockBaseFilter([
                        'match' => function ($v) {
                            return $v === 'test1';
                        }
                    ])
                ],
                ChainFilter::LOGICAL_AND,
                'test1',
                true
            ],
        ];
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

        $filter = Stub::make('ischenko\yii2\jsloader\filters\Chain', [
            'match' => Expected::exactly(5, function ($data) {
                return !is_numeric($data);
            })
        ], $this);

        verify($filter->filter($data))->equals(['test', 'hello', 'world']);
    }
}
