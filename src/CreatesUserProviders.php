<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:42
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth;

use InvalidArgumentException;
use SaTan\Think\Auth\Contracts\UserProvider;
use SaTan\Think\Auth\Provider\DataBaseUserProvider;
use SaTan\Think\Auth\Provider\ModelUserProvider;
use think\App;

trait CreatesUserProviders
{
    protected App $app;
    /**
     * 已注册的自定义提供程序创建者。
     *
     * @var array
     */
    protected array $customProviderCreators = [];

    /**
     * 为驱动程序创建用户提供程序实现。
     *
     * @param string|null $provider
     * @return UserProvider|null|void
     *
     * @throws InvalidArgumentException
     */
    public function createUserProvider(?string $provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return null;
        }

        if (isset($this->customProviderCreators[$driver = ($config['driver'] ?? null)])) {
            return call_user_func(
                $this->customProviderCreators[$driver], $this->app, $config
            );
        }

        switch ($driver) {
            case 'database':
                return $this->createDatabaseProvider($config);
            case 'model':
                return $this->createModelProvider($config);
            default:
                throw new InvalidArgumentException(
                    "Authentication user provider [{$driver}] is not defined."
                );
        }
    }

    /**
     * 获取用户提供程序配置。
     *
     * @param string|null $provider
     * @return array|null
     */
    protected function getProviderConfiguration(?string $provider): ?array
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->app->config->get('auth.providers.'.$provider);
        }
    }

    /**
     * 创建数据库用户提供程序的实例。
     *
     * @param array $config
     * @return DataBaseUserProvider
     */
    protected function createDatabaseProvider(array $config): DataBaseUserProvider
    {
        $connection = $this->app['db']->connection($config['connection'] ?? null);

        return new DatabaseUserProvider($connection, $this->app['hash'], $config['table']);
    }

    /**
     * 创建model的用户提供者的实例。
     *
     * @param array $config
     * @return ModelUserProvider
     */
    protected function createModelProvider(array $config): ModelUserProvider
    {
        return new ModelUserProvider($this->app->get('hash.make'), $config['model']);
    }

    /**
     * 获取默认的用户提供程序名称。
     *
     * @return string
     */
    public function getDefaultUserProvider(): string
    {
        return $this->app['config']['auth.defaults.provider'];
    }
}