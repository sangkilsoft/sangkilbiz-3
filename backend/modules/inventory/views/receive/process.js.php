<?php if (false): ?>
	<script type="text/javascript">
<?php endif; ?>
	yii.receive = (function($) {
		var $grid, $form, template, counter = 0;

		var local = {
			delay: 1000,
			limit: 20,
			format: function(n) {
				return $.number(n, 0);
			},
			normalizeItem: function($row) {
				var s = $row.find('input[data-field="transfer_qty_send"]').val() * 1;
				var r = $row.find('input[data-field="transfer_qty_receive"]').val() * 1;
				var $is = $row.find('input[data-field="transfer_selisih"]');
				$is.val(r - s);
				if(r == s){
					$is.css({color:'black'});
				}else{
					$is.css({color:'red'});
				}
			},
			initRow: function() {
				$('#detail-grid > tbody > tr').each(function() {
					var $row = $(this);
					local.normalizeItem($row);
				});
				
			},
			initObj: function() {
				$grid = $('#detail-grid');
				$form = $('#purchase-form');
				template = $('#detail-grid > tbody').data('template');
			},
			initEvent: function() {
				$grid.on('click', '[data-action="delete"]', function() {
					$(this).closest('tr').remove();
					local.normalizeItem();
					return false;
				});

				$grid.on('click', 'tr', function() {
					$grid.find('tbody > tr').removeClass('selected');
					$(this).addClass('selected');
				});

				$grid.on('keydown', ':input[data-field]', function(e) {
					if (e.keyCode == 13) {
						var $inputs = $grid.find(':input:visible[data-field]:not([readonly])');
						var idx = $inputs.index(this);
						if (idx >= 0) {
							if (idx < $inputs.length - 1) {
								$inputs.eq(idx + 1).focus();
							} else {
								//$('#product').focus();
							}
						}
					}
				});

				$grid.on('change', ':input[data-field]', function() {
					var $row = $(this).closest('tr');
					local.normalizeItem($row);
				});

				var clicked = false;
				$grid.on('click focus', 'input[data-field]', function(e) {
					if (e.type == 'click') {
						clicked = true;
					} else {
						if (!clicked) {
							$(this).select();
						}
						clicked = false;
					}
				});
			}
		}

		var pub = {
			init: function() {
				local.initObj();
				local.initRow();
				local.initEvent();
				yii.numeric.input($grid, 'input[data-field]');
			},
		};
		return pub;
	})(window.jQuery);
<?php if (false): ?>
	</script>
<?php endif; ?>