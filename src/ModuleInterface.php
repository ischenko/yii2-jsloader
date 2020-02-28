<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

/**
 * Interface for a module
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
interface ModuleInterface extends DependencyAwareInterface
{
    /**
     * @return string a name associated with a module
     */
    public function getName(): string;

    /**
     * Sets alias name for a module
     *
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias($alias): ModuleInterface;

    /**
     * @return string an alias for a module or name (see [[getName()]]) if alias not set
     */
    public function getAlias(): string;

    /**
     * @return string base URL for a module
     */
    public function getBaseUrl(): string;

    /**
     * Adds JS file into a module
     *
     * @param string $file URL of a file
     * @param array $options options for given file
     *
     * @return $this
     */
    public function addFile($file, $options = []): ModuleInterface;

    /**
     * @return array a list of files and their options, indexed by filename
     */
    public function getFiles(): array;

    /**
     * Clears all files from a module
     *
     * @return $this
     */
    public function clearFiles(): ModuleInterface;

    /**
     * @param array $options options for a module
     * @return $this
     */
    public function setOptions(array $options): ModuleInterface;

    /**
     * @return array a list of assigned options
     */
    public function getOptions(): array;
}
