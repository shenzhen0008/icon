<?php

namespace App\Filament\Resources\OnchainRechargeRequests;

use App\Filament\Resources\OnchainRechargeRequests\Pages\ListOnchainRechargeRequests;
use App\Filament\Resources\OnchainRechargeRequests\Tables\OnchainRechargeRequestsTable;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\OnchainRecharge\Support\OnchainRechargeStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OnchainRechargeRequestResource extends Resource
{
    protected static ?string $model = RechargePaymentRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = '链上充值申请';

    protected static ?string $modelLabel = '链上充值申请';

    protected static ?string $pluralModelLabel = '链上充值申请';

    protected static ?int $navigationSort = 13;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('channel', OnchainRechargeStatus::CHANNEL_ONCHAIN_WALLET);
    }

    public static function table(Table $table): Table
    {
        return OnchainRechargeRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOnchainRechargeRequests::route('/'),
        ];
    }
}
