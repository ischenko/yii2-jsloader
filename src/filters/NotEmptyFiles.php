<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\filters;

use ischenko\yii2\jsloader\base\Filter;
use ischenko\yii2\jsloader\ModuleInterface;

/**
 * NotEmptyFiles filter
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class NotEmptyFiles extends Filter
{
    /**
     * Performs checks on data
     *
     * @param mixed $data
     * @return boolean
     */
    public function match($data)
    {
        if (!($data instanceof ModuleInterface)) {
            return false;
        }

        $files = $data->getFiles();

        return !empty($files);
    }
}
