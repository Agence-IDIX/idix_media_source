<?php

namespace Drupal\idix_media_source\Plugin\Field\FieldWidget;

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
      '#tree' => true,
      '#attributes' => [
        'id' => ['edit-field-media-idix-generic-' . $delta . '-fieldset']
      ]
    ];

    $new_element['fieldset']['container'] = [
      '#type' => 'container',
    ];

    $new_element['fieldset']['container']['oembed_url'] = [
      '#type' => 'textfield',
      '#title' => 'Url du media'
    ];

    $new_element['fieldset']['container']['error'] = [
      '#type' => 'markup',
      '#markup' => '',
    ];

    $new_element['fieldset']['container']['load'] = [
      '#type' => 'button',
      '#value' => "Transformer l'url en code embed",
      '#ajax' => [
        'callback' => [$this, 'transformUrlToEmbed'],
        'wrapper' => 'edit-field-media-idix-generic-' . $delta . '-fieldset',
      ],
      '#attributes' => [
        // 'class' => ['inline'],
      ],
      '#name' => 'load-' . $delta,
    ];

    $new_element['fieldset']['value'] = [
      '#type' => 'textarea',
      '#title' => 'Code embed',
      '#default_value' => $items[$delta]->value,
      '#rows' => $this->getSetting('rows'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#attributes' => ['class' => ['js-text-full', 'text-full']],
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
    $parents = $trigger['#parents'];
    $array_parents = $trigger['#array_parents'];

    $value = $form_state->getValue($parents[0]);

    $url = $value[$parents[1]]['fieldset']['container']['oembed_url'];

    try {
      $resource_url = $url_resolver->getResourceUrl($url);
      $resource = $resource_fetcher->fetchResource($resource_url);

      $markup = IFrameMarkup::create($resource->getHtml());

      $form[$array_parents[0]][$array_parents[1]][$array_parents[2]][$array_parents[3]]['value']['#value'] = $markup->__toString();

      $form[$array_parents[0]][$array_parents[1]][$array_parents[2]][$array_parents[3]]['container']['error']['#markup'] = '';
    }catch(ResourceException $e){
      $form[$array_parents[0]][$array_parents[1]][$array_parents[2]][$array_parents[3]]['container']['error']['#markup'] = '<div class="error">L\'url indiquée ne correspond pas à un média compatible oEmbed, veuillez directement copier/coller le code d\'insertion dans le champ ci-dessous.</div>';
    }

    return $form[$array_parents[0]][$array_parents[1]];
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