<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use yii\base\Object;
use yii\base\InvalidParamException;
use ischenko\yii2\jsloader\ConfigInterface;

/**
 * Base implementation for the configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Config extends Object implements ConfigInterface
{
    /**
     * @var \ArrayObject
     */
    private $_storage;

    /**
     * @inheritDoc
     */
    abstract public function toArray();

    /**
     * @inheritDoc
     *
     * @throws InvalidParamException
     */
    public function addDependency($key, $depends)
    {
        if (empty($key) || !is_string($key)) {
            throw new InvalidParamException('Key must be a string and cannot be empty');
        } elseif (!is_string($depends) && !is_array($depends)) {
            throw new InvalidParamException('Dependency name must be a string or array');
        }

        $depends = (array)$depends;
        $storage = $this->getStorage();

        if (!isset($storage->jsDeps[$key])) {
            $storage->jsDeps[$key] = $depends;
        } else {
            $storage->jsDeps[$key] = array_merge($storage->jsDeps[$key], $depends);
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidParamException
     */
    public function addFile($file, $options = [], $key = null)
    {
        if (empty($file) || !is_string($file)) {
            throw new InvalidParamException('Filename must be a string and cannot be empty');
        } elseif (!is_array($options)) {
            throw new InvalidParamException('Options value must be an array');
        } elseif ($key !== null && !is_string($key)) {
            throw new InvalidParamException('Key value must be a string or NULL');
        }

        $key = $key ?: md5($file);

        $storage = $this->getStorage();
        $jsFile = [$file => $options];

        if (!isset($storage->jsFiles[$key])) {
            $storage->jsFiles[$key] = $jsFile;
        } else {
            $storage->jsFiles[$key] = array_merge($storage->jsFiles[$key], $jsFile);
        }

        return $this;
    }

    /**
     * @return \ArrayObject an object that used as internal storage
     */
    protected function getStorage()
    {
        if (!$this->_storage) {
            $this->_storage = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        }

        return $this->_storage;
    }
}
