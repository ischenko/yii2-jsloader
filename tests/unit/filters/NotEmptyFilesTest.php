<?php

namespace ischenko\yii2\jsloader\tests\unit\filters;

use ischenko\yii2\jsloader\filters\NotEmptyFiles as NotEmptyFilesFilter;

class NotEmptyFilesTest extends \Codeception\Test\Unit
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

    /** Tests go below */

    public function testInstance()
    {
        $filter = new NotEmptyFilesFilter();

        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\FilterInterface');
        verify($filter)->isInstanceOf('ischenko\yii2\jsloader\base\Filter');
    }

    public function testMatch()
    {
        $empty = $this->tester->mockModuleInterface(['getFiles' => []]);
        $notEmpty = $this->tester->mockModuleInterface(['getFiles' => ['file' => []]]);

        $filter = new NotEmptyFilesFilter();

        verify($filter->match($empty))->false();
        verify($filter->match($notEmpty))->true();

        verify($filter->match('test'))->false();
    }
}
