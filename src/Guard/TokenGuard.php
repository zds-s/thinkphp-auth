<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/29
 * @createTime: 0:38
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Guard;

use think\Request;
use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\Guard;
use SaTan\Think\Auth\Contracts\UserProvider;
use think\helper\Str;

class TokenGuard implements Guard
{
    use GuardHelpers;

    /**
     * 请求实例。
     *
     * @var Request
     */
    protected Request $request;

    /**
     * 来自包含API令牌的请求的查询字符串项的名称。
     *
     * @var string
     */
    protected string $inputKey;

    /**
     * 永久存储中标记“列”的名称。
     *
     * @var string
     */
    protected string $storageKey;

    /**
     * 指示API令牌是否在存储器中进行哈希处理。
     *
     * @var bool
     */
    protected $hash = false;

    protected ?UserProvider $provider=null;

    public function __construct(
        UserProvider $provider,
        Request $request,
                     $inputKey = 'api_token',
                     $storageKey = 'api_token',
                     $hash = false)
    {
        $this->hash = $hash;
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->storageKey = $storageKey;
    }


    /**
     * 确定当前用户是否为来宾。
     *
     * @return bool
     */
    public function guest(): bool
    {
        // TODO: Implement guest() method.
    }

    /**
     * 获取当前经过身份验证的用户。
     *
     * @return Authenticate|null
     */
    public function user(): ?Authenticate
    {
        //如果我们已经检索到当前请求的用户，我们可以
        //马上还给我。我们不想在上获取用户数据
        //每次调用这个方法都会非常慢。
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (! empty($token)) {
            $user = $this->provider->retrieveByCredentials([
                $this->storageKey => $this->hash ? hash('sha256', $token) : $token,
            ]);
        }

        return $this->user = $user;
    }

    /**
     * 获取当前请求的令牌。
     *
     * @return string
     */
    public function getTokenForRequest(): string
    {
        $token = $this->request->get($this->inputKey);

        if (empty($token)) {
            $token = $this->request->param((array)$this->inputKey);
        }

        if (empty($token)) {
            $token = $this->bearerToken();
        }

        if (empty($token)) {
            $token = $this->request->server('PHP_AUTH_PW');
        }

        return $token;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * 从请求头获取承载令牌。
     *
     * @return string|null
     */
    public function bearerToken():?string
    {
        $header = $this->getRequest()->header('Authorization', '');

        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
        return null;
    }

    /**
     * 验证用户的凭据。
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }



}