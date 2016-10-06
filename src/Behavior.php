<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\View;

/**
 * The Behavior is used to process asset bundles registered within a view.
 *
 * It listens two events:
 *  - [[\yii\web\View::EVENT_END_BODY]] used to register of asset bundles from the view in the JS loader
 *  - [[\yii\web\View::EVENT_END_PAGE]] used to process other assets registered in the view and perform bootstrap of the JS loader
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 *
 * @property View $owner
 *
 * TODO: write docs for methods
 */
class Behavior extends \yii\base\Behavior
{
    /**
     * @var LoaderInterface
     */
    private $_loader = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            View::EVENT_END_PAGE => 'processAssets',
            View::EVENT_END_BODY => 'processBundles'
        ];
    }

    /**
     * Starts processing of assets by the loader
     */
    public function processAssets()
    {
        $this->getLoader()->processAssets();
    }

    /**
     * Registers asset bundles in the JS loader
     */
    public function processBundles()
    {
        $loader = $this->getLoader();
        $view = $this->ensureView($this->owner);

        foreach (array_keys($view->assetBundles) as $name) {
            $loader->registerAssetBundle($name);
        }
    }

    /**
     * @return LoaderInterface
     *
     * @throws InvalidConfigException
     */
    public function getLoader()
    {
        if (is_array($this->_loader)) {
            $this->_loader = \Yii::createObject($this->_loader, [$this->ensureView($this->owner)]);
        }

        if (!($this->_loader instanceof LoaderInterface)) {
            throw new InvalidConfigException("Unable to instantiate new loader please check configuration");
        }

        return $this->_loader;
    }

    /**
     * @param LoaderInterface|array $loader
     *
     * @throws InvalidParamException
     */
    public function setLoader($loader)
    {
        if (!is_array($loader) && !($loader instanceof LoaderInterface)) {
            throw new InvalidParamException("Argument should be an array or implement LoaderInterface");
        }

        $this->_loader = $loader;
    }

    /**
     * Ensures that passed object is an instance of \yii\web\View
     *
     * @param mixed $object
     *
     * @return View
     * @throws InvalidConfigException
     */
    protected function ensureView($object)
    {
        if (!($object instanceof View)) {
            throw new InvalidConfigException(
                "'" . View::className() . "' instance expected, got '"
                . get_class($object) . "'"
            );
        }

        return $object;
    }
}
