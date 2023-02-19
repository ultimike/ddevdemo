/**
 * @file
 * Select2 behaviors.
 */

(function ($, Drupal, drupalSettings, once) {
  'use strict';

  /**
   * Attaches the select2 behavior to all relevant select fields.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the select2 behaviors.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the select2 behaviors.
   */
  Drupal.behaviors.select2 = {

    settings: {

      /**
       * Completely ignores elements that match one of these selectors.
       *
       * Disabled on:
       * - Field UI
       * - WYSIWYG elements
       * - Tabledrag weights
       * - Elements in the off-canvas dialog
       * - Elements that have opted-out of Select2
       * - Elements already processed by Select2.
       *
       * @type {string}
       */
      ignoreSelector: '#field-ui-field-storage-add-form select, #entity-form-display-edit-form select, #entity-view-display-edit-form select, .wysiwyg, .draggable select[name$="[weight]"], .draggable select[name$="[position]"], #drupal-off-canvas select, .locale-translate-filter-form select, .select2-disable, .select2-processed',

      /**
       * Explicit "opt-in" selector.
       *
       * @type {string}
       */
      optedInSelector: 'select.select2-enable',

      /**
       * The default selector, overridden by drupalSettings.
       *
       * @type {string}
       */
      selector: 'select:visible',

      // @todo Make these configurable.
      minimum_multiple: 5,
      minimum_single: 10
    },

    /**
     * Drupal attach behavior.
     */
    attach: function (context, settings) {
      once('select2', this.getElements(context)).forEach(function (element) {
        this.createSelect2(element);
      }.bind(this));
    },

    /**
     * Drupal detach behavior.
     */
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        once.remove('select2', 'select', context).forEach(function (element) {
          $(element).select2('destroy');
        });
      }
    },

    /**
     * Creates a Select2 instance for a specific element.
     *
     * @param {jQuery|HTMLElement} element
     *   The element.
     */
    createSelect2: function (element) {
      var $element = $(element);
      $element.select2(this.getElementOptions($element));
    },

    /**
     * Filter out elements that should not be converted into Select2.
     *
     * @param {jQuery|HTMLElement} element
     *   The element.
     *
     * @return {boolean}
     *   TRUE if the element should stay, FALSE otherwise.
     */
    filterElements: function (element) {
      var $element = $(element);

      // Remove elements that should be ignored completely.
      if ($element.is(this.settings.ignoreSelector)) {
        return false;
      }

      // Zero value means no minimum.
      var minOptions = $element.attr('multiple') ? this.settings.minimum_multiple : this.settings.minimum_single;
      return !minOptions || $element.find('option').length >= minOptions;
    },

    /**
     * Retrieves the elements that should be converted into Select2 instances.
     *
     * @param {jQuery|Element} context
     *   A DOM Element, Document, or jQuery object to use as context.
     * @param {string} [selector]
     *   A selector to use, defaults to the default selector in the settings.
     */
    getElements: function (context, selector) {
      var $context = $(context || document);
      var $elements = $context.find(selector || this.settings.selector);

      // Remove elements that should not be converted into Select2.
      $elements = $elements.filter(function(i, element) {
        return this.filterElements(element);
      }.bind(this));

      // Add elements that have explicitly opted in to Select2.
      $elements = $elements.add($context.find(this.settings.optedInSelector));

      return $elements;
    },

    /**
     * Retrieves options used to create a Select2 instance based on an element.
     *
     * @param {jQuery|HTMLElement} element
     *   The element to process.
     *
     * @return {Object}
     *   The options object used to instantiate a Select2 instance with.
     */
    getElementOptions: function (element) {
      var $element = $(element);
      var options = $.extend({}, this.settings.options);
      var dimension;
      var width;

      // The width default option is considered the minimum width, so this
      // must be evaluated for every option.
      if (this.settings.minimum_width > 0) {
        // Given we need to manage settings as both percentage and pixel widths,
        // we need to handle width calculations separately.
        if (this.settings.use_relative_width) {
          dimension = '%';
          width = ($element.width() / $element.parent().width() * 100).toPrecision(5);
        }
        else {
          dimension = 'px';
          width = $element.width();
        }

        if (width < this.settings.minimum_width) {
          options.width = this.settings.minimum_width + dimension;
        }
        else {
          options.width = width + dimension;
        }
      }

      // Some field widgets have cardinality, so we must respect that.
      // @see select2_pre_render_select()
      var cardinality;
      if ($element.attr('multiple') && (cardinality = $element.data('cardinality'))) {
        options.maximumSelectionLength = cardinality;
      }

      return options;
    },

    /**
     * Retrieves the settings passed from Drupal.
     *
     * @param {Object} [settings]
     *   Passed Drupal settings object, if any.
     */
    getSettings: function (settings) {
      return $.extend(true, {}, this.settings, settings && settings.select2 || drupalSettings.select2);
    }

  };

})(jQuery, Drupal, drupalSettings, once);
