<?php

namespace Drupal\idix_media_source\Plugin\media\Source;

use Drupal\media\Plugin\media\Source\OEmbed as OEmbedBase;

use Drupal\Component\Utility\Crypt;
use Drupal\media\OEmbed\Resource;
use GuzzleHttp\Exception\RequestException;

class OEmbed extends OEmbedBase {

  protected function getLocalThumbnailUri(Resource $resource) {
    // If there is no remote thumbnail, there's nothing for us to fetch here.
    $remote_thumbnail_url = $resource->getThumbnailUrl();
    if (!$remote_thumbnail_url) {
      return NULL;
    }
    $remote_thumbnail_url = $remote_thumbnail_url->toString();

    // Compute the local thumbnail URI, regardless of whether or not it exists.
    $configuration = $this->getConfiguration();
    $directory = $configuration['thumbnails_directory'];
    $extension = pathinfo($remote_thumbnail_url, PATHINFO_EXTENSION);
    $exp_extension = explode('?', $extension);
    if(count($exp_extension) > 1){
      $extension = $exp_extension[0];
    }
    $local_thumbnail_uri = "$directory/" . Crypt::hashBase64($remote_thumbnail_url) . '.' . $extension;

    // If the local thumbnail already exists, return its URI.
    if (file_exists($local_thumbnail_uri)) {
      return $local_thumbnail_uri;
    }

    // The local thumbnail doesn't exist yet, so try to download it. First,
    // ensure that the destination directory is writable, and if it's not,
    // log an error and bail out.
    if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      $this->logger->warning('Could not prepare thumbnail destination directory @dir for oEmbed media.', [
        '@dir' => $directory,
      ]);
      return NULL;
    }

    $error_message = 'Could not download remote thumbnail from {url}.';
    $error_context = [
      'url' => $remote_thumbnail_url,
    ];
    try {
      $response = $this->httpClient->get($remote_thumbnail_url);
      if ($response->getStatusCode() === 200) {
        $success = file_unmanaged_save_data((string) $response->getBody(), $local_thumbnail_uri, FILE_EXISTS_REPLACE);

        if ($success) {
          return $local_thumbnail_uri;
        }
        else {
          $this->logger->warning($error_message, $error_context);
        }
      }
    }
    catch (RequestException $e) {
      $this->logger->warning($e->getMessage());
    }
    return NULL;
  }
  
}