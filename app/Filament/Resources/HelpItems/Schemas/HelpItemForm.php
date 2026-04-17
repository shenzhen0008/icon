<?php

namespace App\Filament\Resources\HelpItems\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HelpItemForm
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
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort')
                            ->label('排序')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('启用')
                            ->default(true),
                    ]),
                Section::make('多语言问答')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('translations')
                            ->relationship('translations')
                            ->label('按语言维护问题与答案')
                            ->defaultItems(0)
                            ->grid([
                                'md' => 2,
                                'xl' => 3,
                                '2xl' => 4,
                            ])
                            ->columns(2)
                            ->schema([
                                Select::make('locale')
                                    ->label('语言')
                                    ->required()
                                    ->options($localeOptions),
                                TextInput::make('question')
                                    ->label('问题')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('answer')
                                    ->label('答案')
                                    ->required()
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
