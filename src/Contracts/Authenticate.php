<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:34
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Contracts;

interface Authenticate
{
    /**
     * 获取用户的唯一标识符的名称。
     *
     * @return string
     */
    public function getAuthIdentifierName(): string;

    /**
     * 获取用户的唯一标识符。
     *
     * @return mixed
     */
    public function getAuthIdentifier();

    /**
     * 获取用户的密码。
     *
     * @return string
     */
    public function getAuthPassword(): string;

    /**
     * 获取“记住我”会话的令牌值。
     *
     * @return string
     */
    public function getRememberToken(): string;

    /**
     * 设置“记住我”会话的令牌值。
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken(string $value);

    /**
     * 获取“记住我”标记的列名。
     *
     * @return string
     */
    public function getRememberTokenName(): string;
}