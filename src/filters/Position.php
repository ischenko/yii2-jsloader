<?php
/**
 * @copyright Copyright (c) 2016 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader#readme
 */

namespace ischenko\yii2\jsloader\filters;

use ischenko\yii2\jsloader\FilterInterface;
use ischenko\yii2\jsloader\ModuleInterface;

/**
 * Position filter
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Position implements FilterInterface
{
    /**
     * @var integer
     */
    private $position;

    /**
     * Position constructor.
     * @param integer $position
     */
    public function __construct($position = null)
    {
        $this->position = $position;
    }

    /**
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Performs checks on data
     *
     * @param mixed $data
     * @return boolean
     */
    public function match($data)
    {
        if ($data instanceof ModuleInterface) {
            $data = $data->getOptions();
        }

        return isset($data['position']) && $data['position'] == $this->position;
    }
}
