<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

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
                            ->disabledOn('edit')
                            ->unique(ignoreRecord: true),
                        Select::make('user_level')
                            ->label('用户等级')
                            ->options([0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                            ->disabled(fn (): bool => Gate::denies('access-admin')),
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
