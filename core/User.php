<?php
namespace weikit\core;

class User extends \yii\web\User
{
    public $loginUrl = '/wp-login.php';
    public $identityClass = false;
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

    /**
     * Returns the identity object associated with the currently logged-in user.
     * When [[enableSession]] is true, this method may attempt to read the user's authentication data
     * stored in session and reconstruct the corresponding identity object, if it has not done so before.
     * @param bool $autoRenew whether to automatically renew authentication status if it has not been done so before.
     * This is only useful when [[enableSession]] is true.
     * @return IdentityInterface|null the identity object associated with the currently logged-in user.
     * `null` is returned if the user is not logged in (not authenticated).
     */
    public function getIdentity($autoRenew = true)
    {
        if ($this->_identity === false) {
            $this->_identity = wp_get_current_user();
        }
        return $this->_identity;
    }
}