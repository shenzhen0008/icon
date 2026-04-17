<?php

namespace Tests\Feature\Product;

use Tests\TestCase;

class ProductRulesPageTest extends TestCase
{
    public function test_product_rules_page_renders_rule_sections(): void
    {
        $this->get('/products/rules')
            ->assertOk()
            ->assertSee('AI Trading')
            ->assertSee('安全结算')
            ->assertSee('每日执行')
            ->assertSee('自动返还')
            ->assertSee('收益说明')
            ->assertSee('赎回规则')
            ->assertDontSee('到账方式')
            ->assertSee('风险提示')
            ->assertSee('前往产品市场')
            ->assertSee('rounded-2xl border border-[rgb(var(--theme-primary))]/20 bg-theme-card/95 p-3 shadow-xl', false)
            ->assertSee('space-y-3 pt-3', false)
            ->assertDontSee('rounded-3xl border border-theme bg-theme-card p-6 shadow-xl', false)
            ->assertDontSee('rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-5 shadow-lg shadow-[rgb(var(--theme-primary))]/10', false)
            ->assertDontSee('grid grid-cols-3 gap-3', false)
            ->assertDontSee('rounded-[1.5rem] border border-theme bg-theme-secondary/20', false)
            ->assertDontSee('bg-gradient-to-b from-[rgb(var(--theme-primary))]/90 via-[rgb(var(--theme-primary))]/70 to-[rgb(var(--theme-accent))]/20', false);
    }
}
