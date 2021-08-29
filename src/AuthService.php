<?php
namespace SaTan\Think\Auth;

use SaTan\Think\Auth\Hash\HashManage;
use think\App;

class AuthService extends \SaTan\Think\BaseService
{
    public function register(): void
    {
        //迁移文件
        $this->move_files();
        //注册服务
        $this->registerContainer();
    }

    /**
     * @return HashManage
     */
    protected function createHashManage():HashManage
    {
        return new HashManage($this->app);
    }

    /**
     * @return HashManage
     */
    protected function getHashManage():HashManage
    {
        if (!$this->app->has('hash.manage'))
        {
            $this->app->bind('hash.manage',$this->createHashManage());
        }
        return $this->app->get('hash.manage');
    }

    protected function createHash()
    {
        $driver = $this->app->config->get('hashing.driver','bcrypt');
        return $this->app->get('hash.manage')->driver($driver);
    }

    protected function createAuthManage():callable
    {
        return function (App $app){
            return new AuthManage($app);
        };
    }

    protected function createAuthDriver():callable
    {
        return function (App $app){
            return $app->get('auth')->guard();
        };
    }

    protected function registerContainer()
    {
        $this->app->bind('hash.manage',$this->createHashManage());
        $this->app->bind('hash.make',$this->createHash());

        $this->app->bind('auth',$this->createAuthManage());
        $this->app->bind('auth.driver',$this->createAuthDriver());
    }

    /**
     * 迁移文件
     */
    protected function move_files()
    {
        $this->publishes([
            __DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'auth.php'
            =>config_path().'auth.php',
            __DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'hashing.php'
            =>config_path().'hashing.php',
            __DIR__.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'20210828171308_create_users_table.php'
            =>root_path('database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR).'20210828171308_create_users_table.php',
            __DIR__.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'Users.template'
            =>app_path('model').'Users.php'
        ],'auth');
    }
}