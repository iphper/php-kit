<?php

namespace Kit\Terminal\Output\Table;

use Kit\Common\Traits\Property;

/**
 * @class Table
 * @description A class for rendering formatted tables in terminal output.
 * @author iphper
 * @date 2024-06
 */
class Table
{
    use Property;

    /** @var string 标题 */
    protected string $caption = '';

    protected string $border = '=';
    protected string $rowBorder = '-';
    protected string $cellBorder = '|';

    /** @var array 表头 */
    protected array $header = [];
    /** @var array 表体 */
    protected array $body = [];
    /** @var array 表尾 */
    protected array $footer = [];

    // 表格宽度，自动计算或手动设置
    protected int $width = 0;

    // 内容边距
    protected int $padding = 1;

    // 每列宽度
    private array $columnWidths = [];

    // 表尾每列宽度
    private array $footerColumnWidths = [];

    // 初始化数据，确保表头、表体、表尾都是二维数组
    protected function renderInit()
    {
        // 初始化一些数据
        foreach (['header', 'body', 'footer'] as $section) {
            $this->$section = count($this->$section) === count($this->$section, COUNT_RECURSIVE) ? [$this->$section] : $this->$section;
        }
    }

    // 计算表格宽度
    protected function getTableWidth(): int
    {

        // 标题两边各有1个空格，表格两边各有4个边框符
        $captionWitdh = strlen($this->caption) + ($this->padding + 4) * 2;

        // 列数 以表头为准
        $cols = count($this->header);

        // 每列宽度记录 以表头内容宽度为基础，后续根据表体内容调整
        // 每列宽度统计
        foreach ([...$this->header, ...$this->body] as $row) {
            foreach ($row as $col => $content) {
                $w = strlen($content) + ($this->padding * 2) * $cols + ($cols + 1);
                $this->columnWidths[$col] = max($this->columnWidths[$col] ?? 0, $w);
            }
        }

        // 遍历脚部宽度
        foreach ($this->footer as $row) {
            foreach ($row as $col => $content) {
                $w = strlen($content) + ($this->padding * 2) * $cols + ($cols + 1);
                $this->footerColumnWidths[$col] = max($this->footerColumnWidths[$col] ?? 0, $w);
            }
        }

        // 表格宽度
        $this->width = $this->width > 0 ? $this->width : max($captionWitdh, array_sum($this->columnWidths) + 1, array_sum($this->footerColumnWidths) + 1);

        // 按表格宽度调整每列宽度
        foreach ($this->columnWidths as $col => $w) {
            $this->columnWidths[$col] = floor($w / array_sum($this->columnWidths) * $this->width);
        }
        // 最后一列等于宽度减去前面所有列宽度，避免因为四舍五入导致的宽度不足
        $lastCol = array_key_last($this->columnWidths);
        $this->columnWidths[$lastCol] = $this->width - 1 - array_sum(array_slice($this->columnWidths, 0, -1));
        
        foreach ($this->footerColumnWidths as $col => $w) {
            $this->footerColumnWidths[$col] = floor($w / array_sum($this->footerColumnWidths) * $this->width);
        }
        // 最后一列等于宽度减去前面所有列宽度，避免因为四舍五入导致的宽度不足
        $lastCol = array_key_last($this->footerColumnWidths);
        $this->footerColumnWidths[$lastCol] = $this->width - 1 - array_sum(array_slice($this->footerColumnWidths, 0, -1));
        return $this->width;
    }

    // 渲染表格边框
    protected function renderBorder()
    {
        $border = str_repeat($this->border, $this->getTableWidth());
        echo $border . PHP_EOL;
    }

    // 渲染标题，标题居中显示
    protected function renderCaption()
    {
        if ($this->caption) {
            echo $this->renderColumnContent('< ' . $this->caption . ' >', $this->getTableWidth(), $this->border), PHP_EOL;
            return;
        }

        return $this->renderBorder();
    }

    // 渲染行边框
    protected function renderRowBorder()
    {
        $border = str_repeat($this->rowBorder, $this->getTableWidth());
        echo $border . PHP_EOL;
    }

    // 渲染列内容，内容居中显示
    protected function renderColumnContent($content, $width, $char = ' ')
    {
        $cLen = $this->strlen($content);
        $left = floor(($width - $cLen) / 2);
        $right = $width - $cLen - $left;
        return str_repeat($char, $left) . $content . str_repeat($char, $right);
    }

    // 渲染行
    protected function renderRow(array $row, array $columnWidths = [])
    {
        empty($columnWidths) && ($columnWidths = $this->columnWidths);

        foreach ($row as $col => $content) {
            echo $this->cellBorder;
            echo $this->renderColumnContent($content, $columnWidths[$col] - 1);
        }

        echo $this->cellBorder, PHP_EOL;
    }
    
    // 渲染表头
    protected function renderHeader()
    {
        if ($this->header) {
            foreach($this->header as $row) {
                $this->renderRow($row);
            }
            $this->renderRowBorder();
        }
    }

    // 渲染表体
    protected function renderBody()
    {
        foreach ($this->body as $row) {
            $this->renderRow($row);
        }
    }

    // 渲染表尾
    protected function renderFooter()
    {
        if ($this->footer) {
            $this->renderRowBorder();
            foreach ($this->footer as $row) {
                $this->renderRow($row, $this->footerColumnWidths);
            }
        }
    }

    /**
     * 计算混合字符串长度，英文字符长度=1，中文字符长度=2
     *
     * @param string $str 输入字符串（UTF-8编码）
     * @return int 计算后的长度
     */
    protected function strlen(string $str): int
    {
        $len = mb_strlen($str, 'UTF-8'); // 总字符数（每个字符计1）
        $chineseCount = 0;
        // 遍历每个字符，统计中文字符数量
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            // 判断是否为中文（CJK统一表意文字，范围：4E00-9FFF）
            if (preg_match('/[\x{4e00}-\x{9fff}]/u', $char)) {
                $chineseCount++;
            }
        }
        // 最终长度 = 总字符数 + 中文字符数（因为每个中文要多加1）
        return $len + $chineseCount;
    }

    // 渲染表格
    public function render()
    {
        $this->renderInit();

        $this->renderCaption();
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
        $this->renderBorder();
    }

    // 直接输出表格字符串
    public function __toString()
    {
        return $this->render();
    }

    // 设置表格标题
    public function setCaption(string $caption): self
    {
        $this->caption = $caption;
        return $this;
    }

    // 设置表头
    public function setHeader(array $header): self
    {
        $this->header = $header;
        return $this;
    }

    // 设置表体
    public function setBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    // 设置表尾
    public function setFooter(array $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    // ==========< 静态方法 >==========
    /**
     * 静态方法直接输出表格字符串
     */
    public static function output(array $properties = [])
    {
        return (new static($properties))->render();
    }

}
