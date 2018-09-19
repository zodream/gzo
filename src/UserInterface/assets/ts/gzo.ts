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