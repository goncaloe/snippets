<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;

class Snippets extends \yii\base\Component
{
    public $basePath = '@webroot/snippets';

    public $baseUrl = '@web/snippets';

    public $linkSnippets = false;


    /**
     * Initializes the component.
     * @throws InvalidConfigException if [[basePath]] is invalid
     */
    public function init()
    {
        parent::init();
        $this->basePath = Yii::getAlias($this->basePath);
        if (!is_dir($this->basePath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
        } elseif (!is_writable($this->basePath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->basePath}");
        }

        $this->basePath = realpath($this->basePath);
        $this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
    }


    public function publish($id){
    }



}
