<?php

namespace Drupal\idix_media_source\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

class OEmbedToTextWidget extends WidgetBase {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $formState){
    $element['value'] = $element + [
        '#type' => 'textarea',
        '#default_value' => $items[$delta]->value,
        '#rows' => $this->getSetting('rows'),
        '#placeholder' => $this->getSetting('placeholder'),
        '#attributes' => ['class' => ['js-text-full', 'text-full']],
      ];
  }

}