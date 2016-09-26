<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\base;

use yii\base\Object;
use yii\base\InvalidParamException;
use ischenko\yii2\jsloader\ConfigInterface;

/**
 * Base implementation for the configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
abstract class Config extends Object implements ConfigInterface
{
    /**
     * @inheritDoc
     */
    abstract public function toArray();

    /**
     * Adds data with specified key to the configuration
     *
     * @param string $key
     * @param mixed $data
     */
    abstract protected function addData($key, $data);

    /**
     * @inheritDoc
     *
     * @throws InvalidParamException
     */
    public function addDependency($key, $depends)
    {
        if (empty($key) || !is_string($key)) {
            throw new InvalidParamException('Key must be a string and cannot be empty');
        } elseif (empty($depends) || !is_string($depends)) {
            throw new InvalidParamException('Dependency name must be a string and cannot be empty');
        }

        $this->addData('jsDeps', [$key => [$depends]]);

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidParamException
     */
    public function addFile($file, $options = [], $key = null)
    {
        if (empty($file) || !is_string($file)) {
            throw new InvalidParamException('Filename must be a string and cannot be empty');
        } elseif (!is_array($options)) {
            throw new InvalidParamException('Options value must be an array');
        } elseif ($key !== null && !is_string($key)) {
            throw new InvalidParamException('Key value must be a string or NULL');
        }

        $key = $key ?: md5($file);

        $this->addData('jsFile', [$key => [$file, $options]]);

        return $this;
    }
}
