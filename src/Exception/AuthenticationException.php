<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 23:06
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Exception;

class AuthenticationException extends \think\Exception
{
    /**
     * 所有被检查过的警卫。
     *
     * @var array
     */
    protected array $guards;

    /**
     * 用户应重定向到的路径。
     *
     * @var string
     */
    protected string $redirectTo;

    /**
     * 创建新的身份验证异常。
     *
     * @param  string  $message
     * @param  array  $guards
     * @param  string|null  $redirectTo
     * @return void
     */
    public function __construct($message = 'Unauthenticated.', array $guards = [], $redirectTo = null)
    {
        parent::__construct($message);

        $this->guards = $guards;
        $this->redirectTo = $redirectTo;
    }

    /**
     * Get the guards that were checked.
     *
     * @return array
     */
    public function guards(): array
    {
        return $this->guards;
    }

    /**
     * Get the path the user should be redirected to.
     *
     * @return string
     */
    public function redirectTo(): ?string
    {
        return $this->redirectTo;
    }
}