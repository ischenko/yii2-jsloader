<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

/**
 * Module dependency aware interface
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.3
 */
interface DependencyAwareInterface
{
    /**
     * @return ModuleInterface[] a list of dependencies of an object
     */
    public function getDependencies(): array;

    /**
     * Adds dependency to a module
     *
     * @param ModuleInterface $depends an instance of another module which will is being added as dependency
     *
     * @return $this
     */
    public function addDependency(ModuleInterface $depends): DependencyAwareInterface;

    /**
     * @param ModuleInterface[] $dependencies
     *
     * @return $this
     */
    public function setDependencies(array $dependencies): DependencyAwareInterface;

    /**
     * Clears all dependencies from an object
     *
     * @return $this
     */
    public function clearDependencies(): DependencyAwareInterface;
}
