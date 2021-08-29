<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/29
 * @createTime: 0:07
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth;

use think\helper\Str;

class Recaller
{
    /**
     * “recaller”/“记住我”cookie字符串。
     *
     * @var string
     */
    protected $recaller;

    /**
     * 创建一个新的recaller实例。
     *
     * @param  string  $recaller
     * @return void
     */
    public function __construct(string $recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
    }

    /**
     * 从调用程序获取用户ID。
     *
     * @return string
     */
    public function id(): string
    {
        return explode('|', $this->recaller, 3)[0];
    }

    /**
     * 从重铸者那里获得“记住标记”标记。
     *
     * @return string
     */
    public function token(): string
    {
        return explode('|', $this->recaller, 3)[1];
    }

    /**
     * 从重铸器获取密码。
     *
     * @return string
     */
    public function hash(): string
    {
        return explode('|', $this->recaller, 3)[2];
    }

    /**
     * 确定重铸器是否有效。
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->properString() && $this->hasAllSegments();
    }

    /**
     * 确定重写器是否为无效字符串。
     *
     * @return bool
     */
    protected function properString(): bool
    {
        return is_string($this->recaller) && Str::contains($this->recaller, '|');
    }

    /**
     * 确定重铸器是否具有所有分段。
     *
     * @return bool
     */
    protected function hasAllSegments(): bool
    {
        $segments = explode('|', $this->recaller);

        return count($segments) === 3 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }
}