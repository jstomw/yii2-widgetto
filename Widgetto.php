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
     * Array of widgets
     * [
     *      'parsing_name'=>[
     *          'class'=>Widget::className(),
     *          'option1'=>'foo',
     *          'option2'=>'bar',
     *      ],
     * ]
     * @var array
     */
    public $widgets = [];

    /**
     * Don't show peases
     * @var bool
     */
    public $escapeWrongResult = true;

    private function _replaceWidgets()
    {
        foreach ($this->widgets as $parsing_name => $options) {
            $this->html = preg_replace_callback("/{$this->beginTag}$parsing_name.*?{$this->endTag}/s", function ($match) use ($parsing_name) {
                $peace = current($match);
                $widget = ArrayHelper::getValue($this->widgets, $parsing_name);
                try {
                    if ($json = preg_replace(["/{$this->beginTag}$parsing_name/s", "/{$this->endTag}/s"], '', $peace)) {
                        $options = Json::decode($json);
                        $widget = ArrayHelper::merge($widget, $options);
                    }
                    if ($data = \Yii::createObject($widget)) {
                        return $data->run();
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
