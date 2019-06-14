<?php

namespace weikit\modules\web\controllers\rule;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use weikit\models\Rule;
use weikit\models\RuleKeyword;
use weikit\services\ReplyService;
use weikit\modules\web\Controller;

/**
 * KeywordController implements the CRUD actions for Rule model.
 */
class KeywordController extends Controller
{
    /**
     * @var string
     */
    public $menu = 'common/menu-account';
    /**
     * @var ReplyService
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, ReplyService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Rule models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'searchModel' => $searchModel = $this->service->createSearch(),
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams),
        ]);
    }

    /**
     * Displays a single Rule model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Rule model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /* @var Rule $model */
        $model = Yii::createObject(Rule::class);
        $model->module = 'reply';

        $model->populateRelation('keywords', []); // 新创建减少多余的查询
        /* @var RuleKeyword $keywordModel */
        $keywordModel = Yii::createObject(RuleKeyword::class);

        $request = Yii::$app->request;
        if ($request->isPost && $this->service->saveRule($model, $request, $keywordModel)) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'keywordModel' => $keywordModel
        ]);
    }

    /**
     * Updates an existing Rule model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->service->findRuleById($id);
        $keywordModel = Yii::createObject(RuleKeyword::class);

        $request = Yii::$app->request;
        if ($request->isPost && $this->service->saveRule($model, $request, $keywordModel)) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'keywordModel' => $keywordModel
        ]);
    }

    /**
     * Deletes an existing Rule model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Rule model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Rule the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Rule::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
