<?php

/**
 * 生成web站点地址
 * @param $segment
 * @param array $params
 *
 * @return string|void
 */
function wurl($segment, $params = []) {
    $segments = explode('/', $segment);
    $params = array_merge($params, [
        'c' => $segments[0] ?? null,
        'a' => $segments[1] ?? null,
        'do' => $segments[2] ?? null,
    ]);
    return home_url('/web/?' . http_build_query($params));
}

function murl($segment, $params = [], $noRedirect = true, $addHost = false)
{
    // todo
}
