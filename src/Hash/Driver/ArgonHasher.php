<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 22:12
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Hash\Driver;

use RuntimeException;

class ArgonHasher extends \SaTan\Think\Auth\Hash\AbstractHasher implements \SaTan\Think\Auth\Contracts\Hasher
{

    /**
     * 默认内存成本系数。
     *
     * @var int
     */
    protected $memory = 1024;

    /**
     * 默认的时间成本系数。
     *
     * @var int
     */
    protected $time = 2;

    /**
     * 默认线程系数。
     *
     * @var int
     */
    protected $threads = 2;

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
        $this->time = $options['time'] ?? $this->time;
        $this->memory = $options['memory'] ?? $this->memory;
        $this->threads = $options['threads'] ?? $this->threads;
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
        $hash = @password_hash($value, $this->algorithm(), [
            'memory_cost' => $this->memory($options),
            'time_cost' => $this->time($options),
            'threads' => $this->threads($options),
        ]);

        if (! is_string($hash)) {
            throw new RuntimeException('Argon2 hashing not supported.');
        }

        return $hash;
    }

    /**
     * 获取应该用于散列的算法。
     *
     * @return int
     */
    protected function algorithm():int
    {
        return PASSWORD_ARGON2I;
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
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'argon2i') {
            throw new RuntimeException('This password does not use the Argon2i algorithm.');
        }

        return parent::check($value, $hashedValue, $options);
    }

    /**
     * 检查是否已使用给定选项对给定哈希进行哈希。
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash(string $hashedValue, array $options = []):bool
    {
        return password_needs_rehash($hashedValue, $this->algorithm(), [
            'memory_cost' => $this->memory($options),
            'time_cost' => $this->time($options),
            'threads' => $this->threads($options),
        ]);
    }

    /**
     * 设置默认密码内存系数。
     *
     * @param  int  $memory
     * @return $this
     */
    public function setMemory(int $memory):self
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * 设置默认密码定时系数。
     *
     * @param  int  $time
     * @return $this
     */
    public function setTime(int $time):self
    {
        $this->time = $time;

        return $this;
    }

    /**
     * 设置默认密码系数。
     *
     * @param  int  $threads
     * @return $this
     */
    public function setThreads(int $threads):self
    {
        $this->threads = $threads;

        return $this;
    }

    /**
     * 从选项数组中提取内存开销值。
     *
     * @param  array  $options
     * @return int
     */
    protected function memory(array $options):int
    {
        return $options['memory'] ?? $this->memory;
    }

    /**
     * 从选项数组中提取时间成本值。
     *
     * @param  array  $options
     * @return int
     */
    protected function time(array $options):int
    {
        return $options['time'] ?? $this->time;
    }

    /**
     * 从选项数组中提取线程的值。
     *
     * @param  array  $options
     * @return int
     */
    protected function threads(array $options):int
    {
        return $options['threads'] ?? $this->threads;
    }
}