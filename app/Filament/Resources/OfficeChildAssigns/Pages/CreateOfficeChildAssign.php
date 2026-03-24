<?php

namespace App\Filament\Resources\OfficeChildAssigns\Pages;

use App\Filament\Resources\OfficeChildAssigns\OfficeChildAssignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficeChildAssign extends CreateRecord
{
    protected static string $resource = OfficeChildAssignResource::class;
    protected string $view = 'filament.pages.OfficeChildAssign.create-office-child-assign';
}
