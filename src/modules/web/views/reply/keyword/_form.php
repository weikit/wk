<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap\ActiveForm;
use weikit\models\Rule;
use weikit\models\RuleKeyword;
use weikit\services\ModuleService;

/* @var $view \yii\web\View */
/* @var $app \yii\web\Application */
/* @var $model \weikit\models\Rule */
/* @var $form \yii\widgets\ActiveForm */
/* @var $keywordModel \weikit\models\RuleKeyword */
/* @var $moduleService \weikit\services\ModuleService */

$moduleService = Yii::createObject(ModuleService::class);
?>

<div class="rule-form" id="ruleForm" v-cloak>

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal'
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'module')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <label class="control-label col-sm-3">触发关键字</label>
        <div class="col-sm-6">
            <table class="table">
                <thead>
                <tr>
                    <th><?= $keywordModel->getAttributeLabel('content') ?></th>
                    <th><?= $keywordModel->getAttributeLabel('type') ?></th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <col width="40%" />
                <col width="40%" />
                <col/>
                <tr v-for="(keyword, key) in keywords">
                    <td>{{keyword.content}}</td>
                    <td>{{keywordTypes[keyword.type]}}</td>
                    <td>
                        <input v-if="keyword.id" type="hidden" :name="keywordFormName + '[' + key + '][id]'" :value="keyword.id">
                        <input type="hidden" :name="keywordFormName + '[' + key + '][content]'" :value="keyword.content">
                        <input type="hidden" :name="keywordFormName + '[' + key + '][type]'" :value="keyword.type">
                        <a href="javascript:;" @click="handleRemoveKeyword(keyword)">删除</a>
                    </td>
                </tr>
                </tbody>
            </table>
            <button id="addRuleKeyword" class="btn btn-default btn-block" type="button" data-toggle="modal" data-target="#keywordModal" @click="addKeyword()"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 添加关键字</button>
        </div>
    </div>

    <?= $form->field($model, 'status', ['inline' => true])->radioList(Rule::$statuses, [
        'value' => $model->status !== null ? $model->status : Rule::STATUS_ACTIVE,
    ]) ?>

    <?= $form->field($model, 'displayorder')->textInput() ?>

    <div class="form-group">
        <label class="control-label col-sm-3">回复内容</label>
        <div class="col-sm-6">
            <?= $moduleService->renderModuleForm('core') // TODO custom addon module form ?>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6">
            <?= Html::submitButton($model->isNewRecord ? '创建回复规则' : '修改回复规则', [
                'class' => 'btn ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div id="keywordModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">添加关键字</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label" for="type">关键字类型</label>
                            <div class="col-sm-9">
                                <label v-for="(text, type) in keywordTypes" class="radio-inline">
                                    <input v-model="keywordForm.type" :checked="type == keywordForm.type" :value="type" type="radio" id="type"> {{text}}
                                </label>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label" for="content">关键字</label>
                            <div class="col-sm-9">
                                <input v-model="keywordForm.content" type="text" class="form-control" id="content" placeholder="输出关键字">
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-9 col-sm-offset-3">
								<span v-if="keywordForm.type == <?= RuleKeyword::TYPE_REGULAR ?>" class="help-block">
									用户进行交谈时，对话内容符合述关键字中定义的模式才会执行这条规则。<br>
									注意：如果你不明白正则表达式的工作方式，请不要使用正则匹配<br>
									注意：正则匹配使用MySQL的匹配引擎，请使用MySQL的正则语法<br>
									示例：<br>
									^微信匹配以“微信”开头的语句<br>
									微信$匹配以“微信”结尾的语句<br>
									^微信$匹配等同“微信”的语句<br>
									微信匹配包含“微信”的语句<br>
									[0-9.-]匹配所有的数字，句号和减号<br>
									^[a-zA-Z_]$所有的字母和下划线<br>
									^[[:alpha:]]{3}$所有的3个字母的单词<br>
									^a{4}$aaaa<br>
									^a{2,4}$aa，aaa或aaaa<br>
									^a{2,}$匹配多于两个a的字符串<br>
								</span>
                                <span v-else class="help-block ng-hide">多个关键字请使用逗号隔开，如天气，今日天气</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button @click="handleAddKeyword()" type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php
// 新建的规则从第100个递增,和已有的规则不冲突(前提是已有的规则不能超过100个)
$keywords = Json::encode($model->keywords, JSON_UNESCAPED_UNICODE);
$keywordTypes = json_encode(RuleKeyword::$types, JSON_UNESCAPED_UNICODE);
$keywordFormName = $keywordModel->formName();
$this->registerJs(<<<JS
require(['vue'], function(Vue) {
  new Vue({
    el: '#ruleForm',
    data: {
      keywords: {$keywords},
      keywordForm: {},
      keywordFormName: '{$keywordFormName}',
      keywordTypes: {$keywordTypes},
    },
    methods: {
      addKeyword() {
        this.keywordForm = {
          type: 1,
          content: ''
        };
      },
      handleAddKeyword() {
        this.keywords.push(this.keywordForm);
      },
      handleRemoveKeyword(keyword) {
        this.keywords = this.keywords.filter(function(kwd) {
          return kwd !== keyword;
        })
      }
    }
  });
});
JS
);
