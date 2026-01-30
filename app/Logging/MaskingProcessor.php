<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class MaskingProcessor implements ProcessorInterface
{
    /**
     * Process the log record and mask sensitive information.
     *
     * @param mixed $record
     * @return mixed
     */
    public function __invoke($record)
    {
        if ($record instanceof LogRecord) {
            // Mask sensitive information in the message
            $maskedMessage = $this->maskSensitiveInfo($record->message);

            // Return a new LogRecord with the masked message
            return $record->with(['message' => $maskedMessage]);
        } elseif (is_array($record)) {
            // Handle array format (Laravel's format)
            $record['message'] = $this->maskSensitiveInfo($record['message']);
            return $record;
        }

        // Return the record as is if it's not a LogRecord or array
        return $record;
    }

    /**
     * Mask sensitive information in the message.
     *
     * @param string $message
     * @return string
     */
    private function maskSensitiveInfo(string $message): string
    {
        // Define patterns to mask
        $patterns = [
            '/("access_token":")(.*?)(")/i',
            '/(password=)([^&\s]+)/i',
            '/(token=)([^&\s]+)/i',
            '/([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6})/i',  // Mask emails
            '/(\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4})/i', // Mask credit card numbers
            '/(\d+\.\d+\.\d+\.\d+)/i', // Mask IP addresses
        ];

        // Replace sensitive info with asterisks
        foreach ($patterns as $pattern) {
            $message = preg_replace($pattern, '$1***$3', $message);
        }

        return $message;
    }
}
