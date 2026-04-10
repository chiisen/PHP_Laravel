<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                產品營運儀表板（DEMO）
            </h2>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                展示模式
            </span>
        </div>
    </x-slot>

    @php
        $kpis = [
            ['title' => '今日活躍用戶', 'value' => '12,480', 'delta' => '+8.2%'],
            ['title' => '轉換率', 'value' => '4.7%', 'delta' => '+0.6%'],
            ['title' => '平均回應時間', 'value' => '128 ms', 'delta' => '-11%'],
            ['title' => '本週工單', 'value' => '37', 'delta' => '+3'],
        ];

        $events = [
            ['time' => '09:10', 'title' => '行銷活動 A/B Test 啟動', 'type' => '行銷'],
            ['time' => '10:45', 'title' => '新版本灰度佈署 20%', 'type' => '佈署'],
            ['time' => '12:30', 'title' => '客服回報量下降', 'type' => '客服'],
            ['time' => '14:05', 'title' => '支付成功率回升', 'type' => '營運'],
        ];

        $members = [
            ['name' => '王小明', 'role' => 'PM', 'status' => '在線', 'task' => '首頁改版規劃'],
            ['name' => '陳雅婷', 'role' => 'Backend', 'status' => '在線', 'task' => 'API 觀測指標整合'],
            ['name' => '林冠宇', 'role' => 'Frontend', 'status' => '會議中', 'task' => 'i18n 版面檢查'],
            ['name' => '張品涵', 'role' => 'QA', 'status' => '待命', 'task' => '回歸測試清單'],
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($kpis as $kpi)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-slate-500">{{ $kpi['title'] }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $kpi['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-emerald-600">{{ $kpi['delta'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-12">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-8">
                    <h3 class="text-base font-semibold text-slate-900">專案進度總覽</h3>
                    <p class="mt-1 text-sm text-slate-500">以下資料為示意用假資料，僅供簡報與介面展示。</p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">會員系統重構</span>
                                <span class="text-slate-500">72%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[72%] rounded-full bg-indigo-500"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">支付流程優化</span>
                                <span class="text-slate-500">48%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[48%] rounded-full bg-cyan-500"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">客服後台 i18n</span>
                                <span class="text-slate-500">90%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[90%] rounded-full bg-emerald-500"></div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-4">
                    <h3 class="text-base font-semibold text-slate-900">即時事件（DEMO）</h3>
                    <ul class="mt-4 space-y-3">
                        @foreach ($events as $event)
                            <li class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-500">{{ $event['time'] }} · {{ $event['type'] }}</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $event['title'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-12">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-8">
                    <h3 class="text-base font-semibold text-slate-900">團隊任務面板（DEMO）</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="py-2 pr-3">成員</th>
                                    <th class="py-2 pr-3">角色</th>
                                    <th class="py-2 pr-3">狀態</th>
                                    <th class="py-2">目前任務</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($members as $member)
                                    <tr>
                                        <td class="py-3 pr-3 font-medium">{{ $member['name'] }}</td>
                                        <td class="py-3 pr-3">{{ $member['role'] }}</td>
                                        <td class="py-3 pr-3">
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ $member['status'] }}</span>
                                        </td>
                                        <td class="py-3">{{ $member['task'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-gradient-to-b from-indigo-50 to-white p-6 shadow-sm lg:col-span-4">
                    <h3 class="text-base font-semibold text-slate-900">即將上線</h3>
                    <ul class="mt-4 space-y-3 text-sm text-slate-700">
                        <li class="rounded-lg bg-white p-3">3/15：新版通知中心視覺調整</li>
                        <li class="rounded-lg bg-white p-3">3/18：行為分析報表模組展示</li>
                        <li class="rounded-lg bg-white p-3">3/22：客服 KPI 看板整合</li>
                    </ul>
                    <p class="mt-4 text-xs text-slate-500">此區塊為純展示，不會觸發任何實際流程。</p>
                </article>
            </section>
        </div>
    </div>
</x-app-layout>
