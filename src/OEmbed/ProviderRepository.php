<?php

namespace Drupal\idix_media_source\OEmbed;

use Drupal\media\OEmbed\ProviderRepository as ProviderRepositoryBase;
use Drupal\media\OEmbed\ProviderException;
use Drupal\media\OEmbed\Provider;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;

class ProviderRepository extends ProviderRepositoryBase {

  public function normalizeProviderName($name){
    $name = strtolower($name);
    $name = preg_replace("/\([^)]+\)/", "", $name);
    $name = trim($name);
    return $name;
  }

  public function getAll() {
    $cache_id = 'media:oembed_providers';

    $cached = $this->cacheGet($cache_id);
    if ($cached) {
      return $cached->data;
    }

    try {
      $response = $this->httpClient->request('GET', $this->providersUrl);
    }
    catch (RequestException $e) {
      throw new ProviderException("Could not retrieve the oEmbed provider database from $this->providersUrl", NULL, $e);
    }

    $providers = Json::decode((string) $response->getBody());

    if (!is_array($providers) || empty($providers)) {
      throw new ProviderException('Remote oEmbed providers database returned invalid or empty list.');
    }

    $keyed_providers = [];
    foreach ($providers as $provider) {
      try {
        $name = (string) $provider['provider_name'];
        $name = $this->normalizeProviderName($name);
        $keyed_providers[$name] = new Provider($name, $provider['provider_url'], $provider['endpoints']);
      }
      catch (ProviderException $e) {
        // Just skip all the invalid providers.
        // @todo Log the exception message to help with debugging.
      }
    }

    $this->cacheSet($cache_id, $keyed_providers, $this->time->getCurrentTime() + $this->maxAge);
    return $keyed_providers;
  }

  public function get($provider_name) {
    $provider_name = $this->normalizeProviderName($provider_name);
    return parent::get($provider_name);
  }

}