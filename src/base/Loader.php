<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use Yii;
use yii\base\Object;
use yii\web\View;
use yii\web\AssetBundle;
use yii\helpers\FileHelper;
use ischenko\yii2\jsloader\LoaderInterface;
use ischenko\yii2\jsloader\ConfigInterface;
use ischenko\yii2\jsloader\ModuleInterface;
use ischenko\yii2\jsloader\filters\Position as PositionFilter;
use ischenko\yii2\jsloader\filters\ClassName as ClassNameFilter;

/**
 * Base class for JS loaders
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Loader extends Object implements LoaderInterface
{
    const RUNTIME_DIR = '@runtime/jsloader';

    /**
     * @var View
     */
    private $view;

    /**
     * @var ClassNameFilter
     */
    private $ignoredBundles;

    /**
     * @var PositionFilter
     */
    private $ignoredPositions;

    /**
     * Loader constructor.
     *
     * @param View $view
     * @param array $config
     */
    public function __construct(View $view, array $config = [])
    {
        parent::__construct($config);

        $this->view = $view;
        $this->setIgnoreBundles([]);
        $this->setIgnorePositions([View::POS_HEAD]);
    }

    /**
     * @return \ischenko\yii2\jsloader\ConfigInterface an object that implements configuration interface
     */
    abstract public function getConfig();

    /**
     * Performs actual rendering of the JS loader
     *
     * @param array $codeBlocks a list of js code blocks indexed by position
     */
    abstract protected function doRender(array $codeBlocks);

    /**
     * @return \yii\web\View the view object associated with the loader
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param array $bundles a list of asset bundles names which should be ignored by the loader
     */
    public function setIgnoreBundles(array $bundles)
    {
        $this->ignoredBundles = new ClassNameFilter($bundles);
    }

    /**
     * @param array $positions a list of positions which should be skipped by the loader
     */
    public function setIgnorePositions(array $positions)
    {
        $this->ignoredPositions = new PositionFilter($positions);
    }

    /**
     * Sets new configuration for the loader
     *
     * @param ConfigInterface|array $config
     */
    public function setConfig($config)
    {
        $configObject = $this->getConfig();

        if (is_object($config)) {
            $config = get_object_vars($config);
        }

        foreach ((array)$config as $key => $value) {
            $configObject->$key = $value;
        }
    }

    /**
     * Registers asset bundle in the loader
     *
     * @param string $name
     *
     * @return ModuleInterface|false an instance of registered module or false if asset bundle was not registered
     */
    public function registerAssetBundle($name)
    {
        if (($bundle = $this->getAssetBundleFromView($name)) === false) {
            return $bundle;
        }

        $config = $this->getConfig();

        if (!($module = $config->getModule($name))) {
            $module = $config->addModule($name);
        }

        $module->setOptions(array_merge(['position' => View::POS_END], $bundle->jsOptions));

        foreach ($bundle->depends as $dependency) {
            if (($dependency = $this->registerAssetBundle($dependency)) !== false) {
                $module->addDependency($dependency);
            }
        }

        $bundle->js = $this->importJsFilesFromBundle($bundle, $module);

        return $module;
    }

    /**
     * Performs processing of assets registered in the view
     *
     * @return void
     */
    public function processAssets()
    {
        $codeBlocks = [];
        $config = $this->getConfig();
        $positionFilter = new PositionFilter();

        foreach ([
                     View::POS_HEAD,
                     View::POS_BEGIN,
                     View::POS_END,
                     View::POS_LOAD,
                     View::POS_READY
                 ] as $position
        ) {
            if ($this->ignoredPositions->match($position)) {
                continue;
            }

            $positionFilter->setValue($position);

            $code = $this->importJsCodeFromView($position);
            $depends = $config->getModules($positionFilter);
            $depends = array_merge($depends, $this->importJsFilesFromView($position));

            if (empty($code) && empty($depends)) {
                continue;
            }

            $codeBlocks[$position] = ['code' => $code, 'depends' => $depends];
        }

        $this->doRender($codeBlocks);
    }

    /**
     * @return string a path to runtime folder
     */
    protected function getRuntimePath()
    {
        static $runtimePath;

        if ($runtimePath === null) {
            $runtimePath = Yii::getAlias(self::RUNTIME_DIR);
            FileHelper::createDirectory($runtimePath);
        }

        return $runtimePath;
    }

    /**
     * @param integer $position
     *
     * @return array
     */
    private function importJsFilesFromView($position)
    {
        $modules = [];
        $view = $this->getView();
        $config = $this->getConfig();

        if (!empty($view->jsFiles[$position])) {
            foreach ($view->jsFiles[$position] as $jsFile) {
                if (preg_match('/src=(["\\\'])(.*?)\1/', $jsFile, $matches)) {
                    $modules[] = $config->addModule(md5($matches[2]))
                        ->addFile($matches[2], ['position' => $position]);
                }
            }

            unset($view->jsFiles[$position]);
        }

        return $modules;
    }

    /**
     * @param integer $position
     *
     * @return string
     */
    private function importJsCodeFromView($position)
    {
        $code = '';
        $view = $this->getView();

        if (!empty($view->js[$position])) {
            $code = implode("\n", $view->js[$position]);
            unset($view->js[$position]);
        }

        return $code;
    }

    /**
     * @param string $name
     *
     * @return AssetBundle|false an asset bundle from the view or false if asset bundle not found
     */
    private function getAssetBundleFromView($name)
    {
        $view = $this->getView();

        if (!isset($view->assetBundles[$name])) {
            return false;
        }

        $bundle = $view->assetBundles[$name];

        if (!($bundle instanceof AssetBundle)) {
            return false;
        }

        if ($this->ignoredBundles->match($name)
            || $this->ignoredPositions->match($bundle->jsOptions)
        ) {
            return false;
        }

        return $bundle;
    }

    /**
     * @param AssetBundle $bundle
     * @param ModuleInterface $module
     *
     * @return array a list of ignored files
     */
    private function importJsFilesFromBundle(AssetBundle $bundle, ModuleInterface $module)
    {
        $ignoredJs = [];
        $assetManager = $this->getView()->getAssetManager();

        foreach ($bundle->js as $js) {
            $file = $js;
            $options = [];

            if (is_array($js)) {
                if ($this->ignoredPositions->match($js)) {
                    $ignoredJs[] = $js;
                    continue;
                }

                $file = array_shift($js);
                $options = $js;
            }

            $module->addFile($assetManager->getAssetUrl($bundle, $file), $options);
        }

        return $ignoredJs;
    }
}
