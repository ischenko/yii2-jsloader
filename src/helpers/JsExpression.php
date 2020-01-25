<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\helpers;

use ischenko\yii2\jsloader\JsRendererInterface;
use ischenko\yii2\jsloader\ModuleInterface;
use yii\base\InvalidArgumentException;

/**
 * JsExpression helper
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class JsExpression
{
    /**
     * @var string|JsExpression
     */
    private $expression;

    /**
     * @var ModuleInterface[]
     */
    private $dependencies = [];

    /**
     * JsExpression constructor.
     * @param string|JsExpression $expression
     * @param ModuleInterface[] $depends
     */
    public function __construct($expression = null, $depends = [])
    {
        if ($expression !== null) {
            $this->setExpression($expression);
        }

        $this->setDependencies($depends);
    }

    /**
     * Performs rendering JS expression
     *
     * @param JsRendererInterface $renderer
     * @return string
     */
    public function render(JsRendererInterface $renderer)
    {
        return $renderer->renderJsExpression($this);
    }

    /**
     * @param string|JsExpression $expression
     *
     * @return $this
     */
    public function setExpression($expression)
    {
        if (!is_string($expression) && !($expression instanceof JsExpression)) {
            throw new InvalidArgumentException('Expression must be a string or an instance of JsExpression');
        } elseif (($expression instanceof JsExpression) && $this === $expression) {
            throw new InvalidArgumentException('Expression cannot reference on self');
        }

        $this->expression = $expression;

        return $this;
    }

    /**
     * @param ModuleInterface[] $dependencies
     *
     * @return $this
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = [];

        foreach ($dependencies as $dependency) {
            if (!($dependency instanceof ModuleInterface)) {
                throw new InvalidArgumentException('Dependency must implement ModuleInterface');
            }

            $this->dependencies[] = $dependency;
        }

        return $this;
    }

    /**
     * @return ModuleInterface[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @return string|JsExpression
     */
    public function getExpression()
    {
        return $this->expression;
    }
}
