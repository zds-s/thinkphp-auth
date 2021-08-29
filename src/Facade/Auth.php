<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/29
 * @createTime: 1:08
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Facade;
use SaTan\Think\Auth\AuthManage;
use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\Guard;
use SaTan\Think\Auth\Contracts\StatefulGuard;
use SaTan\Think\Auth\Contracts\UserProvider;
use think\Response;

/**
 * @method static AuthManage extend(string $driver, \Closure $callback)
 * @method static AuthManage provider(string $name, \Closure $callback)
 * @method static Authenticate loginUsingId(mixed $id, bool $remember = false)
 * @method static Authenticate|null user()
 * @method static Guard|StatefulGuard guard(string|null $name = null)
 * @method static UserProvider|null createUserProvider(string $provider = null)
 * @method static Response|null onceBasic(string $field = 'email',array $extraConditions = [])
 * @method static bool attempt(array $credentials = [], bool $remember = false)
 * @method static bool check()
 * @method static bool guest()
 * @method static bool once(array $credentials = [])
 * @method static bool onceUsingId(mixed $id)
 * @method static bool validate(array $credentials = [])
 * @method static bool viaRemember()
 * @method static bool|null logoutOtherDevices(string $password, string $attribute = 'password')
 * @method static int|string|null id()
 * @method static void login(Authenticate $user, bool $remember = false)
 * @method static void logout()
 * @method static void logoutCurrentDevice()
 * @method static void setUser(Authenticate $user)
 * @method static void shouldUse(string $name);
 *
 * @see AuthManage
 * @see \AuthFactory
 * @see Guard
 * @see StatefulGuard
 */
class Auth extends \think\Facade
{
    protected static function getFacadeClass()
    {
        return 'auth';
    }
}