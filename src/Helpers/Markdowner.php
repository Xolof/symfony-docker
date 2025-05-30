<?php

declare(strict_types=1);

namespace App\Helpers;

use Parsedown;

class Markdowner
{
    protected Parsedown $parsedown;

    /**
     * Convert markdown to HTML
     */
    public function print(string $text): string
    {
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(true);
        $this->parsedown->setUrlsLinked(true);
        return $this->parsedown->text($text);
    }
}
