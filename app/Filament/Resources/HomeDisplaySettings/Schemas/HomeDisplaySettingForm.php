<?php

namespace App\Filament\Resources\HomeDisplaySettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HomeDisplaySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('首页展示数值')
                    ->schema([
                        TextInput::make('summary_people_count')
                            ->label('Number of people')
                            ->helperText('后台填写整数，前台自动加千分位。')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->dehydrateStateUsing(static fn ($state): string => (string) max(0, (int) $state)),
                        TextInput::make('summary_people_step_seconds')
                            ->label('人数跳动秒数')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                        TextInput::make('summary_people_min_delta')
                            ->label('人数跳动范围最小值')
                            ->required()
                            ->numeric()
                            ->step('1'),
                        TextInput::make('summary_people_max_delta')
                            ->label('人数跳动范围最大值')
                            ->required()
                            ->numeric()
                            ->step('1'),
                        TextInput::make('summary_total_profit')
                            ->label('总盘获利值')
                            ->helperText('后台填写纯数字，支持两位小数；USDT 固定在前台展示。')
                            ->required()
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->dehydrateStateUsing(static fn ($state): string => number_format((float) $state, 2, '.', '')),
                        TextInput::make('summary_profit_step_seconds')
                            ->label('获利跳动秒数')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                        TextInput::make('summary_profit_min_delta')
                            ->label('获利跳动范围最小值')
                            ->required()
                            ->numeric()
                            ->step('0.01'),
                        TextInput::make('summary_profit_max_delta')
                            ->label('获利跳动范围最大值')
                            ->required()
                            ->numeric()
                            ->step('0.01'),
                        TextInput::make('shared_exchange_profit_base_value')
                            ->label('统一平台获利基础值')
                            ->helperText('所有平台共用这个基础值，前端每次都从这个值重新计算浮动。')
                            ->required()
                            ->numeric()
                            ->step('0.01')
                            ->dehydrateStateUsing(static fn ($state): string => number_format((float) $state, 2, '.', '')),
                        TextInput::make('shared_exchange_profit_step_seconds')
                            ->label('平台获利跳动秒数')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                        TextInput::make('shared_exchange_profit_min_delta')
                            ->label('平台获利跳动范围最小值')
                            ->required()
                            ->numeric()
                            ->step('0.01'),
                        TextInput::make('shared_exchange_profit_max_delta')
                            ->label('平台获利跳动范围最大值')
                            ->required()
                            ->numeric()
                            ->step('0.01'),
                    ]),
            ]);
    }
}
