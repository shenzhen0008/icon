<?php

namespace App\Filament\Resources\RechargeReceivers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RechargeReceiverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('收款账户信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('asset_code')
                            ->label('币种代码')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('例如: USDT'),
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
