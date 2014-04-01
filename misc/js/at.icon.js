(function($){

var icon_info = function(service, icon_set, name) {
  return ''
    + '<table style="width: 100%;">'
    + '  <tr><td><strong>Service:</strong></td> <td>'+ service +'</td></tr>'
    + '  <tr><td><strong>Set:</strong></td>     <td>'+ icon_set +'</td></tr>'
    + "  <tr><td><strong>PHP:</strong></td>     <td><code>&lt;?php at_icon('"+ icon_set +"/"+ name +"', '"+ service +"'); ?&gt;</code></td></tr>"
    + "  <tr><td><strong>Twig:</strong></td>    <td><code>{{ '"+ icon_set +"/"+ name +"'|icon('"+ service +"') }}</code></td></tr>"
    + '</table>';
};

Drupal.behaviors.atuiIcon = {
  attach: function(context, settings) {
    var service  = $('.at-icon-list').data('service');
    var icon_set = $('.at-icon-list').data('set');

    $('.at-icon-list li i', context).once('atuiIcon')
      .append(function(){ return '<span>' + $(this).data('name') + '</span>'; })
      .click(function(){
        var name = $(this).data('name');
        var icon = $(this).parent().html();
        var title = icon + ' — ' + service + '/' + icon_set;
        $(icon_info(service, icon_set, name)).dialog({
          title: title,
          width: '800px',
          open: function() {
            // $(this).css('width', '100%');
            $(icon)
              .find('span').remove().end()
              .css({'font-size': '5em', 'float': 'right', 'padding': '33px'})
              .insertBefore($(this));
          }
        });
      })
    ;
  }
};

})(jQuery);
