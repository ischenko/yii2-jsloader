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
    public function getConfig(): ConfigInterface;

    /**
     * Sets new configuration for the loader
     *
     * @param ConfigInterface|array $config
     * @return $this
     */
    public function setConfig($config): LoaderInterface;

    /**
     * Performs processing of assets registered in the view
     *
     * @return void
     */
    public function processAssets(): void;

    /**
     * Start processing asset bundles with further publish
     * @since 1.3
     */
    public function processBundles(): void;
}
