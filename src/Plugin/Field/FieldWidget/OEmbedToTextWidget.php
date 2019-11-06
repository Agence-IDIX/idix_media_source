<?php

namespace Drupal\idix_media_source\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextareaWidget;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\media\IFrameMarkup;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcher;
use Drupal\media\OEmbed\UrlResolver;

/**
 * Plugin implementation of the 'string_textarea' widget.
 *
 * @FieldWidget(
 *   id = "oembed_to_text",
 *   label = @Translation("OEmbed to Text area"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class OEmbedToTextWidget extends StringTextareaWidget {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $formState){
    $new_element['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $element['#title'],
      '#tree' => false,
    ];

    $new_element['fieldset']['container'] = [
      '#type' => 'container',
      '#tree' => false,
    ];

    $new_element['fieldset']['container']['oembed_url'] = [
      '#type' => 'textfield',
      '#title' => 'Url du media'
    ];

    $new_element['fieldset']['container']['error'] = [
      '#type' => 'markup',
      '#markup' => '',
      '#attributes' => [
        'id' => ['edit-field-media-idix-generic-' . $delta . '-error'],
      ]
    ];

    $new_element['fieldset']['container']['load'] = [
      '#type' => 'button',
      '#value' => "Transformer l'url en code embed",
      '#limit_validation_errors' => [

      ],
      '#ajax' => [
        'callback' => [$this, 'transformUrlToEmbed'],
        //'wrapper' => 'edit-field-media-idix-generic-' . $delta . '-fieldset',
      ],
      '#name' => 'load-' . $delta,
    ];

    $new_element['fieldset']['value'] = [
      '#prefix' => '<div id="edit-field-media-idix-generic-' . $delta . '-value">',
      '#suffix' => '</div>',
      '#type' => 'textarea',
      '#title' => 'Code embed',
      '#default_value' => $items[$delta]->value,
      '#rows' => $this->getSetting('rows'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#attributes' => [
        'class' => ['js-text-full', 'text-full'],
        //'id' => ['edit-field-media-idix-generic-' . $delta . '-value']
      ],
      '#required' => $element['#required'],
    ];

    return $new_element;
  }

  public function transformUrlToEmbed(array &$form, FormStateInterface $form_state){
    /** @var UrlResolver $url_resolver */
    $url_resolver = \Drupal::service('media.oembed.url_resolver');
    /** @var ResourceFetcher $resource_fetcher */
    $resource_fetcher = \Drupal::service('media.oembed.resource_fetcher');

    $trigger = $form_state->getTriggeringElement();
    $array_parents = $trigger['#array_parents'];
    array_pop($array_parents);
    array_pop($array_parents);
    array_push($array_parents, 'value');

    $delta = str_replace('load-', '', $trigger['#name']);

    $url = $form_state->getValue('oembed_url');

    $response = new AjaxResponse();

    try {
      $resource_url = $url_resolver->getResourceUrl($url);
      $resource = $resource_fetcher->fetchResource($resource_url);

      $markup = IFrameMarkup::create($resource->getHtml());

      $response->addCommand(new HtmlCommand('#edit-field-media-idix-generic-' . $delta . '-error', ''));

      $value_element = NestedArray::getValue($form, $array_parents);

      $value_element['#default_value'] = $markup->__toString();
      $value_element['#value'] = $markup->__toString();
      unset($value_element['#prefix']);
      unset($value_element['#suffix']);

      $test = true;

      $response->addCommand(new ReplaceCommand('#edit-field-media-idix-generic-' . $delta . '-value', $value_element));

    }catch(ResourceException $e){
      $response->addCommand(new HtmlCommand('#edit-field-media-idix-generic-' . $delta . '-error', ''));
    }

    return $response;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state)
  {
    $raw_values = $values;
    $values = [];

    foreach($raw_values as $raw_value){
      $delta = $raw_value['_original_delta'];
      $value = $raw_value['fieldset']['value'];
      $values[$delta]['value'] = $value;
    }

    return $values;
  }

}