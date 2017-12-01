Yii2 widgetto
================
This is simple widget allows to replase peases in HTML code;
For example:
```php
<?=
	Widgetto::widget([
		'html'=>'<div>[[some_useful_widget {"a":"1", "b":"2", "c":"3"}]]</div>',
		'widgets'=>[
			'some_useful_widget'=>[
				'class'=>'\common\widgets\ABCWidget',
			]
		]
	]); //will output something like... <div>123</div>
?>

```
The example is very simple. You may using this widget for another wide tasks.
