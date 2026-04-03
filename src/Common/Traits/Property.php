<?php

namespace Kit\Common\Traits;

trait Property
{
    // 匿名属性【传入的属性列表没有key】
    protected array $properties = [];

    public function __construct(?array $properties)
    {
        $this->setProperties($properties);

        // 初始化
        method_exists($this, 'initialize') AND $this->initialize();

    }

    public function setProperties(array $properties)
    {
        if (!empty($properties)) {
            foreach($properties as $key => $val) {
                // 使用魔术方法设置
                $this->$key = $val;
            }
        }

        return $this;
    }

    public function __set($property, $val)
    {
        if (property_exists($this, $property)) {
            $this->$property = $val;
        } else {
            $this->properties[$property] = $val;
        }
        return $this;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        if (!empty($this->properties[$property])){
            return $this->properties[$property];
        }
        return null;
    }

}
