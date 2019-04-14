<?php

namespace weikit\addon\components;

use Yii;
use yii\base\Component;
use weikit\models\Account;

class MessageHandler extends Component
{
    /**
     * @var Account
     */
    protected $account;
    /**
     * @var array
     */
    protected $message;

    public function __construct(Account $account, array $message, $config = [])
    {
        $this->account = $account;
        $this->message = $message;
        parent::__construct($config);
    }

    public function process()
    {
        // TODO 群发事件推送群发处理
        // TODO 模板消息事件推送处理
        // TODO 用户上报地理位置事件推送处理
        // TODO 自定义菜单事件推送处理
        // TODO 微信小店订单付款通知处理
        // TODO 微信卡卷(卡券通过审核、卡券被用户领取、卡券被用户删除)通知处理
        // TODO 智能设备接口
        // TODO 多客服转发处理
        $result = null;
        foreach ($this->match() as $model) {
            if ($model instanceof ReplyRuleKeyword) {
                $processor = $model->rule->processor;
                $route = $processor[0] == '/' ? $processor : '/wechat/' . $model->rule->mid . '/' . $model->rule->processor;
            } elseif (isset($model['route'])) { // 直接返回处理route
                $route = $model['route'];
            } else {
                continue;
            }

            // 转发路由请求 参考: Yii::$app->runAction()
            $parts = Yii::$app->createController($route);
            if (is_array($parts)) {
                list($controller, $actionID) = $parts;

                // 微信请求的处理器必须继承callmez\wechat\components\ProcessController
                if (!($controller instanceof ProcessController)) {
                    throw new InvalidCallException("Wechat process controller must instance of '" . ProcessController::className() . "'");
                }
                // 传入当前公众号和微信请求内容
                $controller->message = $this->message;
                $controller->setWechat($this->getWechat());

                $oldController = Yii::$app->controller;
                $result = $controller->runAction($actionID);
                Yii::$app->controller = $oldController;
            }

            // 如果有数据则跳出循环直接输出. 否则只作为订阅类型继续循环处理
            if ($result !== null) {
                break;
            }
        }


        $module = isset($controller) ? $controller->module->id : 'wechat'; // 处理的模块
        if ($model instanceof ReplyRuleKeyword) {
            $kid = $model->id;
            $rid = $model->rid;
        } else {
            $kid = $rid = 0;
        }
        // 记录请求内容
        MessageHistory::add([
            'wid' => $this->getWechat()->id,
            'rid' => $rid,
            'kid' => $kid,
            'from' => $this->message['FromUserName'],
            'to' => $this->message['ToUserName'],
            'module' => $module,
            'message' => $this->message,
            'type' => MessageHistory::TYPE_REQUEST
        ]);
        // 记录响应内容
        if ($result !== null) {
            // 记录响应内容
            MessageHistory::add([
                'wid' => $this->getWechat()->id,
                'rid' => $rid,
                'kid' => $kid,
                'from' => $this->message['ToUserName'],
                'to' => $this->message['FromUserName'],
                'module' => $module,
                'message' => $result,
                'type' => MessageHistory::TYPE_RESPONSE
            ]);
        }

        return $result;
    }

    /**
     * 回复规则匹配
     * @return array|mixed
     */
    protected function match()
    {
        if ($this->message['MsgType'] == 'event') { // 匹配事件
            $method = 'matchEvent' . $this->message['Event'];
        } else { // 匹配类型
            $method = 'match' . $this->message['MsgType'];
        }
        $matches = [];
        if (method_exists($this, $method)) {
            $matches = call_user_func([$this, $method]);
        }
        $matches = array_merge([
            ['route' => '/wechat/process/fans/record'] // 记录常用数据
        ], $matches);

        return $matches;
    }

    /**
     * 文本消息关键字触发
     * @return array
     */
    protected function matchText()
    {
        return ReplyRuleKeyword::find()
                               ->keyword($this->message['Content'])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 图片消息触发
     * @return mixed
     */
    protected function matchImage()
    {
        return ReplyRuleKeyword::find()
                               ->andFilterWhere(['type' => ReplyRuleKeyword::TYPE_IMAGE])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 音频消息触发
     * @return mixed
     */
    protected function matchVoice()
    {
        return ReplyRuleKeyword::find()
                               ->andFilterWhere(['type' => ReplyRuleKeyword::TYPE_VOICE])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 视频, 短视频消息触发
     * @return mixed
     */
    protected function matchVideo()
    {
        return ReplyRuleKeyword::find()
                               ->andFilterWhere(['type' => [ReplyRuleKeyword::TYPE_VIDEO, ReplyRuleKeyword::TYPE_SHORT_VIDEO]])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 位置消息触发
     * @return mixed
     */
    protected function matchLocation()
    {
        return ReplyRuleKeyword::find()
                               ->andFilterWhere(['type' => [ReplyRuleKeyword::TYPE_LOCATION]])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 链接消息触发
     * @return mixed
     */
    protected function matchLink()
    {
        return ReplyRuleKeyword::find()
                               ->andFilterWhere(['type' => [ReplyRuleKeyword::TYPE_LINK]])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 关注事件
     * @return array|void
     */
    protected function matchEventSubscribe()
    {
        // 扫码关注
        if (array_key_exists('Eventkey', $this->message) && strexists($this->message['Eventkey'], 'qrscene')) {
            $this->message['Eventkey'] = explode('_', $this->message['Eventkey'])[1]; // 取二维码的参数值
            return $this->matchEventScan();
        }
        // 订阅请求回复规则触发
        return ReplyRuleKeyword::find()
                               ->andFilterWhere(['type' => [ReplyRuleKeyword::TYPE_SUBSCRIBE]])
                               ->wechatRule($this->getWechat()->id)
                               ->limitTime(TIMESTAMP)
                               ->all();
    }

    /**
     * 取消关注事件
     * @return array
     */
    protected function matchEventUnsubscribe()
    {
        $match = ReplyRuleKeyword::find()
                                 ->andFilterWhere(['type' => [ReplyRuleKeyword::TYPE_UNSUBSCRIBE]])
                                 ->wechatRule($this->getWechat()->id)
                                 ->limitTime(TIMESTAMP)
                                 ->all();
        return array_merge([ // 取消关注默认处理
            ['route' => '/wechat/process/fans/unsubscribe']
        ], $match);
    }

    /**
     * 用户已关注时的扫码事件触发
     * @return array
     */
    protected function matchEventScan()
    {
        if (array_key_exists('Eventkey', $this->message)) {
            $this->message['Content'] = $this->message['EventKey'];
            return $this->matchText();
        }
        return [];
    }

    /**
     * 上报地理位置事件触发
     * @return mixed
     */
    protected function matchEventLocation()
    {
        return $this->matchLocation(); // 直接匹配位置消息
    }

    /**
     * 点击菜单拉取消息时的事件触发
     * @return array
     */
    protected function matchEventClick()
    {
        // 触发作为关键字处理
        if (array_key_exists('EventKey', $this->message)) {
            $this->message['Content'] = $this->message['EventKey']; // EventKey作为关键字Content
            return $this->matchText();
        }
        return [];
    }

    /**
     * 点击菜单跳转链接时的事件触发
     * @return array
     */
    protected function matchEventView()
    {
        // 链接内容作为关键字
        if (array_key_exists('EventKey', $this->message)) {
            $this->message['Content'] = $this->message['EventKey']; // EventKey作为关键字Content
            return $this->matchText();
        }
        return [];
    }
}