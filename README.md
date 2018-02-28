Yii2 widgetto
================
This is simple widget allows to replace peases in HTML code;
For example:
```php
<?=
	Widgetto::widget([
		'html'=>'<div>[[foo_widget {"passing_param":"123"}]]</div>',
		'widgets'=>[
			'foo_widget'=>[
				'class'=>'\common\widgets\MyWidget',
			]
		]
	]); //will output something like "<div>123</div>"
?>

```
The example is very simple. You may using this widget for another wide tasks.
