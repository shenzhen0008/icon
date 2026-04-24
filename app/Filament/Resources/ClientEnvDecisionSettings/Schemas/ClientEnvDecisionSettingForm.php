<?php

namespace App\Filament\Resources\ClientEnvDecisionSettings\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientEnvDecisionSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('客户端环境二层判定开关')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('启用第二层判定（allow/deny）')
                            ->helperText('关闭后将跳过第二层判定与拦截，仅保留第一层采集。')
                            ->default(true),
                    ]),
            ]);
    }
}

