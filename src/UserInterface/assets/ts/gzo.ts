declare var BASE_URI: string;

function bindCurd() {
    $.getJSON(BASE_URI + 'sql/table', function (data) { 
        if (data.code != 200) {
            return;
        }
        let html = '<option value="">请选择</option>';
        $.each(data.data, function(i, item) {
            html += '<option value="'+item+'">'+item+'</option>';
        });
        $('#table1').html(html);
    });
}
function bindImport() {
    $.getJSON(BASE_URI + 'sql/schema', function (data) { 
        if (data.code != 200) {
            return;
        }
        let html = '<option value="">请选择</option>';
        $.each(data.data, function(i, item) {
            html += '<option value="'+item+'">'+item+'</option>';
        });
        $('#schema1').html(html);
    });
}
function bindExport() {
    bindImport();
    $('#schema1').change(function() {
        $("#table-box").hide();
        $.getJSON(BASE_URI + 'sql/table?schema='+ $(this).val(), function (data) {
            let html = '';
            if (data.code == 200) {
                $.each(data.data, function(i, item) {
                    html += '<option value="'+item+'">'+item+'</option>';
                });
            }
            $('#table1').html(html);
            $("#table-box").toggle(html != '');
        });
    });
}


function bindCopy() {
    let getSchame = function(cb: (data: string[]) => void) {
        $.getJSON(BASE_URI + 'sql/schema', function (data) { 
            if (data.code != 200) {
                return;
            }
            cb(data.data);
        });
    },
    getTable = function(schame: string, cb: (data: string[]) => void) {
        $.getJSON(BASE_URI + 'sql/table?schema='+ schame, function (data) {
            if (data.code != 200) {
                return;
            }
            cb(data.data);
        });
    },
    getColumn = function(table: string, cb: (data: any[]) => void) {
        $.getJSON(BASE_URI + 'sql/column?table='+ table, function (data) {
            if (data.code != 200) {
                return;
            }
            cb(data.data);
        });
    },
    selectBox = $('.dialog-select'),
    selectCb: (table: string) => void = undefined,
    selectTable = function(cb: (table: string) => void) {
        selectBox.show();
        selectCb = cb;
    };
    getSchame(items => {
        let html = '';
        $.each(items, function(i, item) {
            html += '<option value="'+item+'">'+item+'</option>';
        });
        selectBox.find('select[name="schame"]').html(html).trigger('change');
    });
    selectBox.on('change', 'select[name="schame"]', function() {
        getTable($(this).val() as string, items => {
            let html = '';
            $.each(items, function(i, item) {
                html += '<option value="'+item+'">'+item+'</option>';
            });
            selectBox.find('select[name="table"]').html(html).trigger('change');
        });
    }).on('click', 'button', function() {
        selectBox.hide();
        selectCb && selectCb(selectBox.find('select[name="schame"]').val() + '.'  + selectBox.find('select[name="table"]').val());
    });
    $(document).on('click', '*[data-action="table-select"]', function() {
        let $this = $(this);
        selectTable(table => {
            $this.text(table);
            getColumn(table, items => {
                let html = '';
                items.forEach(item => {
                    html += '<div class="column-item"><span>' + item.label + '</span>&lt;-<span data-action="column-select">请选择</span><i class="fa fa-times"></i></div>';
                });
                $this.closest('.panel').find('.panel-body').html(html);
            });
        });
    }).on('click', '*[data-action="table-add"]', function() {
        let $this = $(this);
        selectTable(table => {
            $this.before('<span class="table-item">' + table + '<i class="fa fa-times"></i></span>');
            getColumn(table, items => {
                let panel = $this.closest('.panel');
                panel.data('column', items);
                panel.find('.panel-body .column-item').each((i, ele) => {
                    if (items.length <= i) {
                        return;
                    }
                    $(ele).find('[data-action="column-select"]').text(items[i].label);
                });
            });
        });
    }).on('click', '.table-item .fa-times', function() {
        $(this).closest('.table-item').remove();
    }).on('click', '.column-item .fa-times', function() {
        $(this).closest('.column-item').remove();
    });
}

$(document).ready(function() {
    $(document).on('click', "button[data-type=preview]", function() {
        let $this = $(this).parents('form');
        postJson($this.attr('action')+ '?preview=true', $this.serialize(), function(data) {
            if (data.code != 200) {
                parseAjax(data);
                return;
            }
            Dialog.box({
                title: '预览',
                content: '<pre><code class="language-php">'+ Prism.highlight(data.data.code, Prism.languages.php) +'</code></pre>'
            });
        });
        return false;
    });
});