<?php

function url($segment, $params = []) {
    return wurl($segment, $params);
}
/**
 * 生成web站点地址
 * @param $segment
 * @param array $params
 *
 * @return string|void
 */
function wurl($segment, $params = []) {
    $params[0] = $segment;
    return yii\helpers\Url::to($params);
}

function murl($segment, $params = [], $noRedirect = true, $addHost = false)
{
    // todo
}
