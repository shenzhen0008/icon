<?php

namespace App\Filament\Resources\RechargeReceivers;

use App\Filament\Resources\RechargeReceivers\Pages\CreateRechargeReceiver;
use App\Filament\Resources\RechargeReceivers\Pages\EditRechargeReceiver;
use App\Filament\Resources\RechargeReceivers\Pages\ListRechargeReceivers;
use App\Filament\Resources\RechargeReceivers\Schemas\RechargeReceiverForm;
use App\Filament\Resources\RechargeReceivers\Tables\RechargeReceiversTable;
use App\Modules\Balance\Models\RechargeReceiver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RechargeReceiverResource extends Resource
{
    protected static ?string $model = RechargeReceiver::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $navigationLabel = '收款账户';

    protected static ?string $modelLabel = '收款账户';

    protected static ?string $pluralModelLabel = '收款账户';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return RechargeReceiverForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RechargeReceiversTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRechargeReceivers::route('/'),
            'create' => CreateRechargeReceiver::route('/create'),
            'edit' => EditRechargeReceiver::route('/{record}/edit'),
        ];
    }
}
