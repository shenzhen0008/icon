<?php

namespace Tests\Feature\Legal;

use Tests\TestCase;

class LegalDocumentPageTest extends TestCase
{
    public function test_privacy_page_loads_matching_locale_document(): void
    {
        $this->get('/privacy?locale=zh-CN')
            ->assertOk()
            ->assertSee('隐私政策')
            ->assertSee('Zorai AI套利DApp 隐私政策与用户服务协议');
    }

    public function test_terms_page_loads_matching_locale_document(): void
    {
        $this->get('/terms?locale=de')
            ->assertOk()
            ->assertSee('Nutzungsbedingungen')
            ->assertSee('Diese Nutzungsbedingungen');
    }

    public function test_public_page_footer_has_separate_privacy_and_terms_links(): void
    {
        $this->get('/login?locale=en')
            ->assertOk()
            ->assertSee('/privacy?locale=en')
            ->assertSee('/terms?locale=en');
    }
}
