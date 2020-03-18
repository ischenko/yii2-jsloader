<?php

namespace ischenko\yii2\jsloader\tests\unit;

use Codeception\AssertThrows;
use Codeception\Specify;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use ischenko\yii2\jsloader\LoaderInterface;
use ischenko\yii2\jsloader\tests\UnitTester;
use ischenko\yii2\jsloader\ThemeBasedBehavior;
use yii\base\Theme;
use yii\web\View;

class ThemeBasedBehaviorTest extends Unit
{
    use AssertThrows;
    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testAliasesInitialization()
    {
        /** @var ThemeBasedBehavior $behavior */
        $behavior = $this->make(ThemeBasedBehavior::class, ['themePaths' => ['@runtime/test']]);

        verify($behavior->themePaths)->equals(['@runtime/test']);

        $behavior->init();

        verify($behavior->themePaths)->equals([\Yii::getAlias('@runtime/test')]);
    }

    public function testAssetsProcessing()
    {
        /** @var View $view */
        $view = $this->makeEmpty(View::class);

        /** @var ThemeBasedBehavior $behavior */
        $behavior = $this->make(ThemeBasedBehavior::class, [
            'owner' => $view,
            'themePaths' => ['@runtime/test'],
            'getLoader' => $this->makeEmpty(LoaderInterface::class, [
                'processAssets' => Expected::exactly(1),
                'processBundles' => Expected::exactly(1)
            ])
        ]);

        $behavior->init();

        $behavior->processAssets();
        $behavior->processBundles();

        $behavior->owner = $this->make(View::class,
            ['theme' => $this->make(Theme::class, ['getBasePath' => \Yii::getAlias('@runtime/test')])]);

        $behavior->processAssets();
        $behavior->processBundles();

        $behavior->owner = $this->make(View::class,
            ['theme' => $this->make(Theme::class, ['getBasePath' => \Yii::getAlias('@runtime/test1')])]);

        $behavior->processAssets();
        $behavior->processBundles();
    }
}
