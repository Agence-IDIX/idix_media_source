<?php

namespace Drupal\idix_media_source\Plugin\media\Source;

use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaSourceEntityConstraintsInterface;

/**
 * Provides media type plugin for Slideshows.
 *
 * @MediaSource(
 *   id = "diaporama",
 *   label = @Translation("Diaporama"),
 *   description = @Translation("Provides business logic and metadata for slideshows."),
 *   default_thumbnail_filename = "slideshow.png",
 *   allowed_field_types = {"entity_reference_revisions"},
 * )
 */
class Diaporama extends MediaSourceBase implements MediaSourceEntityConstraintsInterface {

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    $attributes = [
      'length' => $this->t('Slideshow length'),
    ];

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $name) {
    $source_field = $this->configuration['source_field'];

    switch ($name) {
      case 'default_name':
        // The default name will be the timestamp + number of slides.
        $length = $this->getMetadata($media, 'length');
        if (!empty($length)) {
          return $this->formatPlural($length,
            '1 slide, created on @date',
            '@count slides, created on @date',
            [
              '@date' => \Drupal::service('date.formatter')
                ->format($media->getCreatedTime(), 'custom', DATETIME_DATETIME_STORAGE_FORMAT),
            ]);
        }
        return parent::getMetadata($media, 'default_name');

      case 'length':
        return $media->{$source_field}->count();

      case 'thumbnail_uri':
        $source_field = $this->configuration['source_field'];
        $slideshow_item = null;

        /** @var \Drupal\media\MediaInterface $slideshow_item */
        if (isset($media->{$source_field}->target_id) && !empty($media->{$source_field}->target_id)) {
            $media_id = $media->{$source_field}->target_id;
        } else if (isset($media->{$source_field}->entity->field_diapositives)
            && isset($media->{$source_field}->entity->field_diapositives->entity->field_image)
            && isset($media->{$source_field}->entity->field_diapositives->entity->field_image->entity)
        ) {
            $media_id = $media->{$source_field}->entity->field_diapositives->entity->field_image->entity->id();
        }

        if (isset($media_id)) {
            $slideshow_item = $this->entityTypeManager->getStorage('media')->load($media_id);
        }

        if (!$slideshow_item) {
            return parent::getMetadata($media, 'thumbnail_uri');
        }
          
        /** @var \Drupal\media\MediaTypeInterface $bundle */
        $bundle = $this->entityTypeManager->getStorage('media_type')->load($slideshow_item->bundle());
        if (!$bundle) {
          return parent::getMetadata($media, 'thumbnail_uri');
        }

        $thumbnail = $bundle->getSource()->getMetadata($slideshow_item, 'thumbnail_uri');
        if (!$thumbnail) {
          return parent::getMetadata($media, 'thumbnail_uri');
        }

        return $thumbnail;

      default:
        return parent::getMetadata($media, $name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityConstraints() {
    $source_field = $this->configuration['source_field'];

    return ['ItemsCount' => ['sourceFieldName' => $source_field]];
  }

}
