<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use yii\helpers\Url;

class Snippets extends \yii\base\Component
{
    public $basePath = '@data/snippets';

    public $baseUrl = '@web/../data/snippets';

    public $linkSnippets = false;

    public $themes;

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
        $this->baseUrl = FileHelper::normalizePath(Yii::getAlias($this->baseUrl));
    }


    private $_themes;

    public function getThemes(){
        if($this->_themes === null){
            $this->_themes = [];
            $themesPath = Yii::getAlias('@data/themes');
            $handle = opendir($themesPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $themesPath . DIRECTORY_SEPARATOR . $file;
                $metaFile = $path.'/theme.json';
                if(!file_exists($metaFile)){
                    throw new Exception('Theme should have a theme.json file');
                }
                $content = file_get_contents($metaFile);
                $res = @json_decode($content, true);
                if(!empty($res)){
                    $this->_themes[$file] = $res;
                }
            }
            closedir($handle);
        }
        return $this->_themes;
    }

    /**
     * @var array pair where the first element is the theme, and the second is the framework
     */
    private $_currentTheme;

    public function getCurrentTheme(){
        if($this->_currentTheme === null){
            $this->loadCurrentTheme();
        }
        return $this->_currentTheme[0];
    }

    public function setCurrentTheme($theme){
        $themesPath = Yii::getAlias('@data/themes');
        $metaFile = $themesPath.'/'.$theme.'/theme.json';
        if(is_file($metaFile)){
            $content = file_get_contents($metaFile);
            $meta = @json_decode($content, true);
            if(!empty($meta['framework'])){
                $this->_currentTheme = [$theme, $meta['framework']];
                Yii::$app->getSession()->set('__currentTheme', $this->_currentTheme);
            }
        }
    }

    public function getCurrentFramework(){
        if($this->_currentTheme === null){
            $this->loadCurrentTheme();
        }
        return $this->_currentTheme[1];
    }

    private function loadCurrentTheme(){
        $data = Yii::$app->getSession()->get('__currentTheme');
        if(isset($data[0], $data[1])){
            $themesPath = Yii::getAlias('@data/themes');
            if(!is_file($themesPath.'/'.$data[0].'/theme.json')){
                $data = false;
            }
        }
        $this->_currentTheme = ($data ? $data : ['bs3', 'bs3']);
    }

    public function renderIframe($id, $params = [])
    {
        $snippetPath = $this->basePath.'/'.$id;
        $snippetUrl = $this->baseUrl.'/'.$id;

        $indexFile = $snippetPath.'/index.html';

        if(!file_exists($indexFile)){
            throw new Exception("File iframe index '$indexFile' not exists.");
        }

        $am = Yii::$app->getAssetManager();

        $content = file_get_contents($indexFile);
        $content = str_replace('{snippetUrl}', $snippetUrl, $content);

        $headerContent = '';
        if (preg_match('/<body([^>]*)>(.*?)<\/body>/ius', $content, $match)) {
            $bodyProperties = ' '.$match[1];
            $bodyContent = $match[2];
            if (preg_match('/<head[^>]*>(.*?)<\/head>/ius', $content, $match)) {
                $headerContent = $match[1];
            }
        }
        else {
            $bodyProperties = '';
            $bodyContent = $content;
        }

        $js = [];
        $css = [];

        $currTheme = $this->getCurrentTheme();
        $themePath = Yii::getAlias('@data/themes/'.$currTheme);
        $publish = $am->publish($themePath);
        $themeUrl = $publish[1];

        $metaFile = $themePath.'/theme.json';
        $meta = [];
        if(is_file($metaFile)){
            $mc = file_get_contents($metaFile);
            $meta = json_decode($mc, true);
        }

        if(!empty($meta['js'])){
            foreach($meta['js'] as $path){
                $js[] = Url::isRelative($path) ? $themeUrl.'/'.$path : $path;
            }
        }

        if(!empty($meta['css'])){
            foreach($meta['css'] as $path){
                $css[] = Url::isRelative($path) ? $themeUrl.'/'.$path : $path;
            }
        }

        $jsFile = $snippetPath.'/index.js';
        if(file_exists($jsFile)){
            $js[] = $snippetUrl.'/index.js';
        }

        $cssFile = $snippetPath.'/index.css';
        if(file_exists($cssFile)){
            $css[] = $snippetUrl.'/index.css';
        }

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>iFrame</title>';
        $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        foreach($js as $jsFile){
            $html .= '<script src="'.$jsFile.'"></script>';
        }
        foreach($css as $cssFile){
            $html .= '<link href="'.$cssFile.'" rel="stylesheet">';
        }
        $html .= $headerContent;
        $html .= '</head>';
        $html .= '<body'.$bodyProperties.'>';
        $html .= $bodyContent;
        $publish = $am->publish(Yii::getAlias('@app/assets/iframeresizer/iframeresizer.contentwindow.min.js'));
        $html .= '<script type="text/javascript" src="'.$publish[1].'" defer></script>';
        $html .= '</body>';
        $html .= '</html>';
        return $html;
    }


}
