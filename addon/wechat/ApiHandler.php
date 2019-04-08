<?php

namespace weikit\addon\wechat;

use yii\base\BaseObject;
use weikit\models\Account;

class ApiHandler extends BaseObject
{
    /**
     * @var Account
     */
    protected $account;

    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        parent::__construct($config);
    }

}