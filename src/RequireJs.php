<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

use ischenko\yii2\jsloader\base\Loader;
use ischenko\yii2\jsloader\requirejs\Config;

/**
 * TODO: write description
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class RequireJs extends Loader
{
    /**
     * @var Config
     */
    private $_config;

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = new Config();
        }

        return $this->_config;
    }

    /**
     * @inheritDoc
     */
    protected function doRender()
    {
        // TODO: Implement render() method.
    }
}
