<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * 測試首頁是否能正常存取
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // 方式 1: 使用 Laravel Log (會寫入 storage/logs/laravel.log)
        \Illuminate\Support\Facades\Log::info('開始執行首頁健康檢查測試');

        $response = $this->get('/');

        // 方式 2: 使用 dump() 直接在 Console 顯示變數內容 (最常用於除錯)
        dump('測試中: 正在檢查首頁狀態...');

        // 方式 3: 查看 Response 的具體內容
        $response->dumpHeaders(); // 查看標頭
        $response->dump();        // 查看回傳的完整 HTML (內容可能會很長)

        $response->assertStatus(200);
    }
}