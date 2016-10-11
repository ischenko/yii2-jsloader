<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

/**
 * JS loader interface
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
interface LoaderInterface
{
    /**
     * @return \yii\web\View the view object associated with the loader
     */
    public function getView();

    /**
     * @return ConfigInterface an object that implements configuration interface
     */
    public function getConfig();

    /**
     * Sets new configuration for the loader
     *
     * @param ConfigInterface|array $config
     * @return $this
     */
    public function setConfig($config);

    /**
     * Performs processing of assets registered in the view
     *
     * @return void
     */
    public function processAssets();

    /**
     * Registers asset bundle in the loader
     *
     * @param string $name
     *
     * @return ModuleInterface|false an instance of registered module or false if asset bundle was not registered
     */
    public function registerAssetBundle($name);
}
