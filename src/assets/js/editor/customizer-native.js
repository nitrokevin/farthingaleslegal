/**
 * Customizer Preview JavaScript
 * Handles live preview updates for theme customizer settings
 */
(function ($) {
  'use strict';

  // Nav background color
  wp.customize('color_palette_setting_0', function (value) {
    value.bind(function (newval) {
      $('.top-bar, .top-bar ul, .title-bar').css('background-color', newval);
    });
  });

  // Nav menu item color
  wp.customize('color_palette_setting_1', function (value) {
    value.bind(function (newval) {
      $('.top-bar, .top-bar .desktop-menu a:not(.button), .title-bar .mobile-menu a:not(.button)').css('color', newval);
    });
  });

  // Footer background color
  wp.customize('color_palette_setting_3', function (value) {
    value.bind(function (newval) {
      $('.footer').css('background-color', newval);
    });
  });

  // Footer text color
  wp.customize('color_palette_setting_4', function (value) {
    value.bind(function (newval) {
      $('.footer, .footer li').css('color', newval);
    });
  });

  // Footer link color
  wp.customize('color_palette_setting_5', function (value) {
    value.bind(function (newval) {
      $('.footer a').css('color', newval);
    });
  });

  // Page background color
  wp.customize('color_palette_setting_10', function (value) {
    value.bind(function (newval) {
      $('body').css('background-color', newval);
    });
  });

})(jQuery);