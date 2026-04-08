<?php

namespace Kit\Terminal\Output\Colorizer;

/**
 * 颜色门面类
 * @class Facade
 * @description 颜色门面类，提供静态调用接口
 */
class Facade extends \Kit\Common\Facade\Facade
{
    protected static function accessor(): string
    {
        return Colorizer::class;
    }
}
