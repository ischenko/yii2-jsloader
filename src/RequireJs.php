<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader;

use ischenko\yii2\jsloader\base\Loader;
use ischenko\yii2\jsloader\requirejs\Config;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\View;

/**
 * TODO: write description
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class RequireJs extends Loader
{
    const RUNTIME_PATH = '@runtime/jsloader';

    /**
     * @var string URL to be used to load the RequireJS library. If value is empty the loader will publish library from the bower package
     */
    public $libraryUrl;

    /**
     * @var string path to the RequireJS library
     */
    public $libraryPath = '@bower/requirejs/require.js';

    /**
     * @see http://requirejs.org/docs/api.html#data-main
     *
     * @var string|false URL of script file that will be used as value for the data-main entry. FALSE means do not use the data-main entry.
     */
    public $main;

    /**
     * @var Config
     */
    private $_config;

    /**
     * @inheritDoc
     *
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = new Config();
        }

        return $this->_config;
    }

    /**
     * @inheritDoc
     */
    protected function doRender(array $jsCodeBlocks)
    {
        krsort($jsCodeBlocks);

        $jsCode = '';

        foreach ($jsCodeBlocks as $jsCodeBlock) {
            if (!isset($jsCodeBlock['code'], $jsCodeBlock['depends'])) {
                continue;
            }

            $jsCode = "{$jsCodeBlock['code']}\n{$jsCode}";
            $jsCode = $this->renderRequireBlock($jsCode, $jsCodeBlock['depends']);
        }

        $this->publishRequireJs($jsCode);
    }

    /**
     * @param string $code
     */
    protected function publishRequireJs($code)
    {
        $view = $this->getView();
        $am = $view->getAssetManager();

        if (empty($this->libraryUrl)) {
            list(, $this->libraryUrl) = $am->publish($this->libraryPath);
        }

        $requireOptions = ['position' => View::POS_END];

        if ($this->main === false) {
            $view->registerJs($code, $requireOptions['position']);
        } else {
            $mainPath = $this->writeFileContent('requirejs-main.js', $code);
            list(, $requireOptions['data-main']) = $am->publish($mainPath);
        }

        $view->registerJsFile($this->libraryUrl, $requireOptions);
        $view->registerJs($this->renderRequireConfig(), View::POS_HEAD);
    }

    /**
     * Performs rendering of configuration block for RequireJS
     *
     * @return string
     */
    protected function renderRequireConfig()
    {
        $config = $this->getConfig()->toArray();
        $config = Json::encode((object)array_filter($config));

        return str_replace(':config', $config, 'var require = :config;');
    }

    /**
     * @param string $code
     * @param array $depends
     *
     * @return string
     */
    protected function renderRequireBlock($code, array $depends)
    {
        if (empty($code) && empty($depends)) {
            return '';
        }

        $injects = [];
        $modules = [];

        foreach ($depends as $module => $inject) {
            if (is_integer($module)
                && !empty($inject)
            ) {
                $module = $inject;
                unset($inject);
            }

            if (is_string($module)) {
                $modules[] = $module;
            }

            if (!empty($inject)) {
                $injects[] = $inject;
            }
        }

        return str_replace(
            [
                ':injects',
                ':modules'
            ],
            [
                implode(',', $injects),
                implode(',', array_map(['yii\helpers\Json', 'encode'], $modules))
            ],
            "require([:modules], function(:injects) {\n{$code}\n});"
        );
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @return string full path to a file
     *
     * @throws \yii\base\Exception
     */
    private function writeFileContent($filename, $content)
    {
        static $runtimePath;

        if ($runtimePath === null) {
            FileHelper::createDirectory(($runtimePath = \Yii::getAlias(self::RUNTIME_PATH)));
        }

        $filePath = $runtimePath . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($filePath, $content, LOCK_EX) === false) {
            throw new \yii\base\Exception("Failed to write data into a file \"$filePath\"");
        }

        return $filePath;
    }
}
