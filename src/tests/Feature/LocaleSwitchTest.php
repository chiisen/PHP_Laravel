<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_can_switch_locale_to_traditional_chinese(): void
    {
        $response = $this->post('/locale/zh_TW');

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'zh_TW');
    }

    public function test_welcome_page_uses_locale_from_session(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('Core Capabilities');
        $response->assertDontSee('關鍵能力');
    }
}
