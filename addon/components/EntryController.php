<?php

namespace weikit\addon\components;

use weikit\addon\Controller;

abstract class EntryController extends Controller
{
    /**
     * @inheritdoc
     */
    public function createAction($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }

        // TODO 更好的兼容, 放在WeikitRule中兼容?
        global $_GPC;
        $_GPC['do'] = $id;
        $_GPC['m'] = $this->modulename;

        $actionMap = $this->actions();
        if (isset($actionMap[$id])) {
            return Yii::createObject($actionMap[$id], [$id, $this]);
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $do = 'do' . ($this->inMobile ? 'Mobile' : 'Web');
            $methodName = $do . str_replace(' ', '', ucwords(str_replace('-', ' ', $id)));
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new DoAction($id, $this, $methodName);
                }
            } else { // 魔术控制器
                return new DoAction($id, $this, $methodName, [
                    'isMagicMethod' => true,
                ]);
            }
        }

        return null;
    }
}