<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\Response;

class EmptyResponse extends Response
{
    public function send(bool $flush = true): static
    {
        // Skip sending headers and content
        // $this->sendHeaders();
        // $this->sendContent();

        // Keep some standard Symfony magic
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (\function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffers(0, true);
        }

        return $this;
    }
}
