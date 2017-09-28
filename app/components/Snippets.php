<?php

namespace app\components;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\helpers\Html;

class Snippets extends \yii\base\Component
{
    public $snippetsPath = '@data/snippets';

    public $snippetsUrl = '@web/../data/snippets';

    public $themesPath = '@data/themes';

    public $themesUrl = '@web/../data/themes';
    
    public $linkSnippets = false;

    /**
     * Initializes the component.
     * @throws InvalidConfigException if [[snippetsPath]] is invalid
     */
    public function init()
    {
        parent::init();
        $this->snippetsPath = Yii::getAlias($this->snippetsPath);
        if (!is_dir($this->snippetsPath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->snippetsPath}");
        } elseif (!is_writable($this->snippetsPath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->snippetsPath}");
        }

        $this->snippetsPath = realpath($this->snippetsPath);
        $this->snippetsUrl = FileHelper::normalizePath(Yii::getAlias($this->snippetsUrl));

        $this->themesPath = Yii::getAlias($this->themesPath);
        if (!is_dir($this->themesPath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->themesPath}");
        } elseif (!is_writable($this->themesPath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->themesPath}");
        }

        $this->themesPath = realpath($this->themesPath);
        $this->themesUrl = FileHelper::normalizePath(Yii::getAlias($this->themesUrl));
    }
    
    private $_themes;

    public function getThemes(){
        if($this->_themes === null){
            $this->_themes = [];
            $handle = opendir($this->themesPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $this->themesPath . DIRECTORY_SEPARATOR . $file;
                $metaFile = $path.'/theme.json';
                if(!file_exists($metaFile)){
                    throw new Exception('Theme should have a theme.json file');
                }
                $content = file_get_contents($metaFile);
                if(($res = @json_decode($content, true)) !== null){
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
        if($theme == "@"){
            $this->_currentTheme = false;
            Yii::$app->getSession()->set('__currentTheme', $this->_currentTheme);
        }
        else {
            $metaFile = $this->themesPath.'/'.$theme.'/theme.json';
            if(is_file($metaFile)){
                $content = file_get_contents($metaFile);
                $meta = @json_decode($content, true);
                if(!empty($meta['framework'])){
                    $this->_currentTheme = [$theme, $meta['framework']];
                    Yii::$app->getSession()->set('__currentTheme', $this->_currentTheme);
                }
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
            if(!is_file($this->themesPath.'/'.$data[0].'/theme.json')){
                $data = false;
            }
        }
        $this->_currentTheme = ($data ? $data : ['@', '']);
    }


    private $_snippets;
    
    public function getSnippets(){
        if($this->_snippets === null){
            $this->_snippets = [];
            $handle = opendir($this->snippetsPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if($data = $this->getSnippetData($file)){
                    $this->_snippets[$file] = $data;
                }
            }
            closedir($handle);
        }
        return $this->_snippets;
    }

    public function getSnippetData($id){
        $path = $this->snippetsPath . DIRECTORY_SEPARATOR . $id;
        $metaFile = $path.'/snippet.json';
        if(file_exists($metaFile)) {
            $content = file_get_contents($metaFile);
            if (($data = @json_decode($content, true)) !== null) {
                $data['id'] = $id;
                $data['path'] = $path;
                return $data;
            }
        }
        return false;
    }
    
    public function renderIframe($id, $params = [])
    {
        $snippetPath = $this->snippetsPath.'/'.$id;
        $snippetUrl = $this->snippetsUrl.'/'.$id;

        $metaFile = $snippetPath.'/snippet.json';
        if(!file_exists($metaFile)){
            throw new Exception("Metafile iframe '$metaFile' not exists.");
        }
        $mc = file_get_contents($metaFile);
        $snippetMeta = json_decode($mc, true);

        $indexFile = $snippetPath.'/index.html';
        if(!file_exists($indexFile)){
            throw new Exception("File iframe index '$indexFile' not exists.");
        }

        $am = Yii::$app->getAssetManager();

        $content = file_get_contents($indexFile);

        $pattern = "/(src|href)=[\"'](?!cdn|http|https|\/\/)(?:\.?\/)?(.*?)[\"']/mi";
        $content = preg_replace($pattern, "$1=\"{$snippetUrl}/$2\"", $content);

        $appendHeader = '';
        if (preg_match('/<body([^>]*)>(.*?)<\/body>/ius', $content, $match)) {
            $bodyProperties = ' '.$match[1];
            $bodyContent = $match[2];
            if (preg_match('/<head[^>]*>(.*?)<\/head>/ius', $content, $match)) {
                $appendHeader = $match[1];
            }
        }
        else {
            $bodyProperties = '';
            $bodyContent = $content;
        }

        $js = [];
        $css = [];

        $currTheme = $this->getCurrentTheme();
        if($currTheme !== "@") {
            $themePath = $this->themesPath . '/' . $currTheme;
            $publish = $am->publish($themePath);
            $themeUrl = $publish[1];

            $metaFile = $themePath . '/theme.json';
            $meta = [];
            if (file_exists($metaFile)) {
                $mc = file_get_contents($metaFile);
                $meta = json_decode($mc, true);
            }

            if (!empty($meta['js'])) {
                foreach ($meta['js'] as $path) {
                    $js[] = Url::isRelative($path) ? $themeUrl . '/' . $path : $path;
                }
            }
            if (!empty($meta['css'])) {
                foreach ($meta['css'] as $path) {
                    $css[] = Url::isRelative($path) ? $themeUrl . '/' . $path : $path;
                }
            }
        }

        if(!empty($snippetMeta['js'])){
            foreach($snippetMeta['js'] as $path){
                $js[] = Url::isRelative($path) ? $snippetUrl.'/'.$path : $path;
            }
        }

        if(!empty($snippetMeta['css'])){
            foreach($snippetMeta['css'] as $path){
                $css[] = Url::isRelative($path) ? $snippetUrl.'/'.$path : $path;
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

        $title = isset($snippetMeta['name']) ? Html::encode($snippetMeta['name']) : 'iFrame #'.$id;

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>'.$title.'</title>';
        $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        foreach($js as $jsFile){
            $html .= '<script src="'.$jsFile.'"></script>';
        }
        foreach($css as $cssFile){
            $html .= '<link href="'.$cssFile.'" rel="stylesheet">';
        }
        $html .= $appendHeader;
        $html .= '</head>';
        $html .= '<body'.$bodyProperties.'>';
        $html .= $bodyContent;
        $html .= '</body>';
        $html .= '</html>';
        return $html;
    }


}
