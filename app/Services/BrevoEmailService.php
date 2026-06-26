<?php

namespace App\Services;

use App\Mail\ResetPassword;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Mail\Mailable;

class BrevoEmailService
{
    protected Client $client;
    protected string $apiKey;
    protected string $senderEmail;
    protected string $senderName;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => env('APP_ENV') !== 'local'
        ]);
        $this->apiKey = env('BREVO_API_KEY');
        $this->senderEmail = env('BREVO_SENDER_EMAIL');
        $this->senderName = env('APP_NAME', '');
    }

    /**
     * @throws GuzzleException
     */
    public function send(Mailable $mailable): bool
    {
        $response = $this->client->post('https://api.brevo.com/v3/smtp/email', [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'sender' => [
                    'email' => $this->senderEmail,
                    'name' => $this->senderName,
                ],
                'to' => [
                    [
                        'email' => $mailable->email,
                        'name' => $mailable->name,
                    ]
                ],
                'templateId' => $mailable->templateId,
                'params' => $mailable->params(),
            ],
        ]);

        return $response->getStatusCode() === 201;
    }
}
