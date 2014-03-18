<?php 
use yii\helpers\Url;
?>
<style>
<?php $this->beginBlock('CSS') ?>
	#detail-grid td.items .qty{
		/*		display:none;*/
		padding-left: 20px;
	}
	#detail-grid td.items .discon{
		display:none;
		padding-left: 20px;
	}
	#detail-grid > tbody > tr:hover > td{
		background-color:#E9E9F9;
	}
	#detail-grid > tbody > tr.selected > td{
		background-color:#E9E9E9;
	}
	#detail-grid input{
		border:none;
		background:inherit;
		color:inherit;
		text-align:right;
	}
	#detail-grid input:focus{
		
	}
	#detail-grid li:not(:first-child){
		color:#A0A0A0;
	}
	.ui-autocomplete {
		max-height: 200px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
	}
	#list-session li{
		
	}
	#list-session li.active{
		color:blue;
	}
<?php $this->endBlock(); ?>
</style>

<script type="text/javascript">
<?php $this->beginBlock('JS_END') ?>

<?php $this->endBlock(); ?>

<?php $this->beginBlock('JS_READY') ?>
	$('#product').data("ui-autocomplete")._renderItem = function(ul, item) {
		var $a = $('<a>').append($('<b>').text(item.text)).append('<br>')
				.append($('<i>').text(item.cd + ' - @ Rp' + item.price).css({color: '#999999'}));
		return $("<li>").append($a).appendTo(ul);
	};

	$('#product').change(function() {
		var item = yii.Product.searchByCode(this.value);
		if (item !== false) {
			yii.Pos.addItem(item);
		}
		this.value = '';
		$(this).autocomplete("close");
	});

	$('#product').focus();

<?php $this->endBlock(); ?>
</script>
<?php
$this->registerJsFile(Url::toRoute(['js','script'=>'master']),[\yii\web\YiiAsset::className()]);
$this->registerJsFile(Url::toRoute(['js','script'=>'process']),[\yii\web\YiiAsset::className()]);
$this->registerCss($this->blocks['CSS']);
//$this->registerJs($this->blocks['JS_END'], yii\web\View::POS_END);
$this->registerJs($this->blocks['JS_READY'], yii\web\View::POS_READY);
