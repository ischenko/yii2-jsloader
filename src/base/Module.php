<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use yii\base\InvalidParamException;
use ischenko\yii2\jsloader\ModuleInterface;

/**
 * Base implementation of module
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Module implements ModuleInterface
{
    /**
     * @var string
     */
    private $_name;

    /**
     * @var array
     */
    private $_options = [];

    /**
     * @var array
     */
    private $_files = [];

    /**
     * @var array
     */
    private $_dependencies = [];

    /**
     * Module constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        if (empty($name) || !is_string($name)) {
            throw new InvalidParamException('Name must be a string and cannot be empty');
        }

        $this->_name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidParamException
     */
    public function addFile($file, $options = [])
    {
        if (empty($file) || !is_string($file)) {
            throw new InvalidParamException('Filename must be a string and cannot be empty');
        } elseif (!is_array($options)) {
            throw new InvalidParamException('Options value must be an array');
        }

        $this->_files[$file] = $options;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFiles($names = true)
    {
        return $names ? array_keys($this->_files) : $this->_files;
    }

    /**
     * @inheritDoc
     */
    public function clearFiles()
    {
        $this->_files = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addDependency(ModuleInterface $depends)
    {
        $this->_dependencies[] = $depends;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return $this->_dependencies;
    }

    /**
     * @inheritDoc
     */
    public function clearDependencies()
    {
        $this->_dependencies = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
