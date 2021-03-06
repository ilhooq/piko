<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
namespace piko;

/**
 * Component class implements events and behaviors features.
 * Also component public properties can be initialized with an array of configuration during instantiation.
 *
 * Events offer the possibility to inject custom code when they are triggered.
 * Behaviors offer the possibility to add custom methods without extending the class.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Component
{
    /**
     * Behaviors container.
     *
     * @var array
     */
    public $behaviors = [];

    /**
     * Event handlers container.
     *
     * @var array
     */
    public $events = [];

    /**
     * Static event handlers container.
     *
     * @var array
     */
    public static $events2 = [];

    /**
     * Constructor
     *
     * @param array $config A configuration array to set public properties of the class.
     * @return void
     */
    public function __construct($config = [])
    {
        Piko::configureObject($this, $config);
        $this->init();
    }

    /**
     * Method called at the end of the constructor.
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Magic method to call a behavior.
     *
     * @param string $name The name of the behavior.
     * @param array $args The behavior arguments.
     * @throws \RuntimeException
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (isset($this->behaviors[$name])) {
            return call_user_func_array($this->behaviors[$name], $args);
        }

        throw new \RuntimeException("Behavior $name not registered.");
    }

    /**
     * Event registration.
     *
     * @param string $eventName The event name to register.
     * @param mixed $callback The event handler to register. Must be  one of the following:
     *                        - A Closure (function(){ ... })
     *                        - An object method ([$object, 'methodName'])
     *                        - A static class method ('MyClass::myMethod')
     *                        - A global function ('myFunction')
     * @param string $priority The order priority in the events stack ('after' or 'before'). Default to 'after'.
     *
     * @return void
     */
    public function on($eventName = '', $callback = null, $priority = 'after')
    {
        if (! isset($this->events[$eventName])) {
            $this->events[$eventName] = [];
        }

        if ($priority == 'before') {
            array_unshift($this->events[$eventName], $callback);
        } else {
            $this->events[$eventName][] = $callback;
        }
    }

    /**
     * Static event registration.
     *
     * @param string $eventName The event name to register.
     * @param mixed $callback The event handler to register. Must be  one of the following:
     *                        - A Closure (function(){ ... })
     *                        - An object method ([$object, 'methodName'])
     *                        - A static class method ('MyClass::myMethod')
     *                        - A global function ('myFunction')
     * @param string $priority The order priority in the events stack ('after' or 'before'). Default to 'after'.
     *
     * @return void
     */
    public static function when($eventName = '', $callback = null, $priority = 'after')
    {
        if (! isset(static::$events2[$eventName])) {
            static::$events2[$eventName] = [];
        }

        if ($priority == 'before') {
            array_unshift(static::$events2[$eventName], $callback);
        } else {
            static::$events2[$eventName][] = $callback;
        }
    }

    /**
     * Trigger an event.
     *
     * Event handlers corresponding to this event will be called in the order they are registered.
     *
     * @param string $eventName The event name to trigger.
     * @param array $args The event handlers arguments.
     * @return mixed[]
     */
    public function trigger($eventName = '', $args = array())
    {
        $return = [];

        if (isset($this->events[$eventName])) {
            foreach ($this->events[$eventName] as $callback) {
                $return[] = call_user_func_array($callback, $args);
            }
        }

        if (isset(static::$events2[$eventName])) {
            foreach (static::$events2[$eventName] as $callback) {
                $return[] = call_user_func_array($callback, $args);
            }
        }

        return $return;
    }

    /**
     * Attach a behavior to the component instance.
     *
     * @param string $name The behavior name.
     * @param mixed $callback The behavior implementation. Must be  one of the following:
     *                        - A Closure (function(){ ... })
     *                        - An object method ([$object, 'methodName'])
     *                        - A static class method ('MyClass::myMethod')
     *                        - A global function ('myFunction')
     * @return void
     */
    public function attachBehavior($name = '', $callback = null)
    {
        if (!isset($this->behaviors[$name])) {
            $this->behaviors[$name] = $callback;
        }
    }

    /**
     * Detach a behavior.
     *
     * @param string $name The behavior name.
     * @return void
     */
    public function detachBehavior($name = '')
    {
        if (isset($this->behaviors[$name])) {
            unset($this->behaviors[$name]);
        }
    }
}
