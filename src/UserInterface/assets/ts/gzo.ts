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
    let _caches = {};
    let clearCache = function() {
        _caches = {};
    },
    getSchame = function(cb: (data: string[]) => void) {
        const schames = Object.keys(_caches);
        if (schames.length > 0) {
            return cb(schames);
        }
        $.getJSON(BASE_URI + 'sql/schema', function (data) { 
            if (data.code != 200) {
                return;
            }
            $.each(data.data, function() {
                _caches[this] = {};
            });
            cb(data.data);
        });
    },
    getTable = function(schame: string, cb: (data: string[]) => void) {
        const tables = _caches.hasOwnProperty(schame) ? Object.keys(_caches[schame]) : [];
        if (tables.length > 0) {
            return cb(tables);
        }
        $.getJSON(BASE_URI + 'sql/table?schema='+ schame, function (data) {
            if (data.code != 200) {
                return;
            }
            let obj = {};
            $.each(data.data, function() {
                obj[this] = [];
            });
            _caches[schame] = obj;
            cb(data.data);
        });
    },
    getColumn = function(table: string, cb: (data: any[]) => void) {
        let [schame, tab] = table.split('.');
        const columns = _caches.hasOwnProperty(schame) && _caches[schame].hasOwnProperty(tab) ? _caches[schame][tab] : [];
        if (columns.length > 0) {
            return cb(columns);
        }
        $.getJSON(BASE_URI + 'sql/column?table='+ table, function (data) {
            if (data.code != 200) {
                return;
            }
            _caches[schame][tab] = data.data;
            cb(data.data);
        });
    },
    selectBox = $('.dialog-select'),
    selectColumnBox = $('.dialog-column-select'),
    selectCb: (table: any) => void = undefined,
    selectTable = function(cb: (table: string) => void) {
        selectBox.show();
        selectCb = cb;
    },
    selectColumn = function(table: string, cb: (data: any) => void) {
        getColumn(table, items => {
            let html = '';
            items.forEach(item => {
                html += '<option value="'+item.label+'">'+item.label+'</option>';
            });
            selectColumnBox.find('select').html(html);
        });
        selectColumnBox.show();
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
        const oldTable = selectBox.find('select[name="table"]').val();
        getTable($(this).val() as string, items => {
            let html = '';
            $.each(items, function(i, item) {
                html += '<option value="'+item+'" '+  (oldTable === item ? 'selected' :'')  +'>' + item + '</option>';
            });
            selectBox.find('select[name="table"]').html(html).trigger('change');
        });
    }).on('click', 'button', function() {
        selectBox.hide();
        selectCb && selectCb(selectBox.find('select[name="schame"]').val() + '.'  + selectBox.find('select[name="table"]').val());
    });
    selectColumnBox.on('click', 'button', function() {
        selectColumnBox.hide();
        let data: any = {
            type: 0,
            value: null
        };
        let input = selectColumnBox.find('[name="type"]:checked');
        if (input.length < 1) {
            return;
        }
        data.type = input.val();
        data.value = input.next().val();
        selectCb && selectCb(data);
    });
    const postCopyForm = (panel: JQuery, append: any = {}, success?: (res: any) => void) => {
        let data: any = {
            dist: '',
            src: '',
            column: {}
        };
        data.dist = panel.find('.dist-item').text();
        data.src = panel.find('.table-item').eq(0).text();
        panel.find('.column-item').each(function() {
            let dist = '', src = '';
            $(this).find('span').each(function(this: HTMLSpanElement) {
                if ($(this).data('action')) {
                    src = this.innerText;
                    return;
                }
                dist = this.innerText;
            });
            data.column[dist] = src;
        });
        postJson(panel.attr('action'), Object.assign({}, data, append), res => {
            if (res.code != 200) {
                parseAjax(res);
                return;
            }
            success(res);
        });
    };
    $(document).on('click', '*[data-action="table-select"]', function() {
        let $this = $(this);
        selectTable(table => {
            $this.text(table);
            getColumn(table, items => {
                let html = '';
                items.forEach(item => {
                    html += '<div class="column-item"><span class="dist-column">' + item.label + '</span><i>&lt;-</i><span class="src-column" data-action="column-select">请选择</span><i class="fa fa-times"></i></div>';
                });
                $this.closest('.panel').find('.panel-body').html(html);
            });
        });
    }).on('click', '*[data-action="table-add"]', function() {
        let $this = $(this);
        if ($this.closest('.panel-header').find('.dist-item').text().indexOf('请选择') >= 0) {
            Dialog.tip('请先选择目标表');
            return;
        }
        const columnName = (val: string): string => {
            return val.split('(')[0];
        };
        const columnFind = (i: number, name: string, items: any[]): any => {
            for (const item of items) {
                if (name === item.value) {
                    item.is_used = true;
                    return item;
                }
            }
            return undefined;
        };
        selectTable(table => {
            $this.before('<span class="table-item">' + table + '<i class="fa fa-times"></i></span>');
            getColumn(table, items => {
                let panel = $this.closest('.panel');
                panel.data('column', items);
                panel.find('.panel-body .column-item').each((i, ele) => {
                    const $ele = $(ele);
                    const item = columnFind(i, columnName($ele.find('.dist-column').text()), items);
                    if (!item) {
                        return;
                    }
                    $ele.find('[data-action="column-select"]').text(item.label);
                });
            });
        });
    }).on('click', '*[data-action="column-select"]', function() {
        let $this = $(this);
        let panel = $this.closest('.panel');
        let dist = panel.find('.dist-item').text();
        let srcTables = panel.find('.table-item');
        if (srcTables.length < 1) {
            Dialog.tip('请先选择数据表');
            return;
        }
        selectColumn(srcTables.eq(0).text(), data => {
            $this.text(data.type < 1 ? ('"'+ data.value +'"') : data.value);
        });
    }).on('click', '.table-item .fa-times', function() {
        $(this).closest('.table-item').remove();
    }).on('click', '.column-item .fa-times', function() {
        $(this).closest('.column-item').remove();
    }).on('click', '*[data-type="reset"]', function() {
        let panel = $(this).closest('form');
        panel.find('.dist-item').text('请选择');
        panel.find('.table-item').remove();
        panel.find('.panel-body').html('');
    }).on('submit', 'form[data-type="post"]', function(e) {
        postCopyForm($(this), {}, () => {
            Dialog.tip('复制成功');
        });
        return false;
    }).on('click', "button[data-action=preview]", function(e) {
        e.preventDefault();
        postCopyForm($(this).parents('form'), {
            preview: true
        }, res => {
            Dialog.box({
                title: '预览',
                content: `<p>${res.data.code}</p><p>${JSON.stringify(res.data.parameters)}</p>`
            });
        });
        return false;
    });
}

$(function() {
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