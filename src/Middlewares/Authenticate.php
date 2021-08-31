<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/31
 * @createTime: 13:04
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Middlewares;

use Closure;
use SaTan\Think\Auth\AuthManage;
use think\Request;
use think\Response;

abstract class Authenticate
{
    /**
     * @var AuthManage $auth;
     */
    protected AuthManage $auth;

    public function __construct(AuthManage $auth)
    {
        $this->auth = $auth;

    }

    /**
     * 确定用户是否登录到任何给定的警卫。
     * @param Request $request
     * @param array $guards
     * @return null|Response
     */
    protected function authenticate(Request $request, array $guards): ?Response
    {
        if (empty($guards))
        {
            $guards = [null];
        }
        foreach ($guards as $guard)
        {
            if ($this->auth->guard($guard)->check())
            {
                return $this->auth->shouldUse($guard)?:null;
            }
        }
        return $this->unauthenticated($request,$guards);
    }

    /**
     * @param Request $request 当前请求类
     * @param array $guards 警卫列表
     * @return Response
     */
    abstract protected function unauthenticated(Request $request,array $guards): Response;
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure       $next
     * @return Response
     */
    public function handle(Request $request, Closure $next,...$guards):Response
    {
        if (($authenticate=$this->authenticate($request,$guards)) instanceof Response)
        {
            return $authenticate;
        }
        return $next($request);
    }
}