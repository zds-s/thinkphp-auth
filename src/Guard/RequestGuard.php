<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/29
 * @createTime: 0:53
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Guard;

use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\UserProvider;
use SaTan\Think\Auth\Traits\Macroable;
use think\Request;

class RequestGuard implements \SaTan\Think\Auth\Contracts\Guard
{

    use GuardHelpers,Macroable;
    /**
     * 卫兵回调。
     *
     * @var callable
     */
    protected $callback;

    /**
     * 请求实例。
     *
     * @var Request
     */
    protected Request $request;

    /**
     * 创建一个新的身份验证保护。
     *
     * @param  callable  $callback
     * @param  Request  $request
     * @param  UserProvider|null  $provider
     * @return void
     */
    public function __construct(callable $callback, Request $request, UserProvider $provider = null)
    {
        $this->request = $request;
        $this->callback = $callback;
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     */
    public function user():?Authenticate
    {
        //如果我们已经检索到当前请求的用户，我们可以
        //马上还给我。我们不想在上获取用户数据
        //每次调用这个方法都会非常慢。
        if (! is_null($this->user)) {
            return $this->user;
        }

        return $this->user = call_user_func(
            $this->callback, $this->request, $this->getProvider()
        );
    }

    /**
     * 验证用户的凭据。
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []):bool
    {
        return ! is_null((new static(
            $this->callback, $credentials['request'], $this->getProvider()
        ))->user());
    }
}