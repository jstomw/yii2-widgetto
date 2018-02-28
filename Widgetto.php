<?php

namespace exru\widgetto;

use Exception;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Widgetto extends Widget
{

    /**
     * Parsing html
     * @var string
     */
    public $html = '';

    /**
     * Begin of template
     * @var string
     */
    public $beginTag = "\[\[";

    /**
     * End of template
     * @var string
     */
    public $endTag = "\]\]";

    /**
     * Array of widgets. For example:
     * [
     *      'FOO_WIDGET'=>[
     *          'class'=>Widget::className(),
     *          'foo_option'=>'bar_value', //may overload by passed json params
     *      ],
     *      'BAR_WIDGET'=>function($widget_name, $options){
     *          //$options (if json sets)
     *      },
     *      'BAZ_WIDGET'=>'Some string'
     * ]
     * @var array
     */
    public $widgets = [];

    /**
     * Hide wrong widgets
     * @var bool
     */
    public $escapeWrongResult = true;

    private function _replaceWidgets()
    {
        foreach ($this->widgets as $parsing_name => $options) {
            $this->html = preg_replace_callback("/{$this->beginTag}$parsing_name.*?{$this->endTag}/s", function ($match) use ($parsing_name) {
                $peace = current($match);
                $widget_params = ArrayHelper::getValue($this->widgets, $parsing_name);
                try {
                    $options = false;
                    $json = trim(preg_replace(["/{$this->beginTag}$parsing_name/s", "/{$this->endTag}/s"], '', $peace));
                    if ($json) {
                        $options = Json::decode($json);
                    }
                    if (gettype($widget_params) == 'array') {
                        $widget_params = ArrayHelper::merge($widget_params, $options);
                        if ($data = \Yii::createObject($widget_params)) {
                            return $data->run();
                        }
                    }
                    if (gettype($widget_params) == 'string') {
                        return $widget_params;
                    }
                    if ($widget_params instanceof \Closure) {
                        return call_user_func($widget_params, $parsing_name, $options);
                    }

                } catch (Exception $e) {
                    if (!$this->escapeWrongResult && YII_DEBUG == true) {
                        var_dump($e);
                        exit;
                    }
                    return $peace;
                }
            }, $this->html);
        }
    }

    public function run()
    {
        $this->_replaceWidgets();
        return $this->html;
    }

}