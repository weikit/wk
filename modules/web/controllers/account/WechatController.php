<?php

namespace weikit\modules\web\controllers\account;

use Yii;
use yii\filters\VerbFilter;
use weikit\models\Module;
use weikit\models\WechatAccount;
use weikit\modules\web\Controller;
use yii\web\NotFoundHttpException;
use weikit\services\WechatAccountService;

/**
 * AccountController implements the CRUD actions for Account model.
 */
class WechatController extends Controller
{
    /**
     * @var string
     */
    public $frame = 'account';
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

    public function actionHome()
    {
        return $this->render('home', [
        ]);
    }

    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->frame = null;
        return $this->render('index', [
            'searchModel' => $searchModel = $this->service->createSearch(),
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams)
        ]);
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
