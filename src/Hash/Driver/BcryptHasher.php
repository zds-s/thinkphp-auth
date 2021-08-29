<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 22:06
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Hash\Driver;

use RuntimeException;
use SaTan\Think\Auth\Hash\AbstractHasher;

class BcryptHasher extends AbstractHasher implements \SaTan\Think\Auth\Contracts\Hasher
{

    /**
     * 默认成本因素。
     *
     * @var int
     */
    protected $rounds = 10;

    /**
     * 指示是否执行算法检查。
     *
     * @var bool
     */
    protected $verifyAlgorithm = false;


    /**
     * 创建一个新的hasher实例。
     *
     * @param  array  $options
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->rounds = $options['rounds'] ?? $this->rounds;
        $this->verifyAlgorithm = $options['verify'] ?? $this->verifyAlgorithm;
    }

    /**
     * 散列给定的值。
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     *
     * @throws RuntimeException
     */
    public function make_hash(string $value, array $options = []):string
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);

        if ($hash === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * 根据散列检查给定的普通值。
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     *
     * @throws RuntimeException
     */
    public function check(string $value,string $hashedValue, array $options = []):bool
    {
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'bcrypt') {
            throw new RuntimeException('This password does not use the Bcrypt algorithm.');
        }

        return parent::check($value, $hashedValue, $options);
    }

    /**
     * 检查是否已使用给定选项对给定哈希进行哈希。
     *
     * @param string $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash(string $hashedValue, array $options = []):bool
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
    }

    /**
     * 设置默认密码工作系数。
     *
     * @param  int  $rounds
     * @return $this
     */
    public function setRounds($rounds): BcryptHasher
    {
        $this->rounds = (int) $rounds;

        return $this;
    }

    /**
     * 从选项数组中提取成本值。
     *
     * @param  array  $options
     * @return int
     */
    protected function  cost(array $options = []): int
    {
        return $options['rounds'] ?? $this->rounds;
    }
}