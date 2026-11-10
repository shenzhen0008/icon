<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('用户信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('username')
                            ->label('用户名')
                            ->required()
                            ->maxLength(21)
                            ->unique(ignoreRecord: true),
                        Textarea::make('remark')
                            ->label('备注')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('balance')
                            ->label('余额(USDT)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        TextInput::make('password')
                            ->label('密码')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
