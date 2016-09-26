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
     * @var \ArrayObject
     */
    private $_storage;

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        // TODO: Implement toArray() method.
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
     * @inheritDoc
     */
    protected function addData($key, $data)
    {
        $storage = $this->getStorage();

        switch ($key) {
            case 'jsCode':
                $storage->jsCode[] = $data;
                break;

            case 'jsDeps':
                $data = array_map(function ($deps) {
                    return ['deps' => (array)$deps];
                }, $data);

                $this->setShim($data);
                break;

            case 'jsFile':
                $paths = $this->getPaths();

                foreach ($data as $key => $data_) {
                    $jsFile = array_shift($data_);
                    $jsFile = rtrim($jsFile, '.js');

                    if (!isset($paths[$key])) {
                        $paths[$key] = $jsFile;
                        continue;
                    }

                    // move file from paths to shim
                    $this->setShim([$key => [
                        'deps' => (array)$paths[$key]]
                    ]);

                    $paths[$key] = $jsFile;
                }

                $this->setPaths($paths, false);
                break;
        }
    }

    /**
     * @return \ArrayObject an object that used as internal storage
     */
    protected function getStorage()
    {
        if (!$this->_storage) {
            $this->_storage = new \ArrayObject([
                'jsCode' => []
            ], \ArrayObject::ARRAY_AS_PROPS);
        }

        return $this->_storage;
    }
}
