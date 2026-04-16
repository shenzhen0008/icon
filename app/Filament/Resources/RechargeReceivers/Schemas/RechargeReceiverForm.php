<?php

namespace App\Filament\Resources\RechargeReceivers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RechargeReceiverForm
{
    public static function configure(Schema $schema): Schema
    {
        $allowedAssetCodes = (array) config('recharge.allowed_receive_assets', ['USDT', 'USDC', 'BTC', 'ETH']);
        $assetOptions = array_combine($allowedAssetCodes, $allowedAssetCodes);

        return $schema
            ->components([
                Section::make('收款账户信息')
                    ->columns(2)
                    ->schema([
                        Select::make('asset_code')
                            ->label('币种代码')
                            ->required()
                            ->options($assetOptions)
                            ->in($allowedAssetCodes)
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit'),
                        TextInput::make('asset_name')
                            ->label('币种名称')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('例如: Tether USD'),
                        TextInput::make('network')
                            ->label('网络')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('例如: TRC20'),
                        TextInput::make('sort')
                            ->label('排序')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('address')
                            ->label('收款地址')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('启用')
                            ->default(true),
                    ]),
            ]);
    }
}
