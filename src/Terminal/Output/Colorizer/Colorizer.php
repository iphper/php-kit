<?php

namespace Kit\Terminal\Output\Colorizer;

use InvalidArgumentException;

class Colorizer
{
    private ?array $foreRgb = null;   // 前景色 RGB [r,g,b]
    private ?array $backRgb = null;   // 背景色 RGB

    /**
     * 设置前景色（自动识别参数类型）
     *
     * @param int|string|array $rOrHexOrArray  三个整数 / 十六进制字符串 / RGB数组
     * @param int|null $g
     * @param int|null $b
     * @return self
     */
    public function fg($rOrHexOrArray, ?int $g = null, ?int $b = null): self
    {
        $this->foreRgb = $this->toRgb($rOrHexOrArray, $g, $b);
        return $this;
    }

    /**
     * 设置背景色（参数规则同 fg）
     *
     * @param int|string|array $rOrHexOrArray
     * @param int|null $g
     * @param int|null $b
     * @return self
     */
    public function bg($rOrHexOrArray, ?int $g = null, ?int $b = null): self
    {
        $this->backRgb = $this->toRgb($rOrHexOrArray, $g, $b);
        return $this;
    }

    /**
     * 重置颜色
     *
     * @return self
     */
    public function reset(): self
    {
        $this->foreRgb = null;
        $this->backRgb = null;
        return $this;
    }

    /**
     * 输出带 ANSI 颜色码的文本
     *
     * @param string $text
     * @return string
     */
    public function text(string $text): string
    {
        if ($this->foreRgb === null && $this->backRgb === null) {
            return $text;
        }

        $parts = [];
        if ($this->foreRgb !== null) {
            $parts[] = "38;2;{$this->foreRgb[0]};{$this->foreRgb[1]};{$this->foreRgb[2]}";
        }
        if ($this->backRgb !== null) {
            $parts[] = "48;2;{$this->backRgb[0]};{$this->backRgb[1]};{$this->backRgb[2]}";
        }

        return "\033[" . implode(';', $parts) . "m{$text}\033[0m";
    }

    // ========== 内部辅助 ==========

    /**
     * 将不同格式的颜色输入统一转为 RGB 数组
     *
     * @param int|string|array $first
     * @param int|null $second
     * @param int|null $third
     * @return array [r,g,b]
     * @throws InvalidArgumentException
     */
    private function toRgb($first, ?int $second = null, ?int $third = null): array
    {
        // 情形1: 三个整数
        if (is_int($first) && $second !== null && $third !== null) {
            return $this->clamp([$first, $second, $third]);
        }

        // 情形2: 十六进制字符串
        if (is_string($first)) {
            return $this->hexToRgb($first);
        }

        // 情形3: 包含三个整数的数组
        if (is_array($first) && count($first) === 3) {
            return $this->clamp($first);
        }

        throw new InvalidArgumentException('Invalid color: need 3 ints, hex string, or [r,g,b] array');
    }

    /**
     * 十六进制字符串转 RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $len = strlen($hex);

        if ($len === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        } elseif ($len !== 6) {
            throw new InvalidArgumentException("Invalid hex: $hex, expected 3 or 6 chars");
        }

        $int = hexdec($hex);
        return [
            ($int >> 16) & 0xFF,
            ($int >> 8) & 0xFF,
            $int & 0xFF,
        ];
    }

    /**
     * 将 RGB 每个分量限制在 0~255
     */
    private function clamp(array $rgb): array
    {
        return [
            max(0, min(255, $rgb[0])),
            max(0, min(255, $rgb[1])),
            max(0, min(255, $rgb[2])),
        ];
    }
}

