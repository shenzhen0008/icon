<?php

namespace Tests\Feature\I18n;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SetLocaleMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_test/locale', function (): JsonResponse {
            return response()->json([
                'locale' => app()->getLocale(),
                'session_locale' => session('locale'),
            ]);
        });
    }

    public function test_it_uses_default_locale_when_session_and_request_do_not_set_locale(): void
    {
        $response = $this->get('/_test/locale');

        $response->assertOk()
            ->assertJsonPath('locale', 'zh-CN')
            ->assertJsonPath('session_locale', null);
    }

    public function test_it_can_set_and_persist_locale_from_request_query(): void
    {
        $this->get('/_test/locale?locale=en')
            ->assertOk()
            ->assertJsonPath('locale', 'en')
            ->assertJsonPath('session_locale', 'en');

        $this->get('/_test/locale')
            ->assertOk()
            ->assertJsonPath('locale', 'en')
            ->assertJsonPath('session_locale', 'en');
    }

    public function test_it_ignores_unsupported_locale_value(): void
    {
        $this->withSession(['locale' => 'ja'])
            ->get('/_test/locale?locale=invalid-locale')
            ->assertOk()
            ->assertJsonPath('locale', 'ja')
            ->assertJsonPath('session_locale', 'ja');
    }
}
