<?php

namespace Kit\Office\Csv;

use BadMethodCallException;
use Closure;
use Kit\Office\Traits\FormatTrait;
use Kit\Office\Traits\ExcludeTrait;
use Kit\Office\Traits\OnlyTrait;
use Kit\Office\Traits\ColumnTrait;

class Csv 
{
    // 排除筛选
    use ColumnTrait;
    // 排除筛选
    use OnlyTrait;
    // 格式化
    use FormatTrait;
    // 排除筛选
    use ExcludeTrait;

    /** @var string $filename 读取文件 */
    protected string $filename = '';

    // 构造函数不做任何处理
    public function __construct()
    {

    }

    public function filename($filename) : self
    {
        $this->filename = $filename;
        return $this;
    }

    public function json(int $flags = 0) : string
    {
        return json_encode($this->readAll(), $flags);
    }

    public function read(?string $filename = null) : array
    {
        return $this->readAll($filename);
    }

    public function readAll(?string $filename = null) : array
    {
        $this->filename = $filename ?: $this->filename;

        $data = [];
        
        $this->readFn(function($row) use(&$data) {
            $data[] = $row;
        });

        return $data;
    }


    public function readFn(Closure $fn)
    {
        if (!$this->filename || !file_exists($this->filename)) {
            throw new BadMethodCallException($this->filename.' Not Exists!', 404);
        }

        $fp = fopen($this->filename, 'r+');

        $rowId = 0;
        while ($row = fgetcsv($fp, null, ',', '"', '\\')) {
            $row = $this->call($row);
            !empty($row) and $fn($row, $rowId++);
        }

        // 关闭文件
        fclose($fp);
    }

    public function write(array $data) : self
    {
        if (count($data)) {
            $this->writeFn(function(Closure $addFn) use($data) {
                foreach($data as $row) {
                    $addFn($row);
                }
            });
        }
        return $this;
    }

    public function writeFn(Closure $fn)
    {
        if (!$this->filename) {
            throw new BadMethodCallException($this->filename.' Not Exists!', 404);
        }

        $fp = fopen($this->filename, 'w+');

        $callFn = function($row) use($fp) {
            $row = $this->call($row);
            !empty($row) and fputcsv($fp, $row);
        };

        // 回调添加数据
        $fn($callFn);

        // 关闭文件
        fclose($fp);
    }

    // ==========< protected methods >==========

    protected function call(array $row)
    {
        return $this->traitRun(__FUNCTION__, $row);
    }

    protected function traitRun(string $fnName, $row)
    {
        // 按use的顺序
        $uses = class_uses($this);
        foreach($uses as $use) {
            $use = explode('\\', $use);
            $use = end($use);
            $use = substr($use, 0, strlen($use) - strlen('Trait'));
            $use = lcfirst($use);
            $fnName = ucfirst($fnName);
            $method = "$use$fnName";
            if (is_callable([$this, $method])) {
                $row = $this->$method($row);
            }
        }
        return $row;
    }

}

