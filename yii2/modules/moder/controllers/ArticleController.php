<?php

namespace app\modules\moder\controllers;

use Yii;
use app\models\Category;
use app\models\ImageUpload;
use app\models\Tag;
use app\models\User;
use app\models\Article;
use app\models\ArticleSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
{
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
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
    $model = $this->findModel($id);
            if($model->chekAuthor($model)){
            return $this->render('view', [
            'model' => $model,
        ]);
        }
        else {
            throw new ForbiddenHttpException(); 
        }
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Article();

        if($model->load(Yii::$app->request->post())){
            $model->user_id = Yii::$app->user->id;
            if($model->validate() && $model->save()){
                return $this->redirect(['index']);
            }
        }
            return $this->render('create', [
                'model' => $model,
            ]);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
      $model = $this->findModel($id);
      if($model->chekAuthor($model)){
        if ($model->load(Yii::$app->request->post())  && $model->save())
        {
          return $this->redirect(['index', 'id' => $model->id]);
        } 
        else{
          return $this->render('update', [
            'model' => $model,
          ]);
        }
      }
      else {
        throw new ForbiddenHttpException();  
      }

    }
    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->chekDelete($model);
    return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionImage($id)
    {
        $model = new ImageUpLoad; 
        $article = $this->findModel($id);
        if ($article->chekAuthor($article)){
        if (Yii::$app->request->isPost)
        {
          $file = UploadedFile::getInstance($model, 'image');
          if($article->saveImage( $model->upLoadFile($file, $article->image))){
            return $this->redirect(['index']);
          }
        }
    }
        return $this->render('image',['model'=>$model]);
    }
    public function actionCategory($id){
        $article = $this->findModel($id);
        $selectedCategory = $article->category->id;
        $categories = ArrayHelper::map(Category::find()->all(), 'id', 'title');
        if ($article->chekAuthor($article))  {
          if(Yii::$app->request->isPost)
        {
            $category = Yii::$app->request->post('category');
            if($article->saveCategory($category))
            {
                return $this->redirect(['view', 'id'=>$article->id]);
            }
        }
    }
        return $this->render('category', [
            'article'=>$article,
            'selectedCategory'=>$selectedCategory,
            'categories'=>$categories
        ]);
    }
    public function actionTag($id){
        $article = $this->findModel($id);
        $selectedTags = $article->getSelectedTags();
        $tags = ArrayHelper::map(Tag::find()->all(), 'id', 'title');
        if ($article->chekAuthor($article)){
        if(Yii::$app->request->isPost)
        {
           $tags = Yii::$app->request->post('tags');
           $article->saveTag($tags);
           return $this->redirect(['view', 'id'=>$article->id]);
        }
        return $this->render('tags', [
            'selectedTags'=>$selectedTags,
            'tags'=>$tags
        ]);
    }
}
}
