<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/29
 * @createTime: 1:25
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Traits;

trait Authenticatable
{
    /**
     * “记住我”标记的列名。
     *
     * @var string
     */
    protected string $rememberTokenName = 'remember_token';

    /**
     * 获取用户的唯一标识符的名称。
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getPk();
    }

    /**
     * 获取用户的唯一标识符。
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * 获取用户的密码。
     *
     * @return string
     */
    public function getAuthPassword():string
    {
        return $this->password;
    }

    /**
     * 获取“记住我”会话的令牌值。
     *
     * @return string|null
     */
    public function getRememberToken():string
    {
        if (! empty($this->getRememberTokenName())) {
            return (string) $this->{$this->getRememberTokenName()};
        }
    }

    /**
     * 设置“记住我”会话的令牌值。
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken(string $value)
    {
        if (! empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }

    /**
     * 获取“记住我”标记的列名。
     *
     * @return string
     */
    public function getRememberTokenName():string
    {
        return $this->rememberTokenName;
    }
}