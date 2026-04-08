<?php

namespace App\Filament\Resources\RechargePaymentRequests;

use App\Filament\Resources\RechargePaymentRequests\Pages\ListRechargePaymentRequests;
use App\Filament\Resources\RechargePaymentRequests\Tables\RechargePaymentRequestsTable;
use App\Modules\Balance\Models\RechargePaymentRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RechargePaymentRequestResource extends Resource
{
    protected static ?string $model = RechargePaymentRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = '充值申请';

    protected static ?string $modelLabel = '充值申请';

    protected static ?string $pluralModelLabel = '充值申请';

    protected static ?int $navigationSort = 12;

    public static function table(Table $table): Table
    {
        return RechargePaymentRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRechargePaymentRequests::route('/'),
        ];
    }
}
