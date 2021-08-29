<?php
/**
 * @author    : Death-Satan
 * @date      : 2021/8/28
 * @createTime: 23:12
 * @company   : Death撒旦
 * @link      https://www.cnblogs.com/death-satan
 */

namespace SaTan\Think\Auth\Traits;

use BadMethodCallException;
use ReflectionClass;
use ReflectionMethod;

trait Macroable
{
    /**
     * 注册了字符串宏
     *
     * @var array
     */
    protected static array $macros = [];

    /**
     * 注册自定义宏。
     *
     * @param  string  $name
     * @param  object|callable  $macro
     * @return void
     */
    public static function macro(string $name, $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * 将另一个对象混合到类中。
     *
     * @param  object  $mixin
     * @param  bool  $replace
     * @return void
     *
     */
    public static function mixin($mixin, $replace = true)
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || ! static::hasMacro($method->name)) {
                $method->setAccessible(true);
                static::macro($method->name, $method->invoke($mixin));
            }
        }
    }

    /**
     * 检查宏是否已注册。
     *
     * @param  string  $name
     * @return bool
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * 动态处理对类的调用。
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * 动态处理对类的调用。
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }
}