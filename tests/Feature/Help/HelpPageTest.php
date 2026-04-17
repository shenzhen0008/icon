<?php

namespace Tests\Feature\Help;

use Tests\TestCase;

class HelpPageTest extends TestCase
{
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
}
