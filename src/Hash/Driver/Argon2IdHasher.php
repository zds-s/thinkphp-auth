<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 22:17
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Hash\Driver;

use RuntimeException;

class Argon2IdHasher extends ArgonHasher
{
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
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'argon2id') {
            throw new RuntimeException('This password does not use the Argon2id algorithm.');
        }

        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * 获取应该用于散列的算法。
     *
     * @return int
     */
    protected function algorithm():int
    {
        return PASSWORD_ARGON2ID;
    }
}