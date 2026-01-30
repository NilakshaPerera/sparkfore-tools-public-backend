<?php

namespace App\Domain\Shared\Actions;

class FilePutContentsAction
{
    public function __invoke(string $url, mixed $data)
    {
        file_put_contents($url, $data);
    }
}
