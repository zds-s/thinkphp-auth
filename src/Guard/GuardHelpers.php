<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 23:04
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Guard;

use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\UserProvider;
use SaTan\Think\Auth\Exception\AuthenticationException;

trait GuardHelpers
{
    /**
     * 当前经过身份验证的用户。
     *
     * @var null|Authenticate
     */
    protected ?Authenticate $user=null;

    /**
     * 用户提供程序实现。
     *
     * @var ?UserProvider
     */
    protected ?UserProvider $provider=null;

    /**
     * 确定当前用户是否经过身份验证。如果不是，抛出一个异常。
     *
     * @return Authenticate
     *
     * @throws AuthenticationException
     */
    public function authenticate(): Authenticate
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException;
    }

    /**
     * 确定防护是否有用户实例。
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return ! is_null($this->user);
    }

    /**
     * 确定当前用户是否经过身份验证。
     *
     * @return bool
     */
    public function check(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * 确定当前用户是否为来宾。
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * 获取当前经过身份验证的用户的ID。
     *
     * @return int|string|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * 设置当前用户。
     *
     * @param  Authenticate  $user
     * @return $this
     */
    public function setUser(Authenticate $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * 获取警卫使用的用户提供程序。
     *
     * @return UserProvider
     */
    public function getProvider(): UserProvider
    {
        return $this->provider;
    }

    /**
     * 设置警卫使用的用户提供程序。
     *
     * @param  UserProvider  $provider
     * @return void
     */
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }
}