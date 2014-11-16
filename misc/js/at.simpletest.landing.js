(function ($, Drupal, window) {
    var addLinks = function(context) {
        $('#simpletest-form-table tbody .simpletest-group', context).each(function() {
            var name = $('.simpletest-select-all', $(this)).attr('id');
            var path = Drupal.settings.basePath + 'admin/config/development/testing/' + name;
            var label = $(this).find('label.simpletest-group-label').text();
            $(this).find('label.simpletest-group-label').html('<a href="'+ path +'">'+ label +'</a>');
        });
    };

    var addSelector = function(context, group) {
        var $table = $('#simpletest-form-table tbody', context);
        var $selector = $('<tr><td colspan="3"></td></tr>');

        $selector
                .find('td')
                    .append('<div class="form-item form-type-select"></div>')
                    .end()
                .find('.form-type-select')
                    .append('<select class="form-select" id="at-test-group-selector"></select>')
                    .end()
                .find('select')
                    .append('<option value="">-- Select a group --</option>')
                    .end()
                .prependTo($table);

        $table.find('.simpletest-group').each(function () {
            var name = $('.simpletest-select-all', $(this)).attr('id');
            var label = $('.simpletest-group-label label', $(this)).text();
            $('#at-test-group-selector').append('<option value="' + name + '">' + label + '</option>');
            $(this).hide();
        });

        $('#at-test-group-selector').change(function() {
            var id = $(this).val();
            
            // hide other
            $table.find('tr.simpletest-group, tr:gt(1)').hide();

            // show selected
            $('#'+ id).parent().show();
            $('.'+ id +'-test').show();
        });
        
        $('#at-test-group-selector').val(group).trigger('change');
    };

    Drupal.behaviors.atSimpleTestLandingForm = {
        attach: function (context) {
            var group = window.location.pathname.replace(/^.+testing\/(.+)$/, '$1');
            if (!group.match(/\//)) {
                return addSelector(context, group !== 'testing' ? group : null);
            }
            return addLinks();
        }
    };

})(jQuery, Drupal, window);
