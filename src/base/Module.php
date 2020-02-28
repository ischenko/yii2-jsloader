<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use ischenko\yii2\jsloader\ModuleInterface;
use ischenko\yii2\jsloader\traits\DependencyAware;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;

/**
 * Base implementation of module
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Module extends BaseObject implements ModuleInterface
{
    use DependencyAware;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var string alias name
     */
    private $alias;

    /**
     * Module constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct([]);

        if (empty($name) || !is_string($name)) {
            throw new InvalidArgumentException('Name must be a string and cannot be empty');
        }

        $this->name = $name;
    }

    /**
     * @return string a name associated with a module
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string an alias for a module or name (see [[getName()]]) if alias not set
     */
    public function getAlias(): string
    {
        return $this->alias ?: $this->getName();
    }

    /**
     * Sets alias name for a module
     *
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias($alias): ModuleInterface
    {
        if (!is_string($alias)) {
            throw new InvalidArgumentException('Alias must be a string');
        }

        $this->alias = $alias;

        return $this;
    }

    /**
     * Adds JS file into a module
     *
     * @param string $file URL of a file
     * @param array $options options for given file
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addFile($file, $options = []): ModuleInterface
    {
        if (empty($file) || !is_string($file)) {
            throw new InvalidArgumentException('Filename must be a string and cannot be empty');
        } elseif (!is_array($options)) {
            throw new InvalidArgumentException('Options value must be an array');
        }

        $this->files[$file] = $options;

        return $this;
    }

    /**
     * @return array a list of files and their options, indexed by filename
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Clears all files from a module
     *
     * @return $this
     */
    public function clearFiles(): ModuleInterface
    {
        $this->files = [];

        return $this;
    }

    /**
     * @return array a list of assigned options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options options for a module
     * @return $this
     */
    public function setOptions(array $options): ModuleInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string base URL for a module
     */
    public function getBaseUrl(): string
    {
        return $this->options['baseUrl'] ?? '';
    }
}
