<?php

namespace weikit\modules\web\controllers\account;

use weikit\models\WechatAccount;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use weikit\services\WechatAccountService;

/**
 * AccountController implements the CRUD actions for Account model.
 */
class WechatController extends Controller
{
    /**
     * @var WechatAccountService
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, WechatAccountService $service, $config = [])
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
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index', $this->service->search(Yii::$app->getRequest()->queryParams));
    }

    /**
     * Displays a single Account model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($acid)
    {
        $model = $this->service->findByAcid($acid);
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = $this->service->add(Yii::$app->getRequest());

        if ($model instanceof WechatAccount) {
            return $this->redirect(['view', 'acid' => $model->acid]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $acid
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($acid)
    {
        $model = $this->service->editByAcid($acid, Yii::$app->getRequest());

        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Account model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($acid)
    {
        $this->service->editByAcid($acid);

        return $this->redirect(['index']);
    }

    /**
     *
     * @param $acid
     *
     * @return string
     */
    public function actionGuide($acid)
    {
        $model = $this->service->findByAcid($acid);

        return $this->render('guide', [
            'model' => $model,
        ]);
    }
}