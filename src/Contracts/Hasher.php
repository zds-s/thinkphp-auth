<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 19:53
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Contracts;

interface Hasher
{
    /**
     * 获取有关给定哈希值的信息。
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info(string $hashedValue): array;

    /**
     * 散列给定的值。
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make_hash(string $value, array $options = []): string;

    /**
     * 根据散列检查给定的普通值。
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check(string $value,string $hashedValue, array $options = []): bool;

    /**
     * 检查是否已使用给定选项对给定哈希进行哈希。
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash(string $hashedValue, array $options = []): bool;
}