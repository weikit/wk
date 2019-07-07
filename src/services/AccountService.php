<?php

namespace weikit\services;

use Yii;
use yii\db\ActiveQuery;
use weikit\core\Service;
use weikit\models\Account;

/**
 * Class AccountService
 * @package weikit\services
 * @property int $managingUniacid
 */
class AccountService extends Service
{
    /**
     * 当前管理账号seesion键值
     */
    const SESSION_MANAGE_ACCOUNT = 'session_manage_account';
    /**
     * @var Account
     */
    private $_managing = false;
    /**
     * @var int
     */
    private $_managingUniacid;
    /**
     * @var string
     */
    public $modelClass = Account::class;

    /**
     * @return int
     */
    public function getManagingUniacid()
    {
        if ($this->_managingUniacid === null) {
            $this->_managingUniacid = Yii::$app->session->get(self::SESSION_MANAGE_ACCOUNT, 0);
        }
        return $this->_managingUniacid;
    }

    /**
     * @param int|string $uniacid
     */
    public function setManagingUniacid($uniacid)
    {
        Yii::$app->session->set(self::SESSION_MANAGE_ACCOUNT, (int) $uniacid);
        $this->_managingUniacid = $uniacid;
    }

    /**
     * @param string $hash
     * @param array $options
     *
     * @return Account|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByHash($hash, array $options = [])
    {
        return $this->findOne(['hash' => $hash], $options);
    }

    /**
     * @param int $acid
     * @param array $options
     *
     * @return Account|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByAcid($acid, array $options = [])
    {
        return $this->findOne(['a.acid' => $acid], array_merge($options, [
            'alias' => 'a'
        ]));
    }

    /**
     * @param int $uniacid
     * @param array $options
     *
     * @return Account|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByUniacid($uniacid, array $options = [])
    {
        return $this->findOne(['a.uniacid' => $uniacid], array_merge($options, [
            'alias' => 'a'
        ]));
    }

    /**
     * 设置管理账号
     *
     * @param Account|int $modelOrUniacid
     * @return Account
     */
    public function manage($modelOrUniacid)
    {
        if ($modelOrUniacid instanceof Account) {
            $this->setManagingUniacid($modelOrUniacid->uniacid);
            $this->_managing = $modelOrUniacid;
        } else {
            $this->setManagingUniacid($modelOrUniacid);
        }

        return $this->managing();
    }

    /**
     * 获取正在管理的账号
     *
     * @return Account|null
     */
    public function managing()
    {   // TODO cache last manage
        if ($this->_managing === false) {
            $uniacid = $this->getManagingUniacid();
            $this->_managing = $uniacid ? $this->findByUniacid($uniacid, [
                'exception' => false,
                'query' => function($query) {
                    /* @var ActiveQuery $query */
                    $query->innerJoinWith([
                        'uniAccount' => function($query) {
                            /* @var ActiveQuery $query */
                            $query->cache();
                        },
                        'wechatAccount' => function($query) {
                            /* @var ActiveQuery $query */
                            $query->cache();
                        },
                    ])->cache();
                }
            ]) : null;
        }
        return $this->_managing;
    }
}