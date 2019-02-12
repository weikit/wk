<?php

namespace weikit\core;

use Yii;
use yii\web\Request;
use yii\web\NotFoundHttpException;
use yii\web\UrlNormalizerRedirectException;
use yii\helpers\Url;
use yii\base\InvalidRouteException;


class Application extends \yii\web\Application
{
    /**
     * @var string
     */
    public $controllerNamespace = 'weikit\controllers';
    /**
     * @var string|bool
     */
    public $layout = false;

    /**
     * Handles the specified request.
     * @param Request $request the request to be handled
     * @return Response the resulting response
     * @throws NotFoundHttpException if the requested route is invalid
     */
    public function handleRequest($request)
    {
        try {
            list($route, $params) = $request->resolve();
        } catch (UrlNormalizerRedirectException $e) {
            $url = $e->url;
            if (is_array($url)) {
                if (isset($url[0])) {
                    // ensure the route is absolute
                    $url[0] = '/' . ltrim($url[0], '/');
                }
                $url += $request->getQueryParams();
            }

            return $this->getResponse()->redirect(Url::to($url, $e->scheme), $e->statusCode);
        }

        try {
            Yii::debug("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = $this->runAction($route, $params);
            if ($result instanceof Response) {
                return $result;
            }

            $response = $this->getResponse();
            if ($result !== null) {
                $response->data = $result;
            }

            return $response;
        } catch (InvalidRouteException $e) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), $e->getCode(), $e);
        }
    }
}
