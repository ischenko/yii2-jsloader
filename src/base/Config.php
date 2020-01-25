<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use ischenko\yii2\jsloader\ConfigInterface;
use ischenko\yii2\jsloader\FilterInterface;
use ischenko\yii2\jsloader\ModuleInterface;
use yii\base\BaseObject;

/**
 * Base implementation for the configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Config extends BaseObject implements ConfigInterface
{
    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var ModuleInterface[]
     */
    private $modules = [];

    /**
     * Builds configuration set into an array
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Sets aliases for modules
     *
     * @param array $aliases a list of aliases, where keys are modules name and value is an alias
     *
     * @return $this
     */
    public function setAliases(array $aliases)
    {
        foreach ($aliases as $name => $alias) {
            if (!($module = $this->getModule($name))) {
                $module = $this->addModule($name);
            }

            $module->setAlias($alias);
        }

        return $this;
    }

    /**
     * Adds new module into configuration
     *
     * If passed a string a new module will be created if it does not exist yet
     *
     * @param ModuleInterface|string $module an instance of module to be added or name of a module to be created and added
     *
     * @return ModuleInterface
     */
    public function addModule($module)
    {
        if (!($module instanceof ModuleInterface)) {
            $module = new Module($module);
        }

        return ($this->modules[$module->getName()] = $module);
    }

    /**
     * @param string $name a name of requested module
     *
     * @return ModuleInterface|null an instance of a module or null if module not found
     */
    public function getModule($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }
    }

    /**
     * @param FilterInterface $filter filter to be used to select modules for matching conditions
     *
     * @return ModuleInterface[] a list of registered modules
     */
    public function getModules(FilterInterface $filter = null)
    {
        if ($filter !== null) {
            return $filter->filter($this->modules);
        }

        return $this->modules;
    }
}
