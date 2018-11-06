<?php declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;

class NasaApiService
{
    const API_KEY = 'a9454qYBATkVczyjD9Sn7T8E9PeBSbTpGTPIRcO0';
    const APOD_URI = 'https://api.nasa.gov/planetary/apod';

    /** @var ClientInterface */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new GuzzleClient(
            [
                'handler' => new CurlHandler(),
            ]
        );
    }

    public function request()
    {
        $request = $this->httpClient->request(
            'GET',
            $this->resolveUriParameters()
        );

        return json_decode($request->getBody()->getContents());
    }

    private function getKey()
    {
        return self::API_KEY;
    }

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
}
