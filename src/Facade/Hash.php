<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 22:20
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Facade;

use SaTan\Think\Auth\Hash\HashManage;

/**
 * @method static array info(string $hashedValue)
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static string make_hash(string $value, array $options = [])
 * @see HashManage
 */
class Hash extends \think\Facade
{
    protected static function getFacadeClass()
    {
        return HashManage::class;
    }
}