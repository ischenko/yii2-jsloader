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
    public function addCodeBlock($code, $dependencies = [])
    {
        if (empty($code) || !is_string($code)) {
            throw new InvalidParamException('Code must be a string and cannot be empty');
        } elseif (!is_array($dependencies)) {
            throw new InvalidParamException('Dependencies value must be an array');
        }

        $this->getStorage()->codeBlocks[] = [
            'code' => $code,
            'deps' => $dependencies
        ];

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidParamException
     */
    public function addDependency($key, $depends)
    {
        if (empty($key) || !is_string($key)) {
            throw new InvalidParamException('Key must be a string and cannot be empty');
        } elseif (empty($depends) || !is_string($depends)) {
            throw new InvalidParamException('Dependency name must be a string and cannot be empty');
        }

        $this->getStorage()->depends[$key][] = $depends;

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

        $this->getStorage()->files[$key][$file] = $options;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeWith($config, $replace = false)
    {
        // TODO: Implement mergeWith() method.
    }

    /**
     * @return \ArrayObject internal storage for configuration
     */
    protected function getStorage()
    {
        if (!$this->_storage) {
            $this->_storage = new \ArrayObject([
                'files' => [],
                'depends' => [],
                'codeBlocks' => []
            ], \ArrayObject::ARRAY_AS_PROPS);
        }

        return $this->_storage;
    }
}
