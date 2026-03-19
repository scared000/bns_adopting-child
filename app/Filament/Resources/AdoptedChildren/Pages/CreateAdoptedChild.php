<?php

namespace App\Filament\Resources\AdoptedChildren\Pages;

use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdoptedChild extends CreateRecord
{
    protected static string $resource = AdoptedChildResource::class;
}
