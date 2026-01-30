<?php

namespace App\Domain\Shared\Actions;

class FileGetContentsAction
{
    public function __invoke(string $url): string
    {
        return file_get_contents($url);
    }
}
