## Laravel - Sigavi

[![Downloads](https://img.shields.io/packagist/dt/agenciafmd/laravel-sigavi.svg?style=flat-square)](https://packagist.org/packages/agenciafmd/laravel-rdstation)
[![Licença](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

- Envia as conversões para a Sigavi

## Instalação

```bash
composer require agenciafmd/laravel-sigavi:dev-master
```

## Configuração

Para que a integração seja realizada, precisamos do **endpoint, username e senha**

É necessário colocar esses dados no .env

```dotenv
SIGAVI_ENDPOINT=endpoint da api
SIGAVI_USERNAME=username
SIGAVI_PASSWORD=sua senha
SIVAGI_ORIGEM=string que identifica a origem
```

## Uso

Envie os campos no formato de array para o SendConversionsToSigavi.

O campo **email** é obrigatório =)

Para que o processo funcione pelos **jobs**, é preciso passar os valores dos cookies conforme mostrado abaixo.

```php
use Agenciafmd\Sigavi\Jobs\SendConversionsToSigavi;

$data['email'] = 'milena@fmd.ag';
$data['nome'] = 'Milena Ramiro';

SendConversionsToSigavi::dispatch($data + [
        'identificador' => 'seja-um-parceiro',
        'utm_campaign' => Cookie::get('utm_campaign', ''),
        'utm_content' => Cookie::get('utm_content', ''),
        'utm_medium' => Cookie::get('utm_medium', ''),
        'utm_source' => Cookie::get('utm_source', ''),
        'utm_term' => Cookie::get('utm_term', ''),
        'gclid_' => Cookie::get('gclid', ''),
        'cid' => Cookie::get('cid', ''),
    ])
    ->delay(5)
    ->onQueue('low');
```

Note que no nosso exemplo, enviamos o job para a fila **low**.

Certifique-se de estar rodando no seu queue:work esteja semelhante ao abaixo.

```shell
php artisan queue:work --tries=3 --delay=5 --timeout=60 --queue=high,default,low
```