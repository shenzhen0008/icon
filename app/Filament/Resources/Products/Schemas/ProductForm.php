<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $localeOptions = [];
        $localeLabels = (array) config('i18n.locale_labels_zh', []);
        foreach ((array) config('i18n.supported_locales', []) as $locale) {
            if (is_string($locale) && $locale !== '') {
                $label = $localeLabels[$locale] ?? null;
                $localeOptions[$locale] = is_string($label) && $label !== ''
                    ? sprintf('%s %s', $locale, $label)
                    : $locale;
            }
        }

        return $schema
            ->components([
                Section::make('基础信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('产品名称')
                            ->required()
                            ->maxLength(255),
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
                        Select::make('trade_mode')
                            ->label('交易模式')
                            ->options([
                                'direct' => '正式商品（立即购买）',
                                'reserve' => '预订商品（立即预订）',
                            ])
                            ->default('direct')
                            ->required(),
                        TextInput::make('unit_price')
                            ->label('参考单价(USDT)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        TextInput::make('purchase_limit_count')
                            ->label('限购次数')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->helperText('留空表示不限次'),
                    ]),
                Section::make('产品参数')
                    ->columns(3)
                    ->schema([
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
                Section::make('多语言介绍')
                    ->schema([
                        Repeater::make('translations')
                            ->relationship('translations')
                            ->label('按语言维护介绍文案')
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                Select::make('locale')
                                    ->label('语言')
                                    ->required()
                                    ->options($localeOptions),
                                Textarea::make('description')
                                    ->label('介绍文案')
                                    ->rows(4)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
