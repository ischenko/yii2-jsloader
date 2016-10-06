<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use yii\base\Object;
use ischenko\yii2\jsloader\FilterInterface;
use ischenko\yii2\jsloader\ConfigInterface;
use ischenko\yii2\jsloader\ModuleInterface;

/**
 * Base implementation for the configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Config extends Object implements ConfigInterface
{
    /**
     * @var ModuleInterface[]
     */
    private $modules = [];

    /**
     * @inheritDoc
     */
    abstract public function toArray();

    /**
     * @inheritDoc
     */
    public function addModule($module)
    {
        if (!($module instanceof ModuleInterface)) {
            $module = new Module($module);
        }

        return ($this->modules[$module->getName()] = $module);
    }

    /**
     * @inheritDoc
     */
    public function getModule($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }
    }

    /**
     * @inheritDoc
     */
    public function getModules(FilterInterface $filter = null)
    {
        return $this->modules;
    }
}
