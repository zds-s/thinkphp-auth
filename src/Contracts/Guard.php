<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:33
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Contracts;


interface Guard
{
    /**
     * 确定当前用户是否经过身份验证。
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * 确定当前用户是否为来宾。
     *
     * @return bool
     */
    public function guest(): bool;

    /**
     * 获取当前经过身份验证的用户。
     *
     * @return Authenticate|null
     */
    public function user(): ?Authenticate;

    /**
     * 获取当前经过身份验证的用户的ID。
     *
     * @return int|string|null
     */
    public function id();

    /**
     * 验证用户的凭据。
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool;

    /**
     * 设置当前用户。
     *
     * @param  Authenticate  $user
     * @return void
     */
    public function setUser(Authenticate $user);
}
