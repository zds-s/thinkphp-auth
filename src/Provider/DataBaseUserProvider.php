<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:50
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Provider;

use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\Hasher;
use SaTan\Think\Auth\GenericUser;
use think\db\Connection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;

class DataBaseUserProvider implements \SaTan\Think\Auth\Contracts\UserProvider
{

    /**
     * 当前系统的数据库连接。
     *
     * @var Connection
     */
    protected $conn;

    /**
     * hasher实现。
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     * 包含用户的表。
     *
     * @var string
     */
    protected string $table;

    /**
     * Create a new database user provider.
     *
     * @param  Connection  $conn
     * @param  Hasher  $hasher
     * @param  string  $table
     * @return void
     */
    public function __construct(Connection $conn, Hasher $hasher,string $table)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->hasher = $hasher;
    }

    /**
     * 通过用户的唯一标识符检索用户。
     *
     * @param mixed $identifier
     * @return Authenticate|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */

    public function retrieveById($identifier):Authenticate
    {
        $user = $this->conn->table($this->table)->where($identifier)->find();

        return $this->getGenericUser($user);
    }

    /**
     * 通过用户的唯一标识符和“记住我”标记检索用户。
     *
     * @param mixed $identifier
     * @param string $token
     * @return Authenticate|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function retrieveByToken($identifier, string $token):?Authenticate
    {
        $user = $this->getGenericUser(
            $this->conn->table($this->table)->find($identifier)
        );

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)
            ? $user : null;
    }

    /**
     * 更新存储器中给定用户的“记住我”令牌。
     *
     * @param Authenticate $user
     * @param string $token
     * @return void
     * @throws DbException
     */
    public function updateRememberToken(Authenticate $user,string $token)
    {
        $this->conn->table($this->table)
            ->where($user->getAuthIdentifierName(), $user->getAuthIdentifier())
            ->update([$user->getRememberTokenName() => $token]);
    }

    /**
     * 通过给定的凭据检索用户。
     *
     * @param array $credentials
     * @return Authenticate|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function retrieveByCredentials(array $credentials):?Authenticate
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // generic "user" object that will be utilized by the Guard instances.
        $query = $this->conn->table($this->table);

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        // Now we are ready to execute the query to see if we have an user matching
        // the given credentials. If not, we will just return nulls and indicate
        // that there are no matching users for these given credential arrays.
        $user = $query->find();

        return $this->getGenericUser($user);
    }

    /**
     * 获取通用用户。
     *
     * @param  mixed  $user
     * @return GenericUser|null
     */
    protected function getGenericUser($user): ?GenericUser
    {
        if (! is_null($user)) {
            return new GenericUser((array) $user);
        }
    }

    /**
     * 根据给定的凭据验证用户。
     *
     * @param  Authenticate  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticate $user, array $credentials):bool
    {
        return $this->hasher->check(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}