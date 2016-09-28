<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\View;

use ischenko\yii2\jsloader\LoaderInterface;

/**
 * TODO: write description
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Loader extends Object implements LoaderInterface
{
    /**
     * @var View
     */
    private $_view;

    /**
     * Loader constructor.
     *
     * @param View $view
     * @param array $config
     */
    public function __construct(View $view, array $config = [])
    {
        parent::__construct($config);

        $this->_view = $view;
    }

    /**
     * @inheritDoc
     */
    abstract public function getConfig();

    /**
     * Performs actual rendering of the JS loader
     *
     * @param array $jsCodeBlocks a list of js code blocks indexed by position
     */
    abstract protected function doRender(array $jsCodeBlocks);

    /**
     * @inheritDoc
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @inheritDoc
     */
    public function registerAssetBundle($name)
    {
        $view = $this->getView();

        if (!isset($view->assetBundles[$name])) {
            return false;
        }

        $bundle = $view->assetBundles[$name];

        if (!($bundle instanceof AssetBundle)) {
            return false;
        }

        $dependencies = [];

        foreach ($bundle->depends as $dependency) {
            if ($this->registerAssetBundle($dependency) !== false) {
                $dependencies[] = $dependency;
            }
        }

        $config = $this->getConfig();

        if ($dependencies !== []) {
            $config->addDependency($name, $dependencies);
        }

        $am = $view->getAssetManager();

        foreach ($bundle->js as $js) {
            $options = $bundle->jsOptions;

            if (!is_array($js)) {
                $config->addFile($am->getAssetUrl($bundle, $js), $options, $name);
            } else {
                $file = $am->getAssetUrl($bundle, array_shift($js));
                $config->addFile($file, ArrayHelper::merge($options, $js), $name);
            }
        }

        $bundle->js = [];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function processAssets()
    {
        $view = $this->getView();

        $codeBlockPositions = [
            View::POS_BEGIN,
            View::POS_END,
            View::POS_LOAD,
            View::POS_READY
        ];

        $jsCodeBlocks = [];

        foreach ($codeBlockPositions as $position) {
            $depends = [];
            $codeBlock = '';

            if (!empty($view->js[$position])) {
                $codeBlock = implode("\n", $view->js[$position]);

                if ($position == View::POS_LOAD || $position == View::POS_READY) {
                    $depends[] = JqueryAsset::className();

                    if ($position == View::POS_LOAD) {
                        $codeBlock = "jQuery(window).load(function () {\n{$codeBlock}\n});";
                    }

                    if ($position == View::POS_READY) {
                        $codeBlock = "jQuery(document).ready(function () {\n{$codeBlock}\n});";
                    }
                }

                unset($view->js[$position]);
            }

            if (!empty($view->jsFiles[$position])) {
                foreach ($view->jsFiles[$position] as $jsFile) {
                    if (preg_match('/src=(["\\\'])(.*?)\1/', $jsFile, $m_)) {
                        $depends[] = $m_[2];
                    }
                }

                unset($view->jsFiles[$position]);
            }

            if (empty($codeBlock) && empty($depends)) {
                continue;
            }

            $jsCodeBlocks[$position] = [
                'code' => $codeBlock,
                'depends' => $depends
            ];
        }

        $this->doRender($jsCodeBlocks);
    }

    /**
     * @inheritDoc
     */
    public function setConfig($config)
    {
        \Yii::configure($this->getConfig(), $config);
    }
}
