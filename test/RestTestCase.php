<?php

use GuzzleHttp\Client;
use Zarinpal\Zarinpal;

class RestTestCase extends \PHPUnit\Framework\TestCase
{
    private $zarinpal;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->zarinpal = new Zarinpal('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $this->zarinpal->enableSandbox();

        parent::__construct($name, $data, $dataName);
    }

    public function testCorrectRequest()
    {
        $answer = $this->zarinpal->request('http://www.example.com/testVerify.php', 1000, 'testing');
        $this->assertEquals(strlen($answer['authority']), 36);

        // try and mock pay form
        try {
            $client = new Client();
            $response = $client->request(
                'POST',
                'https://sandbox.zarinpal.com/pg/transaction/pay/'.$answer['authority'],
                [
                    'form_params' => [
                        'ok' => 'ok',
                    ],
                ]);
        } catch (Exception $e) {
        }

        $answer = $this->zarinpal->verify(1000, $answer['authority']);
        $this->assertEquals($answer['Status'], 'success');
        $this->assertEquals(strlen($answer['Status']), 7);
    }
}
