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
                    ->description('开启拦截后仅允许钱包环境访问，不允许电脑端或移动端浏览器访问。当前支持钱包：MetaMask、OKX Web3 Wallet、TokenPocket、imToken、Bitget Wallet、Coinbase Wallet 等。Trust Wallet 暂不支持，开启后 Trust Wallet 将无法访问，请管理员注意。')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('启用第二层判定（allow/deny）')
                            ->helperText('关闭后将跳过第二层判定与拦截，仅保留第一层采集。')
                            ->default(true),
                    ]),
            ]);
    }
}
