<?php

namespace ischenko\yii2\jsloader\tests\unit;

use Codeception\Util\Stub;
use ischenko\yii2\jsloader\RequireJs;
use yii\web\View;

class RequireJsTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \ischenko\yii2\jsloader\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();
    }

    protected function mockLoader()
    {
        return Stub::construct('ischenko\yii2\jsloader\RequireJs', [$this->tester->mockView()]);
    }

    /** Tests go below */

    public function testInstance()
    {
        $loader = $this->mockLoader();

        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\base\Loader');
        verify($loader)->isInstanceOf('ischenko\yii2\jsloader\LoaderInterface');
    }

    public function testConfigGetter()
    {
        $loader = $this->mockLoader();
        $config = $loader->getConfig();

        verify($config)->notEmpty();
        verify($config)->isInstanceOf('ischenko\yii2\jsloader\ConfigInterface');
        verify($config)->isInstanceOf('ischenko\yii2\jsloader\requirejs\Config');
        verify($config)->same($loader->getConfig());
    }
}
