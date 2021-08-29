<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:43
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Contracts;

interface UserProvider
{
    /**
     * 通过用户的唯一标识符检索用户。
     *
     * @param  mixed  $identifier
     * @return Authenticate|null
     */
    public function retrieveById($identifier): ?Authenticate;

    /**
     * 通过用户的唯一标识符和“记住我”标记检索用户。
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return Authenticate|null
     */
    public function retrieveByToken($identifier,string $token): ?Authenticate;

    /**
     * 更新存储器中给定用户的“记住我”令牌。
     *
     * @param  Authenticate  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticate $user,string $token);

    /**
     * 通过给定的凭据检索用户。
     *
     * @param  array  $credentials
     * @return Authenticate|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticate;

    /**
     * 根据给定的凭据验证用户。
     *
     * @param  Authenticate  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticate $user, array $credentials): bool;
}