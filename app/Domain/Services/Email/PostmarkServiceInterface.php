<?php

namespace App\Domain\Services\Email;

interface PostmarkServiceInterface
{
    public function sendEmailWithTemplate($to, $templateId, $templateData, $pdfPath);
}

