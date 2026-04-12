<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Concerns;

use Illuminate\Support\HtmlString;

trait HasBoldLabel
{
    protected static function bold(string $text): HtmlString
    {
        return new HtmlString("<span style=\"font-weight:750;\">{$text}</span>");
    }
}
