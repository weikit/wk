<?php

namespace weikit\modules\web\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use weikit\services\ModuleService;
use weikit\modules\web\Controller;

/**
 * ModuleController implements the CRUD actions for Module model.
 */
class ModuleController extends Controller
{
    /**
     * @var ModuleService
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, ModuleService $service, $config = [])
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
     * 可安装模块
     *
     * @return string
     */
    public function actionInactive()
    {
        return $this->render('inactive', $this->service->searchInactive(Yii::$app->getRequest()->queryParams));
    }

    public function actionActivate($name)
    {
        $this->service->activate($name);
        return $this->redirect(['index']);
    }

    /**
     * Displays a single Account model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->service->findById($id);
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

        if (!$model->getIsNewRecord()) {
            return $this->redirect(['view', 'id' => $model->acid]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->service->editById($id, Yii::$app->getRequest());

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
    public function actionDelete($id)
    {
        $this->service->deleteById($id);

        return $this->redirect(['index']);
    }
}
