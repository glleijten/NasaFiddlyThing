<?php declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class ApiService
{
    const API_KEY = 'a9454qYBATkVczyjD9Sn7T8E9PeBSbTpGTPIRcO0';
    const APOD_URI = 'https://api.nasa.gov/planetary/apod';

    /** @var ClientInterface */
    private $httpClient;

    /** @var AdapterInterface */
    private $cacheAdapter;

    public function __construct(AdapterInterface $cacheAdapter)
    {
        $this->httpClient = new GuzzleClient(
            [
                'handler' => new CurlHandler(),
            ]
        );
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * @return bool|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function request()
    {
        $cacheKey = self::constructCacheKey('api');

        try {
            $cacheItem = $this->cacheAdapter->getItem($cacheKey);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        try {
            $request = $this->httpClient->request(
                'GET',
                $this->resolveUriParameters()
            );
        } catch (GuzzleException $e) {
            return false;
        }

        $decode = json_decode($request->getBody()->getContents());

        $cacheItem->set($decode);
        $cacheItem->expiresAt(new \DateTime('tomorrow'));

        return $decode;
    }

    /**
     * @return string
     */
    private function getKey()
    {
        return self::API_KEY;
    }

    /**
     * @param null $date
     * @param bool $hd
     *
     * @return string
     */
    private function resolveUriParameters($date = null, $hd = false)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $url = sprintf(
            '%s?date=%s&hd=%s&api_key=%s',
            self::APOD_URI,
            $date,
            $hd,
            $this->getKey()
        );

        return $url;
    }

    /**
     * @param $api
     *
     * @return string
     */
    public static function constructCacheKey($api): string
    {
        return sprintf('ApiCall_%s', md5($api));
    }
}
