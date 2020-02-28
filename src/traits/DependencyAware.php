<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\traits;

use ischenko\yii2\jsloader\DependencyAwareInterface;
use ischenko\yii2\jsloader\ModuleInterface;
use yii\base\InvalidArgumentException;

/**
 * Implementation of dependency aware interface
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.3
 */
trait DependencyAware
{
    /**
     * @var ModuleInterface[]
     */
    private $dependencies = [];

    /**
     * {@inheritDoc}
     *
     * @return ModuleInterface[] a list of dependencies of an object
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * {@inheritDoc}
     *
     * @param ModuleInterface[] $dependencies
     *
     * @return $this
     */
    public function setDependencies(array $dependencies): DependencyAwareInterface
    {
        $this->dependencies = [];

        foreach ($dependencies as $dependency) {
            if (!($dependency instanceof ModuleInterface)) {
                throw new InvalidArgumentException('Dependency must implement ModuleInterface');
            }

            $this->dependencies[] = $dependency;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param ModuleInterface $depends an instance of another module which will is being added as dependency
     *
     * @return $this
     */
    public function addDependency(ModuleInterface $depends): DependencyAwareInterface
    {
        if ($depends->getFiles() === []) {
            foreach ($depends->getDependencies() as $dependency) {
                $this->addDependency($dependency);
            }

            return $this;
        }

        $this->dependencies[$depends->getName()] = $depends;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return $this
     */
    public function clearDependencies(): DependencyAwareInterface
    {
        $this->dependencies = [];
        return $this;
    }
}
