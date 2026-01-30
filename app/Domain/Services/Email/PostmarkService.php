<?php

namespace App\Domain\Services\Email;

use Exception;
use Postmark\PostmarkClient;
use Illuminate\Support\Facades\Log;


class PostmarkService implements PostmarkServiceInterface
{
    protected $client;

    public function __construct($token=null)
    {
        if ($token){
            $this->client = new PostmarkClient($token);
        }else{
            $this->client = new PostmarkClient('79fed87d-c063-43e2-bd14-d259be21b78c');
        }
    }
    public function sendEmailWithTemplate($to, $templateId, $templateData, $attachments = [])
    {

        try {
            // Prepare the email
            $response = $this->client->sendEmailWithTemplate(
                config('sparkfore.postmark_email.from'),
                $to,
                $templateId,
                $templateData,
                attachments: $attachments
            );
            Log::info('Email sent with Postmark: ', [$templateId, $templateData, $response]);
            return $response;
        } catch (Exception $e) {
            Log::error('Error sending email with Postmark: ', [$e->getMessage()]);
        }
    }
}

