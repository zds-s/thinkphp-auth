<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUsersTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('users');
        //插入一条默认数据
        $table->insert([
            'username'=>'satan',
            'avatar'=>'/static/dist/img/avatar4.png',
            'email'=>'2771717608@qq.com',
            'password'=>\SaTan\Think\Auth\Facade\Hash::make_hash('123456'),
        ]);
        $table->addColumn('username','string',['limit'=>20,'comment'=>'账户'])
            ->addColumn('avatar','string',['limit'=>255,'comment'=>'头像'])
            ->addColumn('email','string',['null'=>true,'limit'=>255,'comment'=>'邮箱'])
            ->addColumn('last_login_time','datetime',['default'=>0,'comment'=>'登录时间'])
            ->addColumn('last_login_ip','string',['default'=>'127.0.0.1','comment'=>'登录ip'])
            ->addColumn('password','string',['limit'=>254,'comment'=>'密码'])
            ->addColumn('remember_token','string',['null'=>true,'limit'=>100,'comment'=>'记住密码'])
            ->addTimestamps()
            ->addIndex(['username','email'],['unique'=>true])
            ->create();
    }
}
