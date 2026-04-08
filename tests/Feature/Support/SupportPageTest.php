<?php

namespace Tests\Feature\Support;

use Tests\TestCase;

class SupportPageTest extends TestCase
{
    public function test_support_page_renders_official_widget_script_when_tawk_is_enabled(): void
    {
        config()->set('support.tawk.enabled', true);
        config()->set('support.tawk.property_id', '69d2b6406c34951c3533e334');
        config()->set('support.tawk.widget_id', '1jlfhfro7');

        $this->get('/support')
            ->assertOk()
            ->assertSee('data-theme="', false)
            ->assertSee('bg-theme text-theme')
            ->assertSee('客服中心')
            ->assertSee('embed.tawk.to')
            ->assertSee('69d2b6406c34951c3533e334')
            ->assertSee('1jlfhfro7');
    }

    public function test_support_page_shows_unavailable_notice_when_tawk_is_not_configured(): void
    {
        config()->set('support.tawk.enabled', false);
        config()->set('support.tawk.property_id', null);
        config()->set('support.tawk.widget_id', null);

        $this->get('/support')
            ->assertOk()
            ->assertSee('data-theme="', false)
            ->assertSee('bg-theme text-theme')
            ->assertSee('客服系统暂未配置完成');
    }
}
