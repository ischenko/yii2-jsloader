<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

/**
 * Filter interface
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
interface FilterInterface
{
    /**
     * Performs checks on single data entity
     *
     * @param mixed $data
     * @return boolean
     */
    public function match($data): bool;

    /**
     * Performs filtering of given array
     *
     * @param array $data
     * @return array filtered data
     */
    public function filter(array $data): array;
}
