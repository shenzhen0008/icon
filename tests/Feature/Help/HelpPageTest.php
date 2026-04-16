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
}
