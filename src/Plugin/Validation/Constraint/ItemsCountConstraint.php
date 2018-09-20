<?php

namespace Drupal\idix_media_source\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check number of slideshow items.
 *
 * @Constraint(
 *   id = "ItemsCount",
 *   label = @Translation("Slideshow items count", context = "Validation"),
 * )
 */
class ItemsCountConstraint extends Constraint {

  /**
   * Source field name.
   *
   * @var string
   */
  public $sourceFieldName;

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'At least one slideshow item must exist.';

}
