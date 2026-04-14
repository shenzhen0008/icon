<?php

namespace App\Filament\Resources\ProductReservations\Pages;

use App\Filament\Resources\ProductReservations\ProductReservationResource;
use Filament\Resources\Pages\ListRecords;

class ListProductReservations extends ListRecords
{
    protected static string $resource = ProductReservationResource::class;
}
