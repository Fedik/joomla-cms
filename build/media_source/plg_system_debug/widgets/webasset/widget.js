(function ($) {

  var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')

  PhpDebugBar.Widgets.WebAssetWidget = PhpDebugBar.Widget.extend({

    //tagName: 'div',

    className: csscls('webasset'),

    render: function () {
      console.log(this);

      this.bindAttr('data', function (data) {
        console.log(data);
      });

    },
  });

})(PhpDebugBar.$);
