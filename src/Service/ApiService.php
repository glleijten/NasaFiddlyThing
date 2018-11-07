<?php declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class ApiService
{
    const APOD_URI = 'https://api.nasa.gov/planetary/apod';
    const APOD = 'apod';
    const NEOW_FEED = 'https://api.nasa.gov/neo/rest/v1/feed?start_date=START_DATE&end_date=END_DATE&api_key=API_KEY';
    const NEOW = 'neow';

    /** @var string Api Key */
    private $key;

    /** @var ClientInterface */
    private $httpClient;

    /** @var AdapterInterface */
    private $cacheAdapter;

    public function __construct(AdapterInterface $cacheAdapter, $key)
    {
        $this->httpClient = new GuzzleClient(
            [
                'handler' => new CurlHandler(),
            ]
        );
        $this->cacheAdapter = $cacheAdapter;
        $this->key = $key;
    }

    /**
     * @param $api
     *
     * @return bool|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function request($api)
    {
        $cacheKey = self::constructCacheKey('apod');

        try {
            $item = $this->cacheAdapter->getItem($cacheKey);
        } catch (\InvalidArgumentException $e) {
            echo 'InvalidArgumentException';
        }

        if ($item->isHit()) {
            return $item->get();
        }

        try {
            $request = $this->httpClient->request(
                'GET',
                $this->resolveUriParameters($api)
            );
        } catch (GuzzleException $e) {
            echo 'GuzzleException';
        }

        $decode = json_decode($request->getBody()->getContents());

        $item->set($decode);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cacheAdapter->save($item);

        return $decode;
    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        return $this->key;
    }

    //TODO: Figure out how to resolve parameters for two different API calls
    //TODO: Two different methods, but how to incorporate in request() method? (switch case is too fugly)

    /**
     * @param string $api
     * @param array $date
     * @param bool $hd
     *
     * @return string
     */
    private function resolveUriParameters(string $api, $date = [null], $hd = false): string
    {
        switch ($api) {
            case self::APOD:
                if ($date === [null]) {
                    $date = date('Y-m-d');
                }

                return $url = sprintf(
                    '%s?date=%s&hd=%s&api_key=%s',
                    self::APOD_URI,
                    $date,
                    $hd,
                    $this->getApiKey()
                );
                break;
            case self::NEOW:

                if ($date === [null]) {
                    $date['start_date'] = '1900-01-01';
                    $date['end_date'] = (new \DateTime('today'));
                }

                return $url = sprintf(
                    '%s?start_date=%s&end_date=%s&api_key=%s',
                    self::NEOW_FEED,
                    $date['start_date'],
                    $date['end_date'],
                    $this->getApiKey()
                );
            default:
                return $url = 'http://google.com';
        }
    }

    /**
     * @param $api
     *
     * @return string
     */
    public static function constructCacheKey($api): string
    {
        return sprintf('cacheKey_%s', md5($api));
    }
}
