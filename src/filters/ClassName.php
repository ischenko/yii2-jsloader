<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\filters;

use ischenko\yii2\jsloader\base\Filter;

/**
 * ClassName filter
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class ClassName extends Filter
{
    /**
     * Performs checks on data
     *
     * @param mixed $data
     * @return boolean
     */
    public function match($data): bool
    {
        if (is_object($data)) {
            $data = get_class($data);
        }

        return in_array($data, (array)$this->getValue());
    }
}
