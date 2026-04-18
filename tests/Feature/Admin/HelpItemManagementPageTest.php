<?php

namespace Tests\Feature\Admin;

use App\Modules\Help\Models\HelpItem;
use App\Modules\Help\Models\HelpItemTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpItemManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_help_item_management_pages_in_local_environment(): void
    {
        $helpItem = HelpItem::query()->create([
            'sort' => 1,
            'is_active' => true,
        ]);

        HelpItemTranslation::query()->create([
            'help_item_id' => $helpItem->id,
            'locale' => 'zh-CN',
            'question' => '默认问题',
            'answer' => '默认答案',
        ]);

        $this->get('/admin/help-items')
            ->assertOk()
            ->assertSee('帮助FAQ')
            ->assertSee('默认语言问题');

        $this->get('/admin/help-items/create')
            ->assertOk()
            ->assertSee('多语言问答')
            ->assertSee('语言')
            ->assertSee('问题')
            ->assertSee('答案');

        $this->get('/admin/help-items/'.$helpItem->id.'/edit')
            ->assertOk()
            ->assertSee('多语言问答')
            ->assertSee('按语言维护问题与答案')
            ->assertSee('语言');
    }
}
