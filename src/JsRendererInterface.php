<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

use ischenko\yii2\jsloader\helpers\JsExpression;

/**
 * JS renderer interface
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
interface JsRendererInterface
{
    /**
     * Performs rendering of js expression
     *
     * @param JsExpression $expression
     * @return string
     */
    public function renderJsExpression(JsExpression $expression);
}
