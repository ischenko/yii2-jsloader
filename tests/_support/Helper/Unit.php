<?php
namespace ischenko\yii2\jsloader\tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Util\Stub;

use yii\web\AssetManager;
use yii\web\View;

class Unit extends \Codeception\Module
{
    /**
     * @return View
     */
    public function mockView($params = [], $testCase = false)
    {
        return Stub::construct('yii\web\View', [], array_merge([
            'assetManager' => Stub::makeEmpty(AssetManager::className(), [
                'getAssetUrl' => function ($bundle, $asset) {
                    return $asset;
                }
            ])
        ], $params), $testCase);
    }

    /**
     * @return \ischenko\yii2\jsloader\LoaderInterface
     */
    public function mockLoaderInterface($params = [], $testCase = false)
    {
        return Stub::makeEmpty('ischenko\yii2\jsloader\LoaderInterface', $params, $testCase);
    }

    /**
     * @return \ischenko\yii2\jsloader\ModuleInterface
     */
    public function mockModuleInterface($params = [], $testCase = false)
    {
        return Stub::makeEmpty('ischenko\yii2\jsloader\ModuleInterface', $params, $testCase);
    }

    /**
     * @return \ischenko\yii2\jsloader\ConfigInterface
     */
    public function mockConfigInterface($params = [], $testCase = false)
    {
        return Stub::makeEmpty('ischenko\yii2\jsloader\ConfigInterface', $params, $testCase);
    }

    public function mockBaseLoader($params = [], $testCase = false)
    {
        $params = array_merge([
            'doRender' => null,
            'getConfig' => Stub::makeEmpty('ischenko\yii2\jsloader\ConfigInterface', ['getModules' => []])
        ], $params);

        if (!empty($params['view'])) {
            $view = $params['view'];
            unset($params['view']);
        } else {
            $view = $this->mockView();
        }

        return Stub::construct('ischenko\yii2\jsloader\base\Loader', [$view], $params, $testCase);
    }

    /**
     * Provides reflection for specific property of an object
     *
     * @param mixed $object
     * @param string $property
     *
     * @return \ReflectionProperty
     */
    public function getProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);

        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property;
    }

    /**
     * Provides reflection for a method of provided object
     *
     * @param mixed $object
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function getMethod($object, $method, $arguments = [])
    {
        $reflection = new \ReflectionClass($object);

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }
}
