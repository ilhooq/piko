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
 * Piko is the helper class for the Piko framework.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Piko
{
    /**
     * The application instance.
     *
     * @var Application
     */
    public static $app;

    /**
     * The registry container.
     *
     * @var array
     */
    protected static $registry = [];

    /**
     * The singletons container.
     *
     * @var array
     */
    protected static $singletons = [];

    /**
     * The aliases container.
     *
     * @var array
     */
    protected static $aliases = [];

    /**
     * Retrieve data from the registry.
     *
     * @param string $key The registry key.
     * @param mixed $default Default value if data is not found from the registry.
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $data = isset(static::$registry[$key])? static::$registry[$key] : $default;

        if (is_callable($data)) {
            return call_user_func($data);
        }

        return $data;
    }

    /**
     * Store data in the registry.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        static::$registry[$key] = $value;
    }

    /**
     * Registers a path alias.
     *
     * A path alias is a short name representing a long path (a file path, a URL, etc.)
     *
     * @param string $alias The alias name (e.g. "@web"). It must start with a '@' character.
     * @param string $path the path corresponding to the alias.
     * @return void
     * @throws \InvalidArgumentException if $path is an invalid alias.
     * @see Piko::getAlias()
     */
    public static function setAlias($alias, $path)
    {
        if ($alias{0} != '@') {
            throw new \InvalidArgumentException('Alias must start with the @ character');
        }

        static::$aliases[$alias] = $path;
    }

    /**
     * Translates a path alias into an actual path.
     *
     * @param string $alias The alias to be translated.
     * @return string|bool The path corresponding to the alias. False if the alias is not registered.
     */
    public static function getAlias($alias)
    {
        if ($alias{0} != '@') {
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
        }

        return false;
    }

    /**
     * Singleton factory method.
     *
     * @param string|array $type The object type.
     * If it is a string, it should be the fully qualified name of the class.
     * If an array given, it should contain the key 'class' with the value corresponding
     * to the fully qualified name of the class
     * @param array $properties A name-value pair array corresponding to the object public properties.
     * @return object
     */
    public static function createObject($type, $properties = [])
    {
        if (is_array($type)) {
            $properties = $type;
            $type = $properties['class'];
            unset($properties['class']);
        }

        if (!isset(static::$singletons[$type])) {
            static::$singletons[$type] = empty($properties) ? new $type() : new $type($properties);
        }

        return static::$singletons[$type];
    }

    /**
     * Configure public attributes of an object.
     *
     * @param object $object The object instance.
     * @param array $properties A name-value pair array corresponding to the object public properties.
     * @return void
     */
    public static function configureObject($object, $properties = [])
    {
        foreach ($properties as $key => $value) {
            $object->$key = $value;
        }
    }

    /**
     * Translate a text.
     * This is a shortcut to translate method in i18n component.
     *
     * @param string $domain The translation domain, for instance 'app'.
     * @param string $text The text to translate.
     * @param array $params Parameters substitution.
     *
     * @return string The translated text or the text itself if no translation was found.
     *
     * @see \piko\I18n
     */
    public static function t($domain, $text, $params = [])
    {
        $i18n = static::get('i18n');

        return is_object($i18n) ? $i18n->translate($domain, $text, $params) : $text;
    }
}
