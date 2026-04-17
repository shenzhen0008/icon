<?php

namespace Tests\Feature\Help;

use App\Modules\Help\Models\HelpItem;
use App\Modules\Help\Models\HelpItemTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_page_renders_faq_items(): void
    {
        $this->get('/help')
            ->assertOk()
            ->assertSee('帮助中心')
            ->assertSee('常见问题')
            ->assertSee('在线客服')
            ->assertSee('/stream-chat')
            ->assertSee('你们是谁？')
            ->assertSee('如何充值？')
            ->assertSee('收益是如何结算的？')
            ->assertSee('<details', false)
            ->assertSee('<summary', false);
    }

    public function test_help_page_localizes_fixed_ui_copy_for_english_without_changing_faq_data_source(): void
    {
        $this->get('/help?locale=en')
            ->assertOk()
            ->assertSee('Help Center')
            ->assertSee('Frequently Asked Questions')
            ->assertSee('Online Support')
            ->assertSee('你们是谁？')
            ->assertSee('如何充值？');
    }

    public function test_help_page_prefers_database_translations_when_help_items_exist(): void
    {
        $helpItem = HelpItem::query()->create([
            'sort' => 1,
            'is_active' => true,
        ]);

        HelpItemTranslation::query()->create([
            'help_item_id' => $helpItem->id,
            'locale' => 'zh-CN',
            'question' => '数据库中文问题',
            'answer' => '数据库中文答案',
        ]);

        HelpItemTranslation::query()->create([
            'help_item_id' => $helpItem->id,
            'locale' => 'en',
            'question' => 'Database English Question',
            'answer' => 'Database English Answer',
        ]);

        $this->get('/help?locale=en')
            ->assertOk()
            ->assertSee('Database English Question')
            ->assertSee('Database English Answer')
            ->assertDontSee('你们是谁？');
    }

    public function test_help_page_falls_back_to_default_locale_translation_when_current_locale_missing(): void
    {
        $helpItem = HelpItem::query()->create([
            'sort' => 1,
            'is_active' => true,
        ]);

        HelpItemTranslation::query()->create([
            'help_item_id' => $helpItem->id,
            'locale' => 'zh-CN',
            'question' => '默认语言问题',
            'answer' => '默认语言答案',
        ]);

        $this->get('/help?locale=en')
            ->assertOk()
            ->assertSee('默认语言问题')
            ->assertSee('默认语言答案');
    }
}
