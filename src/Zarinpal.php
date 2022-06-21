<?php

namespace Zarinpal;

use Zarinpal\Drivers\DriverInterface;
use Zarinpal\Drivers\RestDriver;

class Zarinpal
{
    private $redirectUrl = 'https://www.zarinpal.com/pg/StartPay/%s';
    private $merchantId;
    private $driver;
    private $authority;

    public function __construct($merchantId, DriverInterface $driver = null)
    {
        if (is_null($driver)) {
            $driver = new RestDriver();
        }
        $this->merchantId = $merchantId;
        $this->driver = $driver;
    }

    /**
     * send request for money to zarinpal
     * and redirect if there was no error.
     *
     * @param string $callbackUrl
     * @param int $amount
     * @param string $description
     * @param string|null $email
     * @param string|null $mobile
     * @param array|null   $metadata
     *
     * @return array|@redirect
     */
    public function request($callbackUrl, $amount, $description, $email = null, $mobile = null, $metadata = null)
    {
        $inputs = [
            'merchant_id'  => $this->merchantId,
            'callback_url' => $callbackUrl,
            'amount'      => (int)$amount,
            'description' => $description,
        ];
        if (!is_null($email)) {
            $inputs['email'] = $email;
        }
        if (!is_null($mobile)) {
            $inputs['mobile'] = $mobile;
        }
        if (!is_null($metadata)) {
            $inputs['metadata'] = $metadata;
        }

        $results = $this->driver->request($inputs);

        if (empty($results['authority'])) {
            $results['authority'] = null;
        }
        $this->authority = $results['authority'];

        return $results;
    }

    /**
     * verify that the bill is paid or not
     * by checking authority, amount and status.
     *
     * @param $amount
     * @param $authority
     *
     * @return array
     */
    public function verify($amount, $authority)
    {
        // backward compatibility
        if (count(func_get_args()) == 3) {
            $amount = func_get_arg(1);
            $authority = func_get_arg(2);
        }

        $inputs = [
            'merchant_id' => $this->merchantId,
            'authority'  => $authority,
            'amount'     => (int)$amount,
        ];

        return $this->driver->verify($inputs);
    }

    public function redirect()
    {
        header('Location: '.sprintf($this->redirectUrl, $this->authority));
        die;
    }

    /**
     * @return string
     */
    public function redirectUrl()
    {
        return sprintf($this->redirectUrl, $this->authority);
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * active sandbox mod for test env.
     */
    public function enableSandbox()
    {
        $this->redirectUrl = 'https://sandbox.zarinpal.com/pg/StartPay/%s';
        $this->getDriver()->enableSandbox();
    }

    /**
     * active zarinGate mode.
     */
    public function isZarinGate()
    {
        $this->redirectUrl = $this->redirectUrl.'/ZarinGate';
    }
}
