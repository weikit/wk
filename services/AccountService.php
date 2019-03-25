<?php

namespace weikit\services;

use Yii;
use weikit\models\Account;
use weikit\core\service\BaseService;

class AccountService extends BaseService
{
    /**
     * @var Account
     */
    private $_managing = false;
    /**
     * @var int
     */
    private $_managingUniacid;
    /**
     * 当前管理账号seesion键值
     */
    const SESSION_MANAGE_ACCOUNT = 'session_manage_account';
    /**
     * @var string
     */
    public $modelClass = Account::class;

    /**
     * @return int
     */
    public function getManagingUniacid()
    {
        if ($this->_managingUniacid) {
            $this->_managingUniacid = Yii::$app->session->get(self::SESSION_MANAGE_ACCOUNT, 0);
        }
        return $this->_managingUniacid;
    }

    /**
     * @param Account $managing
     */
    public function setManagingUniacid($uniacid)
    {
        Yii::$app->session->set(self::SESSION_MANAGE_ACCOUNT, $uniacid);
        $this->_managingUniacid = $uniacid;
    }

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
        $this->setManagingUniacid($model->uniacid);
        return $this->_managing = $model;
    }

    /**
     * 获取正在管理的账号
     *
     * @return Account|null
     */
    public function managing()
    {   // TODO cache, last manage
        if ($this->_managing === false) {
            $uniacid = $this->getManagingUniacid();
            $this->_managing = $uniacid ? $this->findByUniacid($uniacid, [
                'exception' => false
            ]) : null;
        }
        return $this->_managing;
    }
}