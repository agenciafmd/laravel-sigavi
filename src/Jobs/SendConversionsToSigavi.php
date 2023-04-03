<?php

namespace Agenciafmd\Sigavi\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SendConversionsToSigavi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle()
    {
        if (!config('laravel-sigavi.endpoint')
            || !config('laravel-sigavi.username')
            || !config('laravel-sigavi.password')) {
            return false;
        }

        $client = $this->getClientRequest();
        $endpoint = config('laravel-sigavi.endpoint') . '/Acesso/Token';

        $formParams = [
            'username' => config('laravel-sigavi.username'),
            'password' => config('laravel-sigavi.password'),
            'grant_type' => 'password',
        ];

        $this->access_token = Cache::remember('access_token', now()->addHours(12),
            function () use ($client, $endpoint, $formParams) {
                $response = $client->request('POST', $endpoint, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'form_params' => $formParams
                ]);

                $responseBody = json_decode($response->getBody());
                return $responseBody->access_token;
            });

        $this->sendConversion();
    }

    private function sendConversion()
    {
        $client = $this->getClientRequest();

        $queryParam = '';
        if (config('laravel-sigavi.origem')){
            $queryParam = '?origem=' . config('laravel-sigavi.origem');
        }

        $endpoint = config('laravel-sigavi.endpoint') . '/Leads/Integracao' . $queryParam;

        $client->request('POST', $endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . $this->access_token
            ],
            'json' => $this->data
        ]);
    }

    private function getClientRequest()
    {
        $logger = new Logger('Sigavi');
        $logger->pushHandler(new StreamHandler(storage_path('logs/sigavi-' . date('Y-m-d') . '.log')));

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter("{method} {uri} HTTP/{version} {req_body} | RESPONSE: {code} - {res_body}")
            )
        );

        return new Client([
            'timeout' => 60,
            'connect_timeout' => 60,
            'http_errors' => false,
            'verify' => false,
            'handler' => $stack,
        ]);
    }
}
