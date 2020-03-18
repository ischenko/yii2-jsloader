<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

use yii\base\Theme;
use yii\web\View;

/**
 * The ThemeBasedBehavior is used to process asset bundles registered within a view for specified themes only.
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.3.2
 *
 * @property View $owner
 */
class ThemeBasedBehavior extends Behavior
{
    /**
     * @var array a list of base paths of themese where to use JS loader
     */
    public $themePaths = [];

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();

        foreach ($this->themePaths as $index => $path) {
            $this->themePaths[$index] = \Yii::getAlias($path);
        }
    }

    /**
     * Starts processing of assets by the loader
     */
    public function processAssets()
    {
        if ($this->skipLoader()) {
            return;
        }

        parent::processAssets();
    }

    /**
     * Registers asset bundles in the JS loader
     */
    public function processBundles()
    {
        if ($this->skipLoader()) {
            return;
        }

        parent::processBundles();
    }

    /**
     * @return bool
     */
    protected function skipLoader(): bool
    {
        $view = $this->ensureView($this->owner);

        if ($view->theme instanceof Theme) {
            $basePath = $view->theme->getBasePath();

            if (isset($this->cache[$basePath])) {
                return $this->cache[$basePath];
            }

            return $this->cache[$basePath] = !in_array($basePath, $this->themePaths);
        }

        return true;
    }
}
