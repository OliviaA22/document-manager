<?php
/* @var $this \yii\web\View */
/* @var $content string */

use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use yii\widgets\Breadcrumbs;
use humhub\modules\documentmanager\models\Folder;

humhub\modules\documentmanager\assets\DocumentManagerAsset::register($this);

/* @var  humhub\modules\documentmanager\models\Folder $folders */


?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php $this->head() ?>
    <?= $this->render('head'); ?>
</head>

<body style="padding-top: 10px;">

    <?php $this->beginBody() ?>

    <?= $content; ?>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>