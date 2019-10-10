<?php

namespace Drupal\idix_media_source\Plugin\media\Source;

use Drupal\media\MediaSourceBase;

/**
 * Generic media source.
 *
 * @MediaSource(
 *   id = "idix_generic",
 *   label = @Translation("IDIX Generic media"),
 *   description = @Translation("IDIX Generic media type."),
 *   allowed_field_types = {"string_long"},
 *   default_thumbnail_filename = "generic.png"
 * )
 */
class Generic extends MediaSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function createSourceFieldStorage() {
    return parent::createSourceFieldStorage()->set('custom_storage', TRUE);
  }

}
