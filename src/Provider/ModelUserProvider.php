<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 22:27
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Provider;

use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\Hasher;
use think\db\BaseQuery;
use think\helper\Str;
use think\Model;

class ModelUserProvider implements \SaTan\Think\Auth\Contracts\UserProvider
{
    /**
     * hash容器
     * @var Hasher
     */
    protected Hasher $hasher;

    /**
     * 模型
     * @var string
     */
    protected string $model;

    public function __construct(Hasher $hasher,$model)
    {
        $this->hasher = $hasher;
        $this->model = $model;
    }

    /**
     * 创建模型的新实例。
     *
     * @return Model
     */
    public function createModel():Model
    {
        return new $this->model;
    }

    /**
     * 获取模型实例的新查询生成器。
     *
     * @param  Model|null  $model
     * @return BaseQuery|Model
     */
    protected function newModelQuery(?Model $model = null)
    {
        return is_null($model)
            ? $this->createModel()->newQuery()
            : $model->newQuery();
    }

    /**
     * @inheritDoc
     */
    public function retrieveById($identifier): ?Authenticate
    {
        $model = $this->createModel();
        return $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->find();
    }

    /**
     * 获取哈希程序实现。
     *
     * @return Hasher
     */
    public function getHasher(): Hasher
    {
        return $this->hasher;
    }

    /**
     * @inheritDoc
     */
    public function retrieveByToken($identifier, string $token): ?Authenticate
    {
        $model = $this->createModel();

        $retrievedModel = $this->newModelQuery($model)->where(
            $model->getAuthIdentifierName(), $identifier
        )->find();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel : null;
    }

    /**
     * @inheritDoc
     */
    public function updateRememberToken(Authenticate $user, string $token)
    {
        $user->setRememberToken($token);

        $timestamps = $user->getAutoWriteTimestamp();

        $user->setAutoWriteTimestamp(false);

        $user->save();

        $user->setAutoWriteTimestamp($timestamps);
    }

    /**
     * 从凭证阵列获取第一个密钥。
     *
     * @param  array  $credentials
     * @return string|null
     */
    protected function firstCredentialKey(array $credentials): ?string
    {
        foreach ($credentials as $key => $value) {
            return $key;
        }
    }

    /**
     * @inheritDoc
     */
    public function retrieveByCredentials(array $credentials): ?Authenticate
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                Str::contains($this->firstCredentialKey($credentials), 'password'))) {
            return null;
        }

        //首先，我们将把每个凭证元素作为where子句添加到查询中。
        //然后我们可以执行查询，如果我们找到了一个用户，就可以在
        //将由Guard实例使用的雄辩用户“模型”。
        $query = $this->newModelQuery();

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

        return $query->find();
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials(Authenticate $user, array $credentials): bool
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}