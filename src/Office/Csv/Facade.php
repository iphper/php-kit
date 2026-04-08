<?php

namespace Kit\Office\Csv;

/**
 * Csv门面类
 * @class Facade
 * @description Csv门面类，提供静态调用接口
 */
class Facade extends \Kit\Common\Facade\Facade
{
    protected static function accessor(): string
    {
        return Csv::class;
    }
}
