<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use ischenko\yii2\jsloader\FilterInterface;

/**
 * Base class for filters
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Filter implements FilterInterface
{
    /**
     * Performs checks on single data entity
     *
     * @param mixed $data
     * @return boolean
     */
    abstract public function match($data);

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
}
