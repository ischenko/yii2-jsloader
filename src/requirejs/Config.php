<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\requirejs;

use yii\helpers\ArrayHelper;
use \ischenko\yii2\jsloader\base\Config as BaseConfig;

/**
 * RequireJs-specific implementation of the configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Config extends BaseConfig
{
    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->build()->getArrayCopy();
    }

    /**
     * @see http://requirejs.org/docs/api.html#config-paths
     *
     * @param array $data
     * @param boolean $merge
     *
     * @return $this
     */
    public function setPaths(array $data, $merge = true)
    {
        $storage = $this->getStorage();

        if (!isset($storage->paths)) {
            $storage->paths = [];
        }

        $storage->paths = $merge ? ArrayHelper::merge($storage->paths, $data) : $data;

        return $this;
    }

    /**
     * @see http://requirejs.org/docs/api.html#config-paths
     *
     * @return array paths section of the configuration
     */
    public function getPaths()
    {
        $storage = $this->getStorage();

        if (!isset($storage->paths)) {
            return [];
        }

        return $storage->paths;
    }

    /**
     * @see http://requirejs.org/docs/api.html#config-shim
     *
     * @param array $data
     * @param boolean $merge
     *
     * @return $this
     */
    public function setShim(array $data, $merge = true)
    {
        $storage = $this->getStorage();

        if (!isset($storage->shim)) {
            $storage->shim = [];
        }

        $storage->shim = $merge ? ArrayHelper::merge($storage->shim, $data) : $data;

        return $this;
    }

    /**
     * @see http://requirejs.org/docs/api.html#config-shim
     *
     * @return array shim section of the configuration
     */
    public function getShim()
    {
        $storage = $this->getStorage();

        if (!isset($storage->shim)) {
            return [];
        }

        return $storage->shim;
    }

    /**
     * Builds configuration for requirejs
     *
     * @return \ArrayObject reference on internal storage
     */
    protected function build()
    {
        $storage = $this->getStorage();

        if (isset($storage->jsFiles)) {
            foreach ($storage->jsFiles as $key => $files) {
                $depends = [];

                foreach ($files as $file => $options) {
                    if (isset($storage->paths[$key])) {
                        $depends[] = $storage->paths[$key];
                    }

                    $storage->paths[$key] = preg_replace('/\.js$/', '', $file);
                }

                if ($depends !== []) {
                    $this->setShim([$key => ['deps' => $depends]]);
                }
            }

            unset($storage->jsFiles);
        }

        return $storage;
    }
}
