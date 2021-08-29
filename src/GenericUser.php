<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:58
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth;

class GenericUser implements Contracts\Authenticate
{

    /**
     * 用户的所有属性。
     *
     * @var array
     */
    protected array $attributes;

    /**
     * 创建一个新的通用用户对象。
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * 获取用户的唯一标识符的名称。
     *
     * @return string
     */
    public function getAuthIdentifierName():string
    {
        return 'id';
    }

    /**
     * 获取用户的唯一标识符。
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->attributes[$this->getAuthIdentifierName()];
    }

    /**
     * 获取用户的密码。
     *
     * @return string
     */
    public function getAuthPassword():string
    {
        return $this->attributes['password'];
    }

    /**
     * 获取“记住我”标记值。
     *
     * @return string
     */
    public function getRememberToken():string
    {
        return $this->attributes[$this->getRememberTokenName()];
    }

    /**
     * 设置“记住我”标记值。
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken(string $value)
    {
        $this->attributes[$this->getRememberTokenName()] = $value;
    }

    /**
     * 获取“记住我”标记的列名。
     *
     * @return string
     */
    public function getRememberTokenName():string
    {
        return 'remember_token';
    }

    /**
     * 动态访问用户的属性。
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->attributes[$key];
    }

    /**
     * 动态设置用户属性。
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * 动态检查是否在用户上设置了值。
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * 动态取消设置用户上的值。
     *
     * @param  string  $key
     * @return void
     */
    public function __unset(string $key)
    {
        unset($this->attributes[$key]);
    }
}