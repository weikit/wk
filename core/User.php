<?php
namespace weikit\core;

use yii\base\Component;

class User extends Component
{
    private $_identity = false;

    /**
     * Returns a value that uniquely represents the user.
     * @return string|int the unique identifier for the user. If `null`, it means the user is a guest.
     * @see getIdentity()
     */
    public function getId()
    {
        if ($this->getIsGuest()) {
            return null;
        }

        return $this->getIdentity()->ID;
    }

    /**
     * Returns a value indicating whether the user is a guest (not authenticated).
     * @return bool whether the current user is a guest.
     * @see getIdentity()
     */
    public function getIsGuest()
    {
        return !$this->getIdentity()->exists();
    }

    public function getIdentity($autoRenew = true)
    {
        if ($this->_identity === false) {
            $this->_identity = wp_get_current_user();
        }
        return $this->_identity;
    }
}