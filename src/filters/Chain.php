<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\filters;

use ischenko\yii2\jsloader\base\Filter;
use ischenko\yii2\jsloader\FilterInterface;

/**
 * Chain filter
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Chain implements FilterInterface
{
    const LOGICAL_OR = 0;
    const LOGICAL_AND = 1;

    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * @var integer
     */
    private $operator;

    /**
     * Filter constructor.
     * @param FilterInterface[] $filters
     * @param integer $operator
     */
    public function __construct(array $filters = [], $operator = self::LOGICAL_AND)
    {
        $this->setFilters($filters);
        $this->setOperator($operator);
    }

    /**
     * @return integer
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param integer $operator
     */
    public function setOperator($operator)
    {
        if (!in_array($operator, [self::LOGICAL_OR, self::LOGICAL_AND], true)) {
            throw new \yii\base\InvalidParamException('Operator value can be only 0 or 1');
        }

        $this->operator = $operator;
    }

    /**
     * @param FilterInterface[] $filters
     */
    public function setFilters(array $filters)
    {
        foreach ($filters as $filter) {
            if (!($filter instanceof FilterInterface)) {
                throw new \yii\base\InvalidParamException(
                    'Value must be an array of objects that implement FilterInterface'
                );
            }
        }

        $this->filters = $filters;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Performs filtering of given array
     *
     * @param array $data
     * @return array filtered data
     */
    public function filter(array $data)
    {
        $filteredData = [];

        foreach ($data as $value) {
            if ($this->match($value)) {
                $filteredData[] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * Performs checks on data
     *
     * @param mixed $data
     * @return boolean
     */
    public function match($data)
    {
        $match = false;
        $operator = $this->getOperator();

        foreach ($this->getFilters() as $filter) {
            $match = $filter->match($data);

            if ((!$match && $operator === self::LOGICAL_AND)
                || ($match && $operator === self::LOGICAL_OR)
            ) {
                break;
            }
        }

        return $match;
    }
}
