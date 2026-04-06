<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基础信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('产品名称')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('产品介绍')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('产品代码')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort')
                            ->label('排序')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('是否上架')
                            ->default(true),
                        TextInput::make('unit_price')
                            ->label('每份价格(USDT)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                    ]),
                Section::make('产品参数')
                    ->columns(3)
                    ->schema([
                        TextInput::make('purchase_limit')
                            ->label('限购份数')
                            ->numeric(),
                        TextInput::make('limit_min_usdt')
                            ->label('最低限额(USDT)')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('limit_max_usdt')
                            ->label('最高限额(USDT)')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('rate_min_percent')
                            ->label('最低收益率(%)')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('rate_max_percent')
                            ->label('最高收益率(%)')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('cycle_days')
                            ->label('周期(天)')
                            ->numeric(),
                    ]),
                Section::make('图标设置')
                    ->schema([
                        TextInput::make('product_icon_path')
                            ->label('主图标路径')
                            ->placeholder('/images/products/symbols/symbol-01.png')
                            ->maxLength(255),
                        TagsInput::make('symbol_icon_paths')
                            ->label('币种图标列表')
                            ->placeholder('/images/products/symbols/symbol-01.png'),
                    ]),
            ]);
    }
}
