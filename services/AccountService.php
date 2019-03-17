<?php

namespace weikit\services;

use Yii;
use weikit\models\Account;
use weikit\core\service\BaseService;

class AccountService extends BaseService
{
    /**
     * 当前管理账号seesion键值
     */
    const SESSION_MANAGE_ACCOUNT = 'session_manage_account';
    /**
     * @var string
     */
    public $modelClass = Account::class;

    /**
     * @param int $uniacid
     *
     * @return Account|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByUniacid($uniacid, array $options = [])
    {
        return $this->findBy(['uniacid' => $uniacid], $options);
    }

    /**
     * 当前正在管理的账号
     *
     * @param Account|int $modelOrUniacid
     * @return Account
     */
    public function manage($modelOrUniacid)
    {
        $model = $modelOrUniacid instanceof Account ? $modelOrUniacid : $this->findByUniacid($modelOrUniacid);
        Yii::$app->session->set(self::SESSION_MANAGE_ACCOUNT, $model->uniacid);
        return $model;
    }

    /**
     * 获取正在管理的账号
     *
     * @return Account|null
     */
    public function managing()
    {   // TODO cache, last manage
        $uniacid = Yii::$app->session->get(self::SESSION_MANAGE_ACCOUNT);
        return $uniacid ? $this->findByUniacid($uniacid, [
            'exception' => false
        ]) : null;
    }
}