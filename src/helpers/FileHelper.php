<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\helpers;

/**
 * Class FileHelper is a helper for file manipulations
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.3
 */
class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * @param string $filename
     * @param string $ext
     * @return string
     */
    public static function removeExtension(string $filename, string $ext = '.js'): string
    {
        return preg_replace('/' . preg_quote($ext) . '(?:\?.*)?$/', '', $filename);
    }
}
