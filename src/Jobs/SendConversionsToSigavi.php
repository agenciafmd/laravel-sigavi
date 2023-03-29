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

        $formParams = [
            'username' => config('laravel-sigavi.username'),
            'password' => config('laravel-sigavi.password'),
            'grant_type' => 'password',
        ];

        $response = $client->request('POST', config('laravel-sigavi.endpoint'), [
            'headers' => ['Content-type: application/x-www-form-urlencoded'],
            'form_params' => $formParams,
        ]);

//        dd($response->getBody()->getContents());

        $this->sendConversion();
    }

    private function sendConversion()
    {

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
