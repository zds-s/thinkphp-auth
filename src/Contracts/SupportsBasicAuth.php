<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 23:03
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Contracts;

use think\Response;

interface SupportsBasicAuth
{
    /**
     * 尝试使用HTTP基本身份验证进行身份验证。
     *
     * @param  string  $field
     * @param  array  $extraConditions
     * @return Response|null
     */
    public function basic(string $field = 'email',array $extraConditions = []): ?Response;

    /**
     * 执行无状态HTTP基本登录尝试。
     *
     * @param  string  $field
     * @param  array  $extraConditions
     * @return Response|null
     */
    public function onceBasic(string $field = 'email',array $extraConditions = []): ?Response;
}