<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\documentmanager\assets;


use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\View;


class DocumentManagerAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $defer = true;


    public $sourcePath = '@documentmanager/resources';

    public $publishOptions = [
        'forceCopy' => true,
    ];


    public $css = [
        'css/documentmanager.css',
    ];
    /**
     * @inheritdoc
     */

    public $jsOptions = ['position' => View::POS_END];

    public $js = [

        'js/documentmanager.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        JqueryAsset::class,
        'yii\bootstrap\BootstrapAsset',
    ];
}
