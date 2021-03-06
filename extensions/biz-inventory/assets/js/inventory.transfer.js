yii.transfer = (function($) {
    var $grid, $form, template, counter = 0;

    var local = {
        checkStock: true,
        addItem: function(item) {
            var has = false;
            $('#detail-grid > tbody > tr').each(function() {
                var $row = $(this);
                if ($row.find('input[data-field="id_product"]').val() == item.id) {
                    has = true;
                    var $qty = $row.find('input[data-field="transfer_qty_send"]');
                    if ($qty.val() == '') {
                        $qty.val('2');
                    } else {
                        $qty.val($qty.val() * 1 + 1);
                    }
                }
            });
            if (!has) {
                var $row = $(template.replace(/_index_/g, counter++));

                $row.find('span.cd_product').text(item.cd);
                $row.find('span.nm_product').text(item.text);
                $row.find('input[data-field="id_product"]').val(item.id);

                $row.find('input[data-field="transfer_qty_send"]').val('1');
                var $select = $row.find('select[data-field="id_uom"]').html('');
                $.each(item.uoms, function() {
                    $select.append($('<option>').val(this.id).text(this.nm).attr('data-isi', this.isi));
                });

                $grid.find('tbody > tr').removeClass('selected');
                $row.addClass('selected');
                $grid.children('tbody').append($row);
                $row.find('input[data-field="transfer_qty_send"]').focus();
            }
            //local.normalizeItem();
        },
        format: function(n) {
            return $.number(n, 0);
        },
        normalizeItem: function() {
            var total = 0.0;
            $('#detail-grid > tbody > tr').each(function() {
                var $row = $(this);
                var q = $row.find('input[data-field="purch_qty"]').val();
                q = q == '' ? 1 : q;
                var isi = 1; //$row.find('[data-field="id_uom"] > :selected').data('isi');
                isi = isi ? isi : 1;

                var t = isi * q * $row.find('input[data-field="purch_price"]').val();
                $row.find('span.total-price').text(local.format(t));
                $row.find('input[data-field="total_price"]').val(t);
                total += t;
            });
            $('#purchasehdr-purchase_value').val(total);
            $('#total-price').text(local.format(total));
        },
        searchProductByCode: function(cd) {
            var whse = $('#transferhdr-id_warehouse_source').val();
            if (local.checkStock && (whse == '' || biz.master.ps[whse] == undefined)) {
                return false;
            }
            var id = biz.master.barcodes[cd] + '';
            if (id && biz.master.product[id]) {
                if (local.checkStock && (biz.master.ps[whse][id] == undefined || biz.master.ps[whse][id] <= 0)) {
                    return false;
                }
                return biz.master.product[id];
            }
            return false;
        },
        onProductChange: function() {
            var item = local.searchProductByCode(this.value);
            if (item !== false) {
                local.addItem(item);
            }
            this.value = '';
            $(this).autocomplete("close");
        },
        initRow: function() {
            $('#detail-grid > tbody > tr').each(function() {
                var $row = $(this);
                var product = biz.master.product[$row.find('[data-field="id_product"]').val()];
                if (product) {
                    $row.find('[data-field="id_uom"] > option').each(function() {
                        var $opt = $(this);
                        var isi = product.uoms[$opt.val()].isi;
                        $opt.attr('data-isi', isi);
                        //$opt.data('isi',isi);
                    });
                }
                counter++;
            });
            local.normalizeItem();
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
                    var $this = $(this);
                    var $inputs = $this.closest('tr').find(':input:visible[data-field]:not([readonly])');
                    var idx = $inputs.index(this);
                    if (idx >= 0) {
                        if (idx < $inputs.length - 1) {
                            $inputs.eq(idx + 1).focus();
                        } else {
                            $('#product').focus();
                        }
                    }
                }
            });

            $grid.on('change', ':input[data-field]', function() {
                var $row = $(this).closest('tr');
                switch ($(this).data('field')) {
                    case 'markup_price':
                        var p = $row.find('input[data-field="purch_price"]').val();
                        var m = $row.find('input[data-field="markup_price"]').val();
                        var s = p / (1 - 0.01 * m);
                        $row.find('input[data-field="selling_price"]').val(s.toFixed(2));
                        break;

                    case 'purch_price':
                    case 'selling_price':
                        var p = $row.find('input[data-field="purch_price"]').val();
                        var s = $row.find('input[data-field="selling_price"]').val();
                        var m = s > 0 ? 100 * (s - p) / s : 0;
                        $row.find('input[data-field="markup_price"]').val(m.toFixed(2));
                        break;
                }
                local.normalizeItem();
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
            $('#product').change(local.onProductChange);
            $('#product').focus();

            $(window).keydown(function(event) {
                if (event.keyCode == 13) {
                    if ($(event.target).is('#product')) {
                        $('#product').change();
                    } else {
                        event.preventDefault();
                    }
                    return false;
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
        onProductSelect: function(event, ui) {
            local.addItem(ui.item);
        },
    };
    return pub;
})(window.jQuery);