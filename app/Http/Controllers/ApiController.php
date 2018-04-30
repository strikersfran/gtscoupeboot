<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram;
use Htunlogic\Poloniex\Poloniex;
use Illuminate\Support\Facades\Log;
use Spatie\Emoji\Emoji;

class ApiController extends Controller{
    
    public function showMenu($chatid, $info = null, $request = null) {
        // this will create keyboard buttons for users to touch instead of typing commands
        Log::error('Funcion showMenu, el valor de $info es ' . $info);
        if ($info === null) {
            Log::error('Funcion showMenu, $info es null');
            $inlineLayout = [
                    [
                    Keyboard::inlineButton(['text' => 'Noticias', 'callback_data' => '/noticias']),
                    Keyboard::inlineButton(['text' => 'Exchange', 'callback_data' => '/exchange']),
                    Keyboard::inlineButton(['text' => 'DolarTaday', 'callback_data' => '/dolartoday'])
                ], [
                    Keyboard::inlineButton(['text' => 'Sito Web', 'callback_data' => '/website']),
                    Keyboard::inlineButton(['text' => 'Contacto', 'callback_data' => '/contacto'])
                ]
            ];
            // create an instance of the replyKeyboardMarkup method
            $keyboard = Telegram::replyKeyboardMarkup([
                        'inline_keyboard' => $inlineLayout
            ]);
            // Now send the message with they keyboard using 'reply_markup' parameter
            $response = Telegram::sendMessage([
                        'chat_id' => $chatid,
                        'text' => "Bienvenido a <b>GTSCoupe</b> bot\n<b>SELECIONE UNA OPCION</b>",
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard
            ]);
        } else
        if ($info === 'default' && $request['message']['chat']['id'] == '-288334894') {//para identificar que se trata del grupo Dragon Coin
            //para dar la bienvenida a los miembros del grupo
            Log::error('Funcion showMenu, $info es vacio y grupo id es ' . $request['message']['chat']['id']);
            if (array_key_exists('new_chat_participant', $request['message'])) {
                if ($request['message']['new_chat_participant']['is_bot'] == false) {
                    $response = Telegram::sendMessage([
                                'chat_id' => $chatid,
                                'text' => "<b>" . $request['message']['new_chat_participant']['first_name'] . ' ' . $request['message']['new_chat_participant']['last_name'] . "</b> Bienvenido a <b>" . $request['message']['chat']['title'] . "</b> El objetivo del grupo es poder mantenernos informado en el ámbito de las criptomonedas. Por ahora se cuenta con un pequeño bot que se esta desarrollando por <b>Francisco Carrión</b> y que poco a poco se irá mejorando, coloque /help para obtener ayuda. Se aceptan sugerencias. \nSaludos",
                                'parse_mode' => 'HTML'
                    ]);
                }
            }
        }
    }

