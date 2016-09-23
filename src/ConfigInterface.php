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
     * Merges current configuration set with given
     *
     * @param ConfigInterface|array $config configuration set to merge with
     * @param boolean $replace if true current configuration set will be replaced instead of merge
     *
     * @return $this
     */
    public function mergeWith($config, $replace = false);

    /**
     * Adds JS file into configuration
     *
     * @param string $file filename or URL to be added
     * @param array $options options for given file
     * @param string|null $key section name (normally it is a bundle name), value can be NULL which means that it is standalone file
     *
     * @return $this
     */
    public function addFile($file, $options = [], $key = null);

    /**
     * Adds JS code block into configuration
     *
     * @param string $code content of JS code to be added
     * @param array $dependencies a list of dependencies for the block
     */
    public function addCodeBlock($code, $dependencies = []);

    /**
     * Adds dependency for section
     *
     * @param string $key section name to add dependency to
     * @param string $depends dependency name that is being added
     *
     * @return $this
     */
    public function addDependency($key, $depends);
}
