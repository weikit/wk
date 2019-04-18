<?php

namespace weikit\core\traits;

use Yii;
use yii\helpers\Url;
use yii\web\Response;

trait ControllerMessageTrait
{
    /**
     * 发送消息
     *
     * @param $message
     * @param string $type
     * @param null $redirect
     * @param null $resultType
     *
     * @return array|bool|string
     */
    public function message($message, $type = 'error', $redirect = null, $resultType = null)
    {
        $request = Yii::$app->getRequest();
        if ($resultType === null) {
            $resultType = $request->getIsAjax() ? 'json' : 'html';
        } elseif ($resultType === 'flash') {
            $resultType = Yii::$app->getRequest()->getIsAjax() ? 'json' : $resultType;
        }
        $data = [
            'type'     => $type,
            'message'  => $message,
            'redirect' => $redirect === null ? null : Url::to($redirect),
        ];
        switch ($resultType) {
            case 'html':
                return $this->render(Yii::$app->getModule('wechat')->messageLayout, $data);
            case 'json':
                Yii::$app->getResponse()->format = Response::FORMAT_JSON;

                return $data;
            case 'flash':
                Yii::$app->session->setFlash($type, $message);
                $data['redirect'] == null && $data['redirect'] = $request->getReferrer();
                Yii::$app->end(0, $this->redirect($data['redirect']));

                return true;
            default:
                return $message;
        }
    }

    /**
     * flash消息
     *
     * @param $message
     * @param string $status
     * @param null $redirect
     *
     * @return array|bool|string
     */
    public function flash($message, $status = 'error', $redirect = null)
    {
        return $this->message($message, $status, $redirect, 'flash');
    }
}