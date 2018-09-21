<?php

namespace Drupal\idix_media_source;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class IdixMediaSourceServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('media.oembed.provider_repository');
    $definition->setClass('Drupal\idix_media_source\OEmbed\ProviderRepository');
  }

}