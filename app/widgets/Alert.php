<?php

namespace app\widgets;

use yii\helpers\Html;

class Alert extends \yii\base\Widget
{
    public $options = [];

    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the session flash variable
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    public function init()
    {
        parent::init();

        $session = \Yii::$app->getSession();
        $flashes = $session->getAllFlashes();
        if(empty($flashes)){
            return;
        }
        $appendCss = ' alert-dismissible fade show' . (isset($this->options['class']) ? ' ' . $this->options['class'] : '');
        $this->options['role'] = 'alert';

        $hasAlert = false;
        foreach ($flashes as $type => $data) {
            if (isset($this->alertTypes[$type])) {
                $data = (array) $data;
                foreach ($data as $message) {
                    /* initialize css class for each alert box */
                    $this->options['class'] = 'alert ' .$this->alertTypes[$type] . $appendCss;

                    /* assign unique id to each alert box */
                    $this->options['id'] = $this->getId() . '-' . $type;
                    echo Html::beginTag('div', $this->options) . "\n";
                    echo Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'alert', 'aria-label' => 'Close']) . "\n";
                    echo $message . "\n";
                    echo "\n" . Html::endTag('div');
                    $hasAlert = true;
                }

                $session->removeFlash($type);
            }
        }

        if($hasAlert) {
            $this->getView()->registerJs("$('[data-dismiss=\"alert\"]').on('click', function(){ $(this).parent().remove(); return false;})");
        }

    }
}
