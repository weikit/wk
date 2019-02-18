<?php

namespace weikit\core\widgets\bootstrap;

use yii\grid\GridView;

class GridPanel extends GridView
{
    /**
     * @
     */
    public $layout = "<div class='panel panel-default'><div class='panel-heading'>{summary}</div>\n{items}\n</div>{pager}";
}