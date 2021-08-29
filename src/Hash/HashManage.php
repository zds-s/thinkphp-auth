<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 21:59
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Hash;

use think\exception\InvalidArgumentException;

class HashManage extends \think\Manager
{
    protected $namespace = '\\SaTan\\Think\\Auth\\Hash\\Driver\\';

    protected array $config = [

    ];

    /**
     * @inheritDoc
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('driver');
    }

    /**
     * 获取hash配置
     * @access public
     * @param null|string $name    名称
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('hashing.' . $name, $default);
        }

        return $this->app->config->get('hashing');
    }

    /**
     * 获取驱动
     * @param string|null $name
     * @return mixed
     */
    public function driver(string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        if (is_null($name)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].',
                static::class
            ));
        }

        return $this->drivers[$name] = $this->getDriver($name);
    }

    /**
     * 获取驱动配置
     * @param string $name
     * @return mixed
     */
    protected function resolveConfig(string $name)
    {
        return $this->getConfig('bcrypt');
    }
}