<?php
namespace SaTan\Think\Auth\Contracts;

use SaTan\Think\Auth\Contracts\Guard;
use SaTan\Think\Auth\Contracts\StatefulGuard;

/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:32
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

interface AuthFactory
{
    /**
     * 按名称获取一个guard实例。
     *
     * @param  string|null  $name
     * @return Guard|StatefulGuard
     */
    public function guard(?string $name = null);

    /**
     * 设置工厂应提供的默认防护装置。
     *
     * @param  string  $name
     * @return void
     */
    public function shouldUse(string $name);
}