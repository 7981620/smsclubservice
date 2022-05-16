<?php

namespace Agenta\SmsClubService;

use Illuminate\Support\Facades\Http;

class SmsClubService
{
    //кол-во попыток отправки запросов
    protected int $httpRetry = 3;
    //пауза между попытками (в миллисекундах)
    protected int $httpRetryPause = 1000;

    protected string $token;
    protected string $alfaname;

    protected bool $testMode;

    /**
     * @param int $httpRetry
     * @param int $httpRetryPause
     * @param bool $testMode
     */
    public function __construct(int $httpRetry = 3, int $httpRetryPause = 1000, bool $testMode = false)
    {

        $this->testMode = $testMode;
        $this->httpRetry = $httpRetry;
        $this->httpRetryPause = $httpRetryPause;
        $this->token = config('smsclubservice.sms_password');
        $this->alfaname = config('smsclubservice.sms_alphaname');
    }

    /**
     * Возвращает список альфаимен
     *
     * @return mixed
     */
    public function getAlphaNames(): mixed
    {
        $request = Http::retry($this->httpRetry, $this->httpRetryPause)
            ->withoutVerifying()
            ->withToken($this->token)
            ->acceptJson()
            ->post('https://im.smsclub.mobi/sms/originator');
        if ($request->successful()) {
            return $this->returnResult($request->json());
        }

        return false;
    }

    /**
     * Отправка sms сообщение
     *
     * @param array $phone номера телефонов
     * @param string $message сообщение
     * @return mixed
     */
    public function sendMessage(array $phone, string $message): mixed
    {
        if (!$message | $message === '') {
            return false;
        }

        //если тест режим, всегда возвращаем ответ
        if($this->testMode) {
            return [
                '1111' => '380983332211'
            ];
        }

        $request = Http::retry($this->httpRetry, $this->httpRetryPause)
            ->withoutVerifying()
            ->asForm()
            ->withToken($this->token)
            ->acceptJson()
            ->post('https://im.smsclub.mobi/sms/send', [
                'phone' => $phone,
                'message' => $message,
                'src_addr' => $this->alfaname
            ]);
        if ($request->successful()) {
            return $this->returnResult($request->json());
        }

        return false;
    }


    /**
     * Возвращает баланс аккаунта (0.00)
     */
    public function getBalance(): float|bool
    {
        $request = Http::retry($this->httpRetry, $this->httpRetryPause)
            ->withoutVerifying()
            ->withToken($this->token)
            ->acceptJson()
            ->post('https://im.smsclub.mobi/sms/balance');
        if ($request->successful() && $result = $this->returnResult($request->json())) {
            return round($result['money'], 2, PHP_ROUND_HALF_DOWN);
        }

        return false;
    }

    /**
     * Возвращает ответ шлюза
     *
     * @param array $result
     * @return mixed
     */
    private function returnResult(array $result): mixed
    {
        if (isset($result['success_request']) && isset($result['success_request']['info'])) {
            return $result['success_request']['info'];
        }

        return false;

    }

}
