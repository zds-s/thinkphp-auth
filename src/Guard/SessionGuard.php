<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 23:02
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Guard;

use RuntimeException;
use SaTan\Think\Auth\Contracts\Authenticate;
use SaTan\Think\Auth\Contracts\StatefulGuard;
use SaTan\Think\Auth\Contracts\SupportsBasicAuth;
use SaTan\Think\Auth\Contracts\UserProvider;
use SaTan\Think\Auth\Event\Attempting;
use SaTan\Think\Auth\Event\Authenticated;
use SaTan\Think\Auth\Event\Failed;
use SaTan\Think\Auth\Event\Login;
use SaTan\Think\Auth\Event\Logout;
use SaTan\Think\Auth\Event\Validated;
use SaTan\Think\Auth\Exception\UnauthorizedHttpException;
use SaTan\Think\Auth\Recaller;
use SaTan\Think\Auth\Traits\Macroable;
use tauthz\exception\Unauthorized;
use think\Cookie;
use think\Event;
use think\helper\Str;
use think\Request;
use think\Response;
use think\Session;

class SessionGuard implements StatefulGuard, SupportsBasicAuth
{
    use GuardHelpers,Macroable;
    /**
     * 卫兵的名字。通常是“网络”。
     *
     * 对应于身份验证配置中的保护名称。
     *
     * @var string
     */
    protected string $name;

    /**
     * 我们上次尝试检索的用户。
     *
     * @var Authenticate
     */
    protected Authenticate $lastAttempted;

    /**
     * 指示用户是否通过recaller cookie进行了身份验证。
     *
     * @var bool
     */
    protected bool $viaRemember = false;

    /**
     * 警卫使用的会话。
     *
     * @var Session
     */
    protected Session $session;

    /**
     * cookie创建者服务。
     *
     * @var Cookie
     */
    protected Cookie $cookie;

    /**
     * 请求实例。
     *
     * @var Request
     */
    protected Request $request;

    /**
     * 事件调度程序实例。
     *
     * @var Event
     */
    protected Event $events;

    /**
     * 指示是否已调用注销方法。
     *
     * @var bool
     */
    protected bool $loggedOut = false;

    /**
     * @param Event $events
     */
    public function setEvents(Event $events): void
    {
        $this->events = $events;
    }

    /**
     * @param Cookie $cookie
     */
    public function setCookie(Cookie $cookie): void
    {
        $this->cookie = $cookie;
    }

    /**
     * @param Request|null $request
     */
    public function setRequest(?Request $request): void
    {
        $this->request = $request;
    }


    /**
     * 指示是否尝试了令牌用户检索。
     *
     * @var bool
     */
    protected bool $recallAttempted = false;

    /**
     * 新的session警卫
     * @param string $name
     * @param UserProvider $userProvider
     * @param Session $session
     * @param Request|null $request
     */
    public function __construct(string $name,
                                UserProvider $userProvider,
                                Session $session,
                                Request $request=null)
    {
        $this->name = $name;
        $this->provider = $userProvider;
        $this->session = $session;
        $this->request = $request;

    }

    /**
     * 将给定的用户ID记录到应用程序中。
     *
     * @param mixed $id
     * @param bool $remember
     * @return Authenticate|bool
     */
    public function loginUsingId($id, bool $remember = false)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    /**
     * 在没有会话或cookie的情况下将用户登录到应用程序。
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = []): bool
    {
        $this->fireAttemptEvent($credentials);

        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    /**
     * 确定用户是否通过“记住我”cookie进行了身份验证。
     *
     * @return bool
     */
    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }

    /**
     * 在不使用会话或cookie的情况下，将给定的用户ID登录到应用程序中。
     *
     * @param mixed $id
     * @return Authenticate|bool
     */
    public function onceUsingId($id)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }

        return false;
    }

    /**
     * 尝试使用给定凭据对用户进行身份验证。
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        $this->fireFailedEvent($user, $credentials);

        return false;
    }

    /**
     * 使用给定参数触发失败的身份验证尝试事件。
     *
     * @param  Authenticate|null  $user
     * @param  array  $credentials
     * @return void
     */
    protected function fireFailedEvent(?Authenticate $user, array $credentials)
    {
        if (isset($this->events)) {
            $this->events->trigger(Failed::class,
                [$this->name, $user, $credentials]
            );
        }
    }

    /**
     *  使用参数触发尝试事件。
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return void
     */
    protected function fireAttemptEvent(array $credentials,bool $remember = false)
    {
        if (isset($this->events)) {
            $this->events->trigger(Attempting::class,
                [$this->name, $credentials, $remember]
            );
        }
    }

    /**
     * 将用户从应用程序中注销。
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        if (! is_null($this->user) && ! empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }

        //如果我们有一个事件调度器实例，我们可以触发注销事件
        //因此，可以进行任何进一步的处理。这允许开发人员
        //监听用户手动注销此应用程序的任何时间。
        if (isset($this->events)) {
            $this->events->trigger(Logout::class,[$this->name, $user]);
        }

        //一旦启动注销事件，我们将清除内存中的用户
        //因此，它们不再可用，因为用户不再被视为
        //正在登录到此应用程序，此处不可用。
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * 从会话和cookie中删除用户数据。
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        $this->session->delete($this->getName());

        if (! is_null($this->recaller())) {
            $this->getCookieJar()->delete($this->getRecallerName());
        }
    }

    /**
     * 获取身份验证会话值的唯一标识符。
     *
     * @return string
     */
    public function getName(): string
    {
        return 'login_'.$this->name.'_'.sha1(static::class);
    }

    /**
     * 使用给定ID更新会话。
     *
     * @param  string  $id
     * @return void
     */
    protected function updateSession(string $id)
    {
        $this->session->delete($this->getName());
        $this->session->set($this->getName(),$id);
    }

    /**
     * 刷新用户的“记住我”令牌。
     *
     * @param  Authenticate  $user
     * @return void
     */
    protected function cycleRememberToken(Authenticate $user)
    {
        $user->setRememberToken($token = Str::random(60));

        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * 为用户创建一个新的“记住我”令牌（如果还不存在）。
     *
     * @param  Authenticate  $user
     * @return void
     */
    protected function ensureRememberTokenIsSet(Authenticate $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * 将recaller cookie排入cookie罐。
     *
     * @param  Authenticate  $user
     * @return void
     */
    protected function queueRecallerCookie(Authenticate $user)
    {
        $this->createRecaller(
            $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword()
        );
    }

    /**
     * 获取用于存储“recaller”的cookie的名称。
     *
     * @return string
     */
    public function getRecallerName(): string
    {
        return 'remember_'.$this->name.'_'.sha1(static::class);
    }

    /**
     * 为给定ID创建“记住我”cookie。
     *
     * @param  string  $value
     * @return Cookie
     */
    protected function createRecaller(string $value): Cookie
    {
        $cookie = $this->getCookieJar();
        $cookie->forever($this->getRecallerName(), $value);
        return $cookie;
    }

    /**
     * 获取守卫使用的cookie创建者实例。
     *
     * @return Cookie
     *
     * @throws RuntimeException
     */
    public function getCookieJar(): Cookie
    {
        if (! isset($this->cookie)) {
            throw new RuntimeException('Cookie jar has not been set.');
        }

        return $this->cookie;
    }

    /**
     * 将用户登录到应用程序。
     *
     * @param Authenticate $user
     * @param bool $remember
     * @return void
     */
    public function login(Authenticate $user, bool $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        //如果应用程序永久“记住”用户，我们将
        //将包含用户加密副本的永久cookie排入队列
        //标识符。稍后我们将对此进行解密以检索用户。
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);

            $this->queueRecallerCookie($user);
        }

        //如果我们设置了一个事件调度器实例，我们将触发一个事件，以便
        //任何侦听器都将钩住身份验证事件并运行操作
        //基于从guard实例触发的登录和注销事件。
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * 如果设置了dispatcher，则触发登录事件。
     *
     * @param  Authenticate  $user
     * @param  bool  $remember
     * @return void
     */
    protected function fireLoginEvent(Authenticate $user, $remember = false)
    {
        if (isset($this->events)) {
            $this->events->trigger(Login::class, [$this->name, $user, $remember]);
        }
    }

    /**
     * 获取当前经过身份验证的用户。
     *
     * @return Authenticate|null
     */
    public function user():?Authenticate
    {
        if ($this->loggedOut) {
            return null;
        }

        //如果我们已经检索到当前请求的用户，我们可以
        //马上还给我。我们不想在上获取用户数据
        //每次调用这个方法都会非常慢。
        if (! is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        //首先，如果需要，我们将尝试在会话中使用标识符加载用户
        //一个存在。否则，我们将检查此文件中的“记住我”cookie
        //请求，如果存在，则尝试使用该请求检索用户。
        if (! is_null($id) && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }

        //如果用户为空，但我们解密了一个“recaller”cookie，我们可以尝试
        //拉取该cookie上的用户数据，该cookie用作
        //应用程序。一旦我们有了一个用户，我们就可以将其返回给调用者。
        if (is_null($this->user) && ! is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());

                $this->fireLoginEvent($this->user, true);
            }
        }

        return $this->user;
    }

    /**
     * 通过“记住我”cookie令牌从存储库中提取用户。
     *
     * @param Recaller $recaller
     * @return mixed
     */
    protected function userFromRecaller(Recaller $recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return null;
        }

        //如果用户为空，但我们解密了一个“recaller”cookie，我们可以尝试
        //拉取该cookie上的用户数据，该cookie用作
        //应用程序。一旦我们有了一个用户，我们就可以将其返回给调用者。
        $this->recalatten=true;

        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token()
        ));

        return $user;
    }

    /**
     * 获取请求的解密的recaller cookie。
     *
     * @return Recaller|null
     */
    protected function recaller()
    {
        if (is_null($this->request)) {
            return null;
        }

        if ($recaller = $this->request->cookie($this->getRecallerName())) {
            return new Recaller($recaller);
        }
    }

    /**
     * 如果设置了dispatcher，则触发已验证事件。
     *
     * @param  Authenticate  $user
     * @return void
     */
    protected function fireAuthenticatedEvent(Authenticate $user)
    {
        if (isset($this->events)) {
            $this->events->trigger(Authenticated::class,
                [$this->name, $user]
            );
        }
    }

    /**
     * 验证用户的凭据。
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * 确定用户是否与凭据匹配。
     *
     * @param  mixed  $user
     * @param array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, array $credentials):bool
    {
        $validated = ! is_null($user) && $this->provider->validateCredentials($user, $credentials);

        if ($validated) {
            $this->fireValidatedEvent($user);
        }

        return $validated;
    }

    /**
     * 如果已设置dispatcher，则激发已验证事件。
     *
     * @param  Authenticated  $user
     * @return void
     */
    protected function fireValidatedEvent(Authenticated $user)
    {
        if (isset($this->events)) {
            $this->events->trigger(Validated::class,
                [$this->name, $user]
            );
        }
    }

    /**
     * 尝试使用HTTP基本身份验证进行身份验证。
     *
     * @param string $field
     * @param array $extraConditions
     * @return Response|null
     */
    public function basic(string $field = 'email', array $extraConditions = []): ?Response
    {
        if ($this->check()) {
            return null;
        }

        //如果在HTTP基本请求上设置了用户名，那么我们将返回，而不带用户名
        //中断请求生命周期。否则，我们需要生成一个
        //指示给定凭据对登录无效的请求。
        if ($this->attemptBasic($this->getRequest(), $field, $extraConditions)) {
            return null;
        }

        return $this->failedBasicResponse();
    }

    /**
     * Attempt to authenticate using basic authentication.
     *
     * @param  Request  $request
     * @param string $field
     * @param array $extraConditions
     * @return bool
     */
    protected function attemptBasic(Request $request, string $field, array $extraConditions = []): bool
    {
        if (! $this->getRequest()->header('PHP_AUTH_USER')) {
            return false;
        }

        return $this->attempt(array_merge(
            $this->basicCredentials($request, $field), $extraConditions
        ));
    }

    /**
     * 获取HTTP基本请求的凭据阵列。
     *
     * @param  Request  $request
     * @param  string  $field
     * @return array
     */
    protected function basicCredentials(Request $request,string $field):array
    {
        return [$field => $request->header('PHP_AUTH_USER'), 'password' => $request->header('PHP_AUTH_PW')];
    }

    /**
     * @return Authenticate
     */
    public function getUser(): Authenticate
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * 执行无状态HTTP基本登录尝试。
     *
     * @param string $field
     * @param array $extraConditions
     * @return Response|null
     */
    public function onceBasic(string $field = 'email', array $extraConditions = []): ?Response
    {
        $credentials = $this->basicCredentials($this->getRequest(), $field);

        if (! $this->once(array_merge($credentials, $extraConditions))) {
            return $this->failedBasicResponse();
        }
    }
    /**
     * 获取基本身份验证的响应。
     *
     * @return void
     *
     * @throws UnauthorizedHttpException
     */
    protected function failedBasicResponse()
    {
        throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
    }

}