<?php

namespace Zarinpal\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class RestDriver implements DriverInterface
{
    protected $baseUrl = 'https://api.zarinpal.com/pg/v4/payment/';

    /**
     * request driver.
     *
     * @param $inputs
     *
     * @return array
     */
    public function request($inputs)
    {
        $result = $this->restCall('request.json', $inputs);

        if ($result['data']['code'] == 100) {
            return ['authority' => $result['data']['authority']];
        } else {
            return ['error' => $result['data']['code']];
        }
    }

    /**
     * verify driver.
     *
     * @param $inputs
     *
     * @return array
     */
    public function verify($inputs)
    {
        $result = $this->restCall('verify.json', $inputs);

        if ($result['data']['code'] == 100) {
            return [
                'status' => 'success',
                'ref_id' => $result['data']['ref_id'],
            ];
        } elseif ($result['data']['code'] == 101) {
            return [
                'status' => 'verified_before',
                'ref_id' => $result['data']['ref_id'],
            ];
        } else {
            return [
                'status' => 'error',
                'error' => !empty($result['data']['code']) ? $result['data']['code'] : null,
                'error_info' => !empty($result['errors']) ? $result['errors'] : null,
            ];
        }
    }

    /**
     * unverifiedTransactions driver.
     *
     * @param $inputs
     *
     * @return array
     */
    public function unverifiedTransactions($inputs)
    {
        $result = $this->restCall('unVerified.json', $inputs);

        if ($result['data']['code'] == 100) {
            return [
                'status' => 'success',
                'authorities' => $result['code']['authorities']
            ];
        } else {
            return [
                'status' => 'error',
                'error' => !empty($result['data']['code']) ? $result['data']['code'] : null,
                'error_info' => !empty($result['errors']) ? $result['errors'] : null,
            ];
        }
    }

    /**
     * refund driver.
     *
     * @param $inputs
     *
     * @return array
     */
    public function refund($inputs)
    {
        $result = $this->restCall('refund.json', $inputs);

        if ($result['data']['code'] == 100) {
            return ['status' => 'success', 'refreshed' => true];
        } else {
            return ['status' => 'error', 'error' => $result['data']['code']];
        }
    }

    /**
     * request rest and return the response.
     *
     * @param $uri
     * @param $data
     *
     * @return mixed
     */
    private function restCall($uri, $data)
    {
        try {
            $client = new Client(['base_uri' => $this->baseUrl]);
            $response = $client->request('POST', $uri, ['json' => $data]);

            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $rawBody = is_null($response) ?
                '{"data": {"code":-98,"message":"http connection error"}, "errors": []}' :
                $response->getBody()->getContents();
            $body = json_decode($rawBody, true);
        }

        if (!isset($result['data']['code'])) {
            $result['data']['code'] = -99;
        }

        return $body;
    }

    /**
     * @param mixed $baseUrl
     *
     * @return void
     */
    public function setAddress($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function enableSandbox()
    {
        $this->setAddress('https://sandbox.zarinpal.com/pg/v4/payment/');
    }
}
