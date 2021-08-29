<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:37
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Contracts;

interface StatefulGuard extends Guard
{
    /**
     * 尝试使用给定凭据对用户进行身份验证。
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return bool
     */
    public function attempt(array $credentials = [],bool $remember = false): bool;

    /**
     * 在没有会话或cookie的情况下将用户登录到应用程序。
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = []): bool;

    /**
     * 将用户登录到应用程序。
     *
     * @param  Authenticate  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticate $user,bool $remember = false);

    /**
     * 将给定的用户ID记录到应用程序中。
     *
     * @param  mixed  $id
     * @param bool $remember
     * @return Authenticate|bool
     */
    public function loginUsingId($id, bool $remember=false);

    /**
     * 在不使用会话或cookie的情况下，将给定的用户ID登录到应用程序中。
     *
     * @param  mixed  $id
     * @return Authenticate|bool
     */
    public function onceUsingId($id);

    /**
     * 确定用户是否通过“记住我”cookie进行了身份验证。
     *
     * @return bool
     */
    public function viaRemember(): bool;

    /**
     * 将用户从应用程序中注销。
     *
     * @return void
     */
    public function logout();
}