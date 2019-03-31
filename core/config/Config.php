<?php

namespace weikit\core;

use ArrayAccess;
use yii\base\Component;
use weikit\core\config\ConfigInterface;

class Config extends Component implements ConfigInterface, ArrayAccess
{
    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return get_option($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set($key, $value = null)
    {
        update_option($key, $value);
    }

    /**
     * @param $key
     *
     * @return void
     */
    public function delete($key)
    {
        delete_option($key);
    }

    /**
     * @return array
     */
    public function all()
    {
        return wp_load_alloptions();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }
}