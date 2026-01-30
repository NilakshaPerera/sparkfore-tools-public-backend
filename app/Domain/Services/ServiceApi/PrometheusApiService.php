<?php

namespace App\Domain\Services\ServiceApi;

use GuzzleHttp\Client;

class PrometheusApiService implements PrometheusApiServiceInterface
{
    private $prometheusUrl;
    private $prometheusPwd;
    private $prometheusUsername;
    private $client;


    public function __construct()
    {
        $this->prometheusPwd = config('sparkfore.prometheus_pwd');
        $this->prometheusUsername = config('sparkfore.prometheus_username');

        $this->client = new Client([
            'auth' => [$this->prometheusUsername, $this->prometheusPwd]
        ]);
    }

    /**
     * @param $url
     * @param $type
     * @return array|false
     */
    public function getInstallations($query, $server)
    {

        if ($server == 'cleura') {
            $this->prometheusUrl = 'https://prometheus.sto2.cleura.onsparkfore.com/prometheus/api/v1/query';
        } elseif ($server == 'digitalocean') {
            $this->prometheusUrl = 'https://prometheus.fra1.digitalocean.onsparkfore.com/prometheus/api/v1/query';
        }

        $response = $this->client->request('GET', $this->prometheusUrl, [
            'query' => $query
        ]);

        $body = $response->getBody();
        return json_decode($body, true);
    }
}