    public function showWebsite($chatid, $cbid) {
        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }
        $message = "Puedes visitar nuestro sitio web https://gtscoupebot.herokuapp.com";

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'text' => $message
        ]);
    }

    public function showContact($chatid, $cbid) {
        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $message = "<b>GTSCoupe</b> es un bot desarrollado por Francisco Carrión\n";
        $message .= "Email de contacto es carrionfn@gmail.com\n";
        $message .= "Para mayor información visite nuestro sitio web <a href='https://gtscoupebot.herokuapp.com'>Aqui</a>";

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message
        ]);
    }

    public function showNoticias($chatid, $cbid) {
        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $message = 'En construcción';

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'text' => $message
        ]);
    }

    public function showDolarToday($chatid, $cbid) {
        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://s3.amazonaws.com/dolartoday/data.json');

        if ($res->getStatusCode() == '200') {
            $datos = json_decode(utf8_encode($res->getBody()), true);

            $message = "<b>DOLARTODAY </b> " . date('d/m/Y h:i:s A') . "\n\n";
            $message .= Emoji::banknoteWithDollarSign() . " <b>Dolares " . Emoji::banknoteWithDollarSign() . ": </b>" . $datos['USD']['transferencia'] . " Bs.\n";
            $message .= Emoji::banknoteWithEuroSign() . " <b>Euros " . Emoji::banknoteWithEuroSign() . ": </b>" . $datos['EUR']['transferencia'] . " Bs.\n";
        } else {
            $message = 'Disculpe estamos presentando problema con la plataforma <b>DolarTaday</b>';
        }



        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'text' => $message,
                    'parse_mode' => 'HTML',
        ]);
    }

    public function testDolartoday() {
        $client = new \GuzzleHttp\Client();

        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://s3.amazonaws.com/dolartoday/data.json');
        $promise = $client->sendAsync($request)->then(function ($response) {
            $datos = json_decode(utf8_encode($response->getBody()), true);
            //print_r($datos);
            echo $datos['USD']['transferencia'];
        });
        $promise->wait();

        /* $data = file_get_contents("https://s3.amazonaws.com/dolartoday/data.json");

          //echo $data;
          //$products = json_decode("'".$data."'");

          // Definir los errores.
          $constantes = get_defined_constants(true);
          $errores_json = array();
          foreach ($constantes["json"] as $nombre => $valor) {
          if (!strncmp($nombre, "JSON_ERROR_", 11)) {
          $errores_json[$valor] = $nombre;
          }
          }

          // Mostrar los errores para diferentes profundidades.
          foreach (range(4, 3, -1) as $profundidad) {
          var_dump(json_decode(utf8_encode($data), true, $profundidad));
          echo 'Último error: ', $errores_json[json_last_error()], PHP_EOL, PHP_EOL;
          }



          //print_r($products);

          //$res = $client->request('GET', 'https://s3.amazonaws.com/dolartoday/data.json');
          //echo $res->getStatusCode();
          //echo $res->getBody();
          //echo $res->getHeaderLine('content-type');
          // 'application/json; charset=utf8'
          //$datos = json_decode($res->getBody(),true);
          //print_r($datos);
          //echo $datos['USD']['transferencia'].'--'.Emoji::banknoteWithEuroSign(); */
    }

    public function showSubMenu($chatid, $cbid, $text) {
        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }


        switch ($text) {
            case '/exchange':
                $text = "SELECIONE UN EXCHANGE";
                $inlineLayout = [
                        [
                        Keyboard::inlineButton(['text' => 'Poloniex', 'callback_data' => '/poloniex']),
                        Keyboard::inlineButton(['text' => 'Poloniex Resumen', 'callback_data' => '/poloniexr']),
                        Keyboard::inlineButton(['text' => 'Uphold', 'callback_data' => '/uphold']),
                        Keyboard::inlineButton(['text' => 'Bit-z', 'callback_data' => '/bitz'])
                    ]
                ];
                break;
            case '/poloniex':
                $text = "SELECIONE UNA CRIPTOMONEDA";
                $inlineLayout = [
                        [
                        Keyboard::inlineButton(['text' => 'BTC', 'callback_data' => 'polobtc']),
                        Keyboard::inlineButton(['text' => 'XMR', 'callback_data' => 'poloxmr']),
                        Keyboard::inlineButton(['text' => 'XRP', 'callback_data' => 'poloxrp'])
                    ], [
                        Keyboard::inlineButton(['text' => 'BCH', 'callback_data' => 'polobch']),
                        Keyboard::inlineButton(['text' => 'ETH', 'callback_data' => 'poloeth']),
                        Keyboard::inlineButton(['text' => 'LTC', 'callback_data' => 'pololtc'])
                    ], [
                        Keyboard::inlineButton(['text' => 'DASH', 'callback_data' => 'polodash']),
                        Keyboard::inlineButton(['text' => 'NXT', 'callback_data' => 'polonxt']),
                        Keyboard::inlineButton(['text' => 'STR', 'callback_data' => 'polostr'])
                    ]
                ];
                break;
            case '/poloniexr':
                $text = "SELECIONE UN MERCADO";
                $inlineLayout = [
                        [
                        Keyboard::inlineButton(['text' => 'BTC', 'callback_data' => 'polobtcr']),
                        Keyboard::inlineButton(['text' => 'USD', 'callback_data' => 'polousdr'])
                    ]
                ];
                break;
            case '/uphold':
                $text = "SELECIONE UNA CRIPTOMONEDA";
                $inlineLayout = [
                        [
                        Keyboard::inlineButton(['text' => 'BTC', 'callback_data' => 'upbtc']),
                        Keyboard::inlineButton(['text' => 'BCH', 'callback_data' => 'upbch']),
                        Keyboard::inlineButton(['text' => 'LTC', 'callback_data' => 'upltc'])
                    ], [
                        Keyboard::inlineButton(['text' => 'BTG', 'callback_data' => 'upbtg']),
                        Keyboard::inlineButton(['text' => 'ETH', 'callback_data' => 'upeth']),
                        Keyboard::inlineButton(['text' => 'EUR', 'callback_data' => 'upeur'])
                    ]
                ];
                break;
            case '/bitz':
                $text = "SELECIONE UNA CRIPTOMONEDA";
                $inlineLayout = [
                        [
                        Keyboard::inlineButton(['text' => 'BTC', 'callback_data' => 'bitzbtc']),
                        Keyboard::inlineButton(['text' => 'ETH', 'callback_data' => 'bitzeth'])
                    ]
                ];
                break;
            default:
                $text = "SELECIONE UNA OPCION";
                $inlineLayout = [
                        [
                        Keyboard::inlineButton(['text' => 'Cancelar', 'callback_data' => 'cancelar'])
                    ]
                ];
        }


        // create an instance of the replyKeyboardMarkup method
        $keyboard = Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => $inlineLayout
        ]);
        // Now send the message with they keyboard using 'reply_markup' parameter
        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'text' => $text,
                    'reply_markup' => $keyboard
        ]);
    }

    public function showPoloniex($chatid, $cbid, $text) {

        //Log::error('Funcion showPoloniex, Datos de Entrada ' .$chatid.'--'.$cbid.'--'.$text);
        //array identificativos
        $criptoid = array(
            'polobtc' => 'USDT_BTC', 'poloxmr' => 'USDT_XMR', 'poloxrp' => 'USDT_XRP',
            'polobch' => 'USDT_BCH', 'poloeth' => 'USDT_ETH', 'pololtc' => 'USDT_LTC',
            'polodash' => 'USDT_DASH', 'polonxt' => 'USDT_NXT', 'polostr' => 'USDT_STR'
        );

        //Log::error('Funcion showPoloniex, Valor del criptoid ' .$criptoid[$text]);

        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $ticker = Poloniex::getTicker($criptoid[$text]);
        //$balance = Poloniex::getBalanceFor('BTC');
        //Log::error('Funcion showPoloniex, Resultado de api poloniex ' .json_encode($balance));
        if (($ticker['percentChange'] * 100) > 0)
            $emoji = Emoji::moneyMouthFace();
        else
            $emoji = Emoji::loudlyCryingFace();

        $message = "<b>POLONIEX </b>" . date('d/m/Y h:i:s A') . "\n\n";
        $message .= '<b>CRIPTOMONEDA ' . trim($criptoid[$text], 'USDT_') . ":</b>\n";
        $message .= "<b>Precio Actual:</b> " . $ticker['last'] . " $\n";
        $message .= "<b>Porcentaje:</b> " . round($ticker['percentChange'] * 100, 2) . " %  " . $emoji . "\n";
        $message .= "<b>Precio mas Alto 24Hr:</b> " . $ticker['high24hr'] . "\n";
        $message .= "<b>Precio mas Bajo 24Hr:</b> " . $ticker['low24hr'] . "\n";
        $message .= "<b>Volumen:</b> " . $ticker['baseVolume'] . " USDT";

        //control de regrear al menu anterior
        /* $inlineLayout = [
          [
          Keyboard::inlineButton(['text' => 'Menu Principal', 'callback_data' => '/menu'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Exchange', 'callback_data' => '/exchange'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Anteriror', 'callback_data' => '/poloniex'])
          ]
          ];

          // create an instance of the replyKeyboardMarkup method
          $keyboard = Telegram::replyKeyboardMarkup([
          'inline_keyboard' => $inlineLayout
          ]); */

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message/* , 
                          'reply_markup' => $keyboard */
        ]);

        /* $response = Telegram::sendMessage([
          'chat_id' => $chatid,
          'parse_mode' => 'HTML',
          'text' => '<b>IR A:</b>',
          'reply_markup' => $keyboard
          ]); */
    }
    public function showPoloniexResumen($chatid, $cbid, $text) {

        //Log::error('Funcion showPoloniex, Datos de Entrada ' .$chatid.'--'.$cbid.'--'.$text);
        //array identificativos
        $criptoid = array(
            'polobtcr' => 'USDT_BTC', 'polousdr' => 'USDT_XMR'
        );

        //Log::error('Funcion showPoloniex, Valor del criptoid ' .$criptoid[$text]);

        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $ticker = Poloniex::getTickers();
        //$balance = Poloniex::getBalanceFor('BTC');
        Log::error('Funcion showPoloniex, Resultado de api poloniex ' .json_encode($ticker));
        if (($ticker['percentChange'] * 100) > 0)
            $emoji = Emoji::moneyMouthFace();
        else
            $emoji = Emoji::loudlyCryingFace();

        $message = "<b>POLONIEX </b>" . date('d/m/Y h:i:s A') . "\n\n";
        $message .= '<b>CRIPTOMONEDA ' . trim($criptoid[$text], 'USDT_') . ":</b>\n";
        $message .= "<b>Precio Actual:</b> " . $ticker['last'] . " $\n";
        $message .= "<b>Porcentaje:</b> " . round($ticker['percentChange'] * 100, 2) . " %  " . $emoji . "\n";
        $message .= "<b>Precio mas Alto 24Hr:</b> " . $ticker['high24hr'] . "\n";
        $message .= "<b>Precio mas Bajo 24Hr:</b> " . $ticker['low24hr'] . "\n";
        $message .= "<b>Volumen:</b> " . $ticker['baseVolume'] . " USDT";

        //control de regrear al menu anterior
        /* $inlineLayout = [
          [
          Keyboard::inlineButton(['text' => 'Menu Principal', 'callback_data' => '/menu'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Exchange', 'callback_data' => '/exchange'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Anteriror', 'callback_data' => '/poloniex'])
          ]
          ];

          // create an instance of the replyKeyboardMarkup method
          $keyboard = Telegram::replyKeyboardMarkup([
          'inline_keyboard' => $inlineLayout
          ]); */

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message/* , 
                          'reply_markup' => $keyboard */
        ]);

        /* $response = Telegram::sendMessage([
          'chat_id' => $chatid,
          'parse_mode' => 'HTML',
          'text' => '<b>IR A:</b>',
          'reply_markup' => $keyboard
          ]); */
    }

    public function poloniexTest() {

        $response = Poloniex::getTicker('USDT_BTC');
        if (($response['percentChange'] * 100) > 0)
            echo round($response['percentChange'] * 100, 2) . Emoji::moneyMouthFace();
        else
            echo round($response['percentChange'] * 100, 2) . Emoji::loudlyCryingFace();

        return $response;
    }

    public function showUphold($chatid, $cbid, $text) {

        //Log::error('Funcion showPoloniex, Datos de Entrada ' .$chatid.'--'.$cbid.'--'.$text);
        //array identificativos
        $criptoid = array(
            'upbtc' => 'BTCUSD', 'upeur' => 'EURUSD', 'upeth' => 'ETHUSD',
            'upbch' => 'BCHUSD', 'upbtg' => 'BTGUSD', 'upltc' => 'LTCUSD'
        );

        //Log::error('Funcion showPoloniex, Valor del criptoid ' .$criptoid[$text]);

        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://api.uphold.com/v0/ticker/' . $criptoid[$text]);

        if ($res->getStatusCode() == '200') {
            $datos = json_decode($res->getBody(), true);

            $message = "<b>UPHOLD </b> " . date('d/m/Y h:i:s A') . "\n\n";
            $message .= '<b>CRIPTOMONEDA ' . trim($criptoid[$text], 'USD') . ":</b>\n";
            $message .= "<b>Precio Actual:</b> " . $datos['ask'] . " $\n";
        } else {
            $message = 'Disculpe estamos presentando problema con la plataforma <b>UPHOLD</b>';
        }

        //control de regrear al menu anterior
        /* $inlineLayout = [
          [
          Keyboard::inlineButton(['text' => 'Menu Principal', 'callback_data' => '/menu'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Exchange', 'callback_data' => '/exchange'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Anteriror', 'callback_data' => '/uphold'])
          ]
          ];

          // create an instance of the replyKeyboardMarkup method
          $keyboard = Telegram::replyKeyboardMarkup([
          'inline_keyboard' => $inlineLayout
          ]); */

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message/* , 
                          'reply_markup' => $keyboard */
        ]);
    }

    public function upholdTest() {

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://api.uphold.com/v0/ticker/BTCUSD');
        //echo $res->getStatusCode();
        //echo $res->getHeaderLine('content-type');
        // 'application/json; charset=utf8'
        $datos = json_decode($res->getBody(), true);

        echo $datos['ask'] . '--' . Emoji::banknoteWithEuroSign() . '--' . $datos['bid'];

        return $datos;
    }

    public function showBitz($chatid, $cbid, $text) {

        //Log::error('Funcion showPoloniex, Datos de Entrada ' .$chatid.'--'.$cbid.'--'.$text);
        //array identificativos
        $criptoid = array(
            'bitzbtc' => 'btc_usdt', 'bitzeth' => 'eth_usdt'
        );

        //Log::error('Funcion showPoloniex, Valor del criptoid ' .$criptoid[$text]);

        if ($cbid != 0) {
            $responses = Telegram::answerCallbackQuery([
                        'callback_query_id' => $cbid,
                        'text' => '',
                        'show_alert' => false
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://www.bit-z.com/api_v1/ticker?coin=' . $criptoid[$text]);

        if ($res->getStatusCode() == '200') {
            $datos = json_decode($res->getBody(), true);

            $message = "<b>BIT-Z </b> " . date('d/m/Y h:i:s A') . "\n\n";
            $message .= '<b>CRIPTOMONEDA ' . strtoupper(trim($criptoid[$text], '_usdt')) . ":</b>\n";
            $message .= "<b>Precio Actual:</b> " . $datos['data']['last'] . " $\n";
        } else {
            $message = 'Disculpe estamos presentando problema con la plataforma <b>BIT-Z</b>';
        }

        //control de regrear al menu anterior
        /* $inlineLayout = [
          [
          Keyboard::inlineButton(['text' => 'Menu Principal', 'callback_data' => '/menu'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Exchange', 'callback_data' => '/exchange'])
          ],[
          Keyboard::inlineButton(['text' => 'Menu Anteriror', 'callback_data' => '/bitz'])
          ]
          ];

          // create an instance of the replyKeyboardMarkup method
          $keyboard = Telegram::replyKeyboardMarkup([
          'inline_keyboard' => $inlineLayout
          ]); */

        $response = Telegram::sendMessage([
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message/* , 
                          'reply_markup' => $keyboard */
        ]);
    }

    public function bitzTest() {

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://www.bit-z.com/api_v1/ticker?coin=btc_usdt');
        //echo $res->getStatusCode();
        //echo $res->getHeaderLine('content-type');
        // 'application/json; charset=utf8'
        $datos = json_decode($res->getBody(), true);

        echo $datos['data']['last'] . '--' . Emoji::banknoteWithEuroSign() . '--' . $datos['data']['sell'];

        return $datos;
    }

    public function setWebHook() {
        $telegram = new Api(env('TELEGRAM-BOT-TOKEN'));

        $response = $telegram->setWebhook(['url' => 'https://gtscoupebot.herokuapp.com/my-bot-token/webhook']);

        return $response;
    }

    public function webhook(Request $request) {

        Log::error('Funcion webhook, Request ' . json_encode($request['message']));

        $text = 'default';

        if (isset($request['callback_query'])) {
            $text = str_replace('@gtscoupe_bot', '', $request['callback_query']['data']);
            $chatid = $request['callback_query']['message']['chat']['id'];
            $callback_query_id = $request['callback_query']['id'];
        } else {
            //para evitar que el index text venga vacio
            if (array_key_exists('text', $request['message']))
                $text = str_replace('@gtscoupe_bot', '', $request['message']['text']);

            $chatid = $request['message']['chat']['id'];
            $callback_query_id = 0;
        }

        Log::error('Funcion webhook, Valor de entrada para text ' . $text);

        switch ($text) {
            case (($text == '/start') || ($text == '/menu') || ($text == '/help')):
                $this->showMenu($chatid);
                break;
            case '/website':
                $this->showWebsite($chatid, $callback_query_id);
                break;
            case '/contacto':
                $this->showContact($chatid, $callback_query_id);
                break;
            case '/noticias':
                $this->showNoticias($chatid, $callback_query_id);
                break;
            case (($text == '/exchange') || ($text == '/poloniex') || ($text == '/uphold') || ($text == '/bitz')):
                $this->showSubMenu($chatid, $callback_query_id, $text);
                break;
            case (($text == 'polobtc') || ($text == 'poloxmr') || ($text == 'poloxrp') ||
            ($text == 'polostr') || ($text == 'polodash') || ($text == 'pololtc') ||
            ($text == 'polonxt') || ($text == 'poloeth') || ($text == 'polobch')):
                $this->showPoloniex($chatid, $callback_query_id, $text);
                break;
            case (($text == 'polobtcr') || ($text == 'polousdr')):
                $this->showPoloniexResumen($chatid, $callback_query_id, $text);
                break;
            case (($text == 'upbtc') || ($text == 'upltc') || ($text == 'upeur') ||
            ($text == 'upbtg') || ($text == 'upeth') || ($text == 'upbch')):
                $this->showUphold($chatid, $callback_query_id, $text);
                break;
            case (($text == 'bitzbtc') || ($text == 'bitzeth')):
                $this->showBitz($chatid, $callback_query_id, $text);
                break;
            case '/dolartoday':
                $this->showDolarToday($chatid, $callback_query_id);
                break;
            case 'none':
                $this->showNoticias($chatid, $callback_query_id);
                break;
            default:
                $this->showMenu($chatid, $text, $request);
        }
    }
}
