<?php
/**
 * Created by PhpStorm.
 * User: bahodir
 * Date: 17.11.2018
 * Time: 22:55
 */

defined("PAYCOM_ID") or define("PAYCOM_ID", "");
defined("PAYCOM_KEY") or define("PAYCOM_KEY", "");

defined("CLICK_MERCHANT_ID") or define("CLICK_MERCHANT_ID", "");
defined("CLICK_SERVICE_ID") or define("CLICK_SERVICE_ID", "");
defined("CLICK_SECRET_KEY") or define("CLICK_SECRET_KEY", "");
defined("CLICK_MERCHANT_USER_ID") or define("CLICK_MERCHANT_USER_ID", "");

/**
 * Class ClickPayme
 *
 * for protected/config/main.php
 *
 * 'components' => array(
 * 'ClickPayme' => array(
 * 'class' => 'application.components.ClickPayme',
 * 'paycomId' => '', //paycom merchant id
 * 'paycomKey' => '', //paycom token
 * 'clickMerchantId' => '', //click merchant id
 * 'clickServiceId' => '', //click service id,
 * 'clickSecretKey' => '', //click secret key
 * 'clickMerchantUserId' => '', //click merchant user id
 * ),
 * ....
 * )
 */
class ClickPayme extends CApplicationComponent
{
    public $paycomId = PAYCOM_ID;
    public $paycomKey = PAYCOM_KEY;

    public $clickMerchantId = CLICK_MERCHANT_ID;
    public $clickServiceId = CLICK_SERVICE_ID;
    public $clickSecretKey = CLICK_SECRET_KEY;
    public $clickMerchantUserId = CLICK_MERCHANT_USER_ID;

    public function init()
    {

    }

    /**
     * @param $token
     * @param $amount
     * @return mixed
     */
    public function ClickCardPayment($token, $amount)
    {
        $body = [
            'merchant_trans_id' => (string)CLICK_MERCHANT_ID,
            'service_id' => CLICK_SERVICE_ID,
            'card_token' => $token,
            'amount' => number_format((int)$amount, 2, '.', '')
        ];

        $headers = [];
        $headers[] = 'Accept: application/json';
        $headers[] = 'Auth: '.CLICK_MERCHANT_USER_ID.':' . sha1(time() . CLICK_SECRET_KEY) . ':' . time();
        $headers[] = 'Content-type: application/json';

        return json_decode($this->curlPost("https://api.click.uz/v1/merchant/card_token/payment", json_encode($body), $headers));
    }

    /**
     * @param $token
     * @param $code
     * @return mixed
     */
    public function ClickCardVerify($token, $code)
    {
        $body = [
            'service_id' => CLICK_SERVICE_ID,
            'card_token' => $token,
            'sms_code' => (int)$code
        ];
        $headers = [];

        $headers[] = 'Auth: '.CLICK_MERCHANT_USER_ID.':' . sha1(time() . CLICK_SECRET_KEY) . ':' . time();
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Accept: application/json';

        return json_decode($this->curlPost("https://api.click.uz/v1/merchant/card_token/verify", json_encode($body), $headers));
    }

    /**
     * @param $number
     * @param $expire
     * @param int $temp
     * @return mixed
     */
    public function ClickCardCreate($number, $expire, $temp = 0)
    {
        $body = [
            'service_id' => CLICK_SERVICE_ID,
            'card_number' => (string)$number,
            'expire_date' => (string)$expire,
            'temporary' => (int)!$temp
        ];
        $headers = [];
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Accept: application/json';

        return json_decode($this->curlPost("https://api.click.uz/v1/merchant/card_token/request", json_encode($body), $headers));
    }

    /**
     * $id string paycomReceiptsCreate dan qaytgan id
     * $token string card token
     * @param $id
     * @param $token
     * @return mixed
     **/

    public function PaycomReceiptsPay($id, $token)
    {
        $params = ['id' => $id, 'token' => $token];
        return $this->paycomRequest("receipts.pay", $params);
    }

    /**
     * @param $amount
     * @param $order_id
     * @return mixed
     */
    public function PaycomReceiptsCreate($amount, $order_id)
    {
        $params = ['amount' => (int)$amount, 'account' => ['order_id' => $order_id]];
        return $this->paycomRequest("receipts.create", $params);
    }

    /**
     * @param $token
     * @param $code
     * @return mixed
     */
    public function PaycomCardsVerify($token, $code)
    {
        $params = ['token' => $token, 'code' => (string)$code];
        return $this->paycomRequest("cards.verify", $params);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function PaycomCardsGetVerifyCode($token)
    {
        $params = ['token' => $token];
        return $this->paycomRequest("cards.get_verify_code", $params);
    }

    /**
     * @param $number
     * @param $expire
     * @param $amount
     * @param int $save
     * @return mixed
     */
    public function PaycomCardsCreate($number, $expire, $amount, $save = 0)
    {
        $params = [
            'card' => [
                'number' => $number,
                'expire' => (string)$expire
            ],
            'amount' => (int)$amount,
            'save' => (boolean)$save,
        ];

        return $this->paycomRequest("cards.create", $params);
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function paycomRequest($method, $params)
    {
        $paycom_url = "https://checkout.test.paycom.uz/api";
        $merchant_id = PAYCOM_ID;
        $merchant_key = PAYCOM_KEY;
        switch ($method) {
            case 'cards.create':
            case 'cards.get_verify_code':
            case 'cards.verify':
                $merchant_key = "";
                break;

        }

        $body = [
            'id' => 123,
            'method' => $method,
            'params' => $params
        ];
        // var_dump(json_encode($body));
        // exit;
        $headers = [];
        $headers[] = "X-Auth: " . $merchant_id . ":" . $merchant_key;
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-type: application/json';

        return json_decode($this->curlPost($paycom_url, json_encode($body), $headers));
    }

    /**
     * @param $url
     * @param $body
     * @param $headers
     * @return mixed
     */
    protected function curlPost($url, $body, $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $server_output = curl_exec($ch);

        curl_close($ch);
        return $server_output;
    }
}