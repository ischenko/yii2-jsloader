<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

/**
 * Interface for configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
interface ConfigInterface
{
    /**
     * Builds configuration set into an array
     *
     * @return array
     */
    public function toArray();

    /**
     * Adds new module into configuration
     *
     * If passed a string a new module will be created if it does not exist yet
     *
     * @param ModuleInterface|string $module an instance of module to be added or name of a module to be created and added
     *
     * @return ModuleInterface
     */
    public function addModule($module);

    /**
     * @param string $name a name of requested module
     *
     * @return ModuleInterface|null an instance of a module or null if module not found
     */
    public function getModule($name);

    /**
     * @param FilterInterface $filter filter to be used to select modules for matching conditions
     *
     * @return ModuleInterface[] a list of registered modules
     */
    public function getModules(FilterInterface $filter = null);
}
