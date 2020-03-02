<?php

namespace ischenko\yii2\jsloader\tests\unit\helpers;

use Codeception\Test\Unit;
use ischenko\yii2\jsloader\helpers\FileHelper;

class FileHelperTest extends Unit
{
    /**
     * @dataProvider extensionsDataProvider
     */
    public function testExtensionRemoval($filename, $ext, $expected)
    {
        verify(FileHelper::removeExtension($filename, $ext))->equals($expected);
    }

    public function extensionsDataProvider()
    {
        return [
            ['url/test.js', '.js', 'url/test'],
            ['url/test.js', '.ts', 'url/test.js'],
            ['url/test.js?v=123123', '.js', 'url/test']
        ];
    }
}
