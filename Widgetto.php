<?php

namespace exru\widgetto;

use Exception;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Widgetto extends Widget
{

    /**
     * HTML text
     * @var string
     */
    public $html = '';

    /**
     * Begin tag of template
     * @var string
     */
    public $beginTag = "\[\[";

    /**
     * End tag of template
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
     * Decode HTML entities before extract json params
     * For example:
     * //{"text":"&lt;h4 style="float:right;"&gt;foo text&lt;/h4&gt;"}
     * //...it will be translate as...
     * //{"text":"<h4 style="float:right;">foo text</h4>"}
     * @var bool
     */
    public $htmlEntitesDecode = true;

    /**
     * @param $w_name string Widget name
     * @param $item string peace of text
     * @return array|mixed
     */
    public function extractJsonParams($w_name, $item)
    {
        $options = [];
        //remove first & last tags or clear invalid json
        if ($j_params = trim(preg_replace(["/{$this->beginTag}$w_name/s", "/{$this->endTag}/s"], '', $item))) {
            if ($this->htmlEntitesDecode) {
                $j_params = html_entity_decode($j_params);
                $j_params = preg_replace('/(<[^>]+\=)"([^"]+)">/s', '$1\"$2\">', $j_params);
            }
            try { //don't handle with exception
                if ($p_params = Json::decode($j_params)) {
                    $options = $p_params;
                }
            } catch (Exception $e) {
            }
        }
        return $options;
    }

    /**
     * @param $w_params array|mixed default widget params
     * @param $j_params array|mixed extracted widget params
     * @param $w_name string Widget name
     * @return array|mixed
     */
    public function replaceOneWidget($w_params, $j_params, $w_name)
    {
        if (gettype($w_params) == 'array') {
            //overload widget params by json
            $w_params = ArrayHelper::merge($w_params, $j_params);
            if ($data = \Yii::createObject($w_params)) {
                return $data->run();
            }
        }
        if (gettype($w_params) == 'string') {
            return $w_params;
        }
        if ($w_params instanceof \Closure) {
            return call_user_func($w_params, $w_name, $j_params);
        }
    }

    /**
     * Replace all widgets one-by-one.
     * NOTE: avoid much numbers of widgets
     */
    public function replaceAllWidgets()
    {
        foreach ($this->widgets as $w_name => $options) {
            $this->html = preg_replace_callback("/{$this->beginTag}$w_name.*?{$this->endTag}/s", function ($match) use ($w_name) {
                $item = current($match);
                $j_params = $this->extractJsonParams($w_name, $item);
                $w_params = ArrayHelper::getValue($this->widgets, $w_name);
                return $this->replaceOneWidget($w_params, $j_params, $w_name);
            }, $this->html);
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->replaceAllWidgets();
        return $this->html;
    }

}