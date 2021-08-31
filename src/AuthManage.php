<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:30
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth;

use Closure;
use InvalidArgumentException;
use SaTan\Think\Auth\Contracts\AuthFactory;
use SaTan\Think\Auth\Contracts\Guard;
use SaTan\Think\Auth\Contracts\StatefulGuard;
use SaTan\Think\Auth\Guard\RequestGuard;
use SaTan\Think\Auth\Guard\SessionGuard;
use SaTan\Think\Auth\Guard\TokenGuard;
use think\App;

class AuthManage implements AuthFactory
{
    use CreatesUserProviders;

    /**
     * 应用程序实例。
     *
     * @var App
     */
    protected App $app;

    /**
     * 已注册的自定义驱动程序创建者。
     *
     * @var array
     */
    protected array $customCreators = [];

    /**
     * 创建的“驱动程序”数组。
     *
     * @var array
     */
    protected array $guards = [];

    /**
     * 由各种服务共享的用户解析程序。
     *
     * 确定Gate、Request和可验证合约的默认用户。
     *
     * @var Closure
     */
    protected Closure $userResolver;

    /**
     * Create a new Auth manager instance.
     *
     * @param  App  $app
     * @return void
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->userResolver = function ($guard = null) {
            return $this->guard($guard)->user();
        };
    }

    /**
     * 尝试从本地缓存获取防护。
     *
     * @param  string|null  $name
     * @return Guard|StatefulGuard
     */
    public function guard(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * 解决给定的防护。
     *
     * @param  string  $name
     * @return Guard|StatefulGuard
     *
     * @throws InvalidArgumentException
     */
    protected function resolve(string $name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($name, $config);
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        }

        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
        );
    }

    /**
     * 调用自定义驱动程序创建者。
     *
     * @param  string  $name
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(string $name, array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $name, $config);
    }

    /**
     * 创建基于会话的身份验证保护。
     *
     * @param  string  $name
     * @param  array  $config
     * @return SessionGuard
     */
    public function createSessionDriver(string $name,array $config): SessionGuard
    {
        $provider = $this->createUserProvider($config['provider'] ?? null);

        $guard = new SessionGuard($name, $provider, $this->app->session,$this->app->request);

        //使用身份验证服务的“记住我”功能时
        //需要设置guard的加密实例，该实例允许
        //为这些cookie生成安全、加密的cookie值。
        if (method_exists($guard, 'setCookie')) {
            $guard->setCookie($this->app->cookie);
        }

        if (method_exists($guard, 'setEvents')) {
            $guard->setEvents($this->app->event);
        }

        if (method_exists($guard, 'setRequest')) {
            $guard->setRequest($this->app->request);
        }

        return $guard;
    }

    /**
     * 创建基于令牌的身份验证保护。
     *
     * @param  string  $name
     * @param  array  $config
     * @return TokenGuard
     */
    public function createTokenDriver(string $name,array $config):TokenGuard
    {
        //令牌保护实现了一个基本的基于API令牌的保护实现
        //从请求中获取API令牌字段并将其与
        //数据库中的用户或用户所在的另一个持久层。
        $guard = new TokenGuard(
            $this->createUserProvider($config['provider'] ?? null),
            $this->app->request,
            $config['input_key'] ?? 'api_token',
            $config['storage_key'] ?? 'api_token',
            $config['hash'] ?? false
        );
        return $guard;
    }

    /**
     * 获取配置。
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig(string $name): array
    {
        return $this->app->config->get("auth.guards.{$name}")?:[];
    }

    /**
     * 获取默认的身份验证驱动程序名称。
     *
     * @return string
     */
    public function getDefaultDriver():string
    {
        return $this->app->config->get('auth.defaults.guard');
    }

    /**
     * 设置出厂时应使用的默认保护驱动程序。
     *
     * @param  string  $name
     * @return void
     */
    public function shouldUse(string $name)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);

        $this->userResolver = function ($name = null) {
            return $this->guard($name)->user();
        };
    }

    /**
     * 设置默认身份验证驱动程序名称。
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver(string $name)
    {
        $this->app->config->set(
            array_merge(
                $this->app->config->get('auth.defaults'),
                [
                    'guard'=>$name,
                ]
            )
        );
    }

    /**
     * 注册一个新的基于回调的请求保护。
     *
     * @param  string  $driver
     * @param  callable  $callback
     * @return $this
     */
    public function viaRequest(string $driver, callable $callback):self
    {
        return $this->extend($driver, function () use ($callback) {
            return new RequestGuard($callback, $this->app->request, $this->createUserProvider());
        });
    }

    /**
     * 获取用户解析程序回调。
     *
     * @return Closure
     */
    public function userResolver(): Closure
    {
        return $this->userResolver;
    }

    /**
     * 设置用于解析用户的回调。
     *
     * @param  Closure  $userResolver
     * @return $this
     */
    public function resolveUsersUsing(Closure $userResolver): self
    {
        $this->userResolver = $userResolver;

        return $this;
    }

    /**
     * 注册自定义驱动程序创建者关闭。
     *
     * @param  string  $driver
     * @param  Closure  $callback
     * @return $this
     */
    public function extend(string $driver, Closure $callback):self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * 注册自定义提供程序创建者闭包。
     *
     * @param  string  $name
     * @param  Closure  $callback
     * @return $this
     */
    public function provider(string $name, Closure $callback):self
    {
        $this->customProviderCreators[$name] = $callback;

        return $this;
    }

    /**
     * 确定是否已解决任何防护。
     *
     * @return bool
     */
    public function hasResolvedGuards(): bool
    {
        return count($this->guards) > 0;
    }

    /**
     * 忘记所有已解决的guard实例。
     *
     * @return $this
     */
    public function forgetGuards():self
    {
        $this->guards = [];

        return $this;
    }

    /**
     * 设置管理器使用的应用程序实例。
     *
     * @param  App $app
     * @return $this
     */
    public function setApplication(App $app): self
    {
        $this->app = $app;

        return $this;
    }

    /**
     * 动态调用默认驱动程序实例。
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}