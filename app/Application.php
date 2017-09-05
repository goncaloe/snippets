<?php

namespace app;

use Yii;


class Application extends \yii\web\Application
{

    private $_vendorPath;

    /**
     * Returns the directory that stores vendor files.
     * @return string the directory that stores vendor files.
     * Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * @param string $path the directory that stores vendor files.
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Yii::getAlias($path);
        Yii::setAlias('@vendor', $this->_vendorPath);
        Yii::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower-asset');
        Yii::setAlias('@npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'npm-asset');
    }


    private $_themes;

    public function getThemes(){
        if($this->_themes === null){
            $this->_themes = [];
            $themesPath = Yii::getAlias('@var/themes');
            $handle = opendir($themesPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $themesPath . DIRECTORY_SEPARATOR . $file;
                $metaFile = $path.'/meta.json';
                $content = file_get_contents($metaFile);
                $res = json_decode($content, true);
                if($res){
                    $this->_themes[$file] = (!empty($res['name']) ? $res['name'] : $file);
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
        $themesPath = Yii::getAlias('@var/themes');
        $metaFile = $themesPath.'/'.$theme.'/meta.json';
        if(is_file($metaFile)){
            $content = file_get_contents($metaFile);
            $meta = json_decode($content, true);
            if(!empty($meta['framework'])){
                $this->_currentTheme = [$theme, $meta['framework']];
                $this->getSession()->set('__currentTheme', $this->_currentTheme);
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
        $data = $this->getSession()->get('__currentTheme');
        if(isset($data[0], $data[1])){
            $themesPath = Yii::getAlias('@var/themes');
            if(!is_file($themesPath.'/'.$data[0].'/meta.json')){
                $data = false;
            }
        }
        $this->_currentTheme = ($data ? $data : ['bs3', 'bs3']);
    }


    /**
     * Returns the snippets manager.
     * @return \app\components\Snippets the snippets manager application component.
     */
    public function getSnippets()
    {
        return $this->get('snippets');
    }


    /**
     * @inheritdoc
     */
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'snippets' => ['class' => 'app\components\Snippets'],
        ]);
    }

}
