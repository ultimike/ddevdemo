<?php

namespace Drupal\select2_all;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Methods for adding Select2 to render elements.
 */
class Select2 implements TrustedCallbackInterface {

  /**
   * Render API callback: Apply Select2 to a select element.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The element.
   */
  public static function preRenderSelect(array $element) {
    // Exclude Select2 from theme other than admin.
    $theme = \Drupal::theme()->getActiveTheme()->getName();
    $admin_theme = \Drupal::config('system.theme')->get('admin');
    $is_admin_path = \Drupal::service('router.admin_context')->isAdminRoute();
    $is_admin = $is_admin_path || $theme == $admin_theme;

    // $select2_include = \Drupal::config('select2.settings')->get('select2_include');
    // if ($select2_include != SELECT2_INCLUDE_EVERYWHERE && $is_admin == $select2_include) {
    //   return $element;
    // }

    // If the #select2 FAPI property is set, then add the appropriate class.
    if (isset($element['#select2'])) {
      if (!empty($element['#select2'])) {
        // Element has opted-in for Select2, ensure the library gets added.
        $element['#attributes']['class'][] = 'select2-enable';
      }
      else {
        $element['#attributes']['class'][] = 'select2-disable';
        // Element has opted-out of Select2. Do not add the library now.
        return $element;
      }
    }
    elseif (isset($element['#attributes']['class']) && is_array($element['#attributes']['class'])) {
      if (array_intersect($element['#attributes']['class'], ['select2-disable'])) {
        // Element has opted-out of Select2. Do not add the library now.
        return $element;
      }
      elseif (array_intersect($element['#attributes']['class'], ['select2-enable'])) {
        // Element has opted-in for Select2, ensure the library gets added.
      }
    }
    else {
      // Neither the #select property was set, nor any select2 classes found.
      // This element still might match the site-wide criteria, so add the library.
    }

    if (isset($element['#field_name']) && !empty($element['#multiple'])) {
      // Remove '_none' from multi-select options.
      unset($element['#options']['_none']);

      if (isset($element['#entity_type'], $element['#bundle'], $element['#cardinality'])) {
        // Set data-cardinality for fields that aren't unlimited.
        if ($element['#cardinality'] != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && $element['#cardinality'] > 1) {
          $element['#attributes']['data-cardinality'] = $element['#cardinality'];
        }
      }
    }

    // Attach the library.
    static::attachLibrary($element);

    return $element;
  }

  /**
   * Helper function to attach the Select2 library and settings to a given element.
   *
   * @param array &$element
   *   An render array element.
   */
  public static function attachLibrary(array &$element) {
    $element['#attached']['library'][] = 'select2_all/drupal.select2';

    $config = \Drupal::config('select2.settings');

    $options = [
      // 'disable_search' => (bool) $config->get('disable_search'),
      // 'disable_search_threshold' => (int) $config->get('disable_search_threshold'),
      // 'allow_single_deselect' => (bool) $config->get('allow_single_deselect'),
      // 'search_contains' => (bool) $config->get('search_contains'),
      // 'placeholder_text_multiple' => $config->get('placeholder_text_multiple'),
      // 'placeholder_text_single' => $config->get('placeholder_text_single'),
      // 'no_results_text' => $config->get('no_results_text'),
      // 'inherit_select_classes' => TRUE,
    ];

    $language_direction = \Drupal::languageManager()->getCurrentLanguage()->getDirection();
    if (LanguageInterface::DIRECTION_RTL === $language_direction) {
      $options['dir'] = 'rtl';
    }

    $element['#attached']['drupalSettings']['select2'] = [
      // 'selector' => $config->get('jquery_selector'),
      // 'minimum_single' => (int) $config->get('minimum_single'),
      // 'minimum_multiple' => (int) $config->get('minimum_multiple'),
      // 'minimum_width' => (int) $config->get('minimum_width'),
      // 'use_relative_width' => (bool) $config->get('use_relative_width'),
      'options' => $options,
    ];
  }

  /**
   * Render API callback: Apply Select2 to a date_combo element.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The element.
   */
  public static function preRenderDateCombo(array $element) {
    // Because the date_combo field contains many different select elements, we
    // need to recurse down and apply the FAPI property to each one.
    if (isset($element['#select2'])) {
      static::elementApplyPropertyRecursive($element, $element['#select2']);
    }
    return $element;
  }

  /**
   * Render API callback: Apply Select2 to a select_or_other element.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The element.
   */
  public static function preRenderSelectOrOther(array $element) {
    if ($element['#select_type'] === 'select' && isset($element['#select2'])) {
      $element['select']['#select2'] = $element['#select2'];
    }
    return $element;
  }

  /**
   * Recurse through an element to apply the Select2 property to any select fields.
   *
   * @param array $element
   *   The element.
   * @param int $select2_value
   *   Select2 setting.
   */
  public static function elementApplyPropertyRecursive(array &$element, $select2_value = NULL) {
    if (!isset($select2_value)) {
      if (isset($element['#select2'])) {
        $select2_value = $element['#select2'];
      }
      else {
        return;
      }
    }

    if (isset($element['#type']) && $element['#type'] === 'select') {
      $element['#chosen'] = $select2_value;
    }

    foreach (Element::children($element) as $key) {
      static::elementApplyPropertyRecursive($element[$key], $select2_value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderSelect',
      'preRenderDateCombo',
      'preRenderSelectOrOther',
    ];
  }

}
