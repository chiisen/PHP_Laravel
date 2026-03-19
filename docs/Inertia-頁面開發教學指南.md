# Laravel Inertia 頁面開發完整教學

## 什麼是 Inertia？

Inertia.js 是一個讓你在服務端框架（Laravel、Rails、Spring Boot 等）與前端框架（React、Vue、Svelte）之間建立橋樑的工具。它允許你使用傳統的 PHP 後端路由和控制器返回前端組件，而不是使用 API 端點。

### Inertia 的工作流程

```
瀏覽器 → Laravel 路由 → Controller → Inertia 響應 → 前端組件渲染
```

### 傳統 API vs Inertia

**傳統前端架構（SPA 配合 API）：**
```
Frontend (React/Vue) ←→ API (Laravel JSON endpoint) ←→ Database
```

**Inertia 架構：**
```
Browser → Laravel Router → Controller → Inertia Page Component → Vue/React Component
```

## 為什麼使用 Inertia？

1. **無需 API：** 不需要建立 RESTful API 端點
2. **保持 Laravel 常規：** 繼續使用 Laravel 的路由、控制器、中介層、授權等功能
3. **SSR 支援：** 可與 SSR 結合提供更好 SEO
4. **輕量化：** 比傳統 SPA 更輕巧
5. **快速開發：** 減少前後端對接成本

## 安裝與設定

### 1. 安装卸載套件

```bash
# 安裝後端套件
composer require inertiajs/inertia-laravel

# 安裝前端套件 (Vue 3 版本)
npm install @inertiajs/vue3
```

### 2. 佈署模板到資源目錄

```bash
# 在 Laravel 的 AppServiceProvider@register() 方法中加入
public function register()
{
    // ...
    
    // 可選：將 Inertia 模板佈署到資源目錄，這樣可以自訂模板
    if ($this->app->environment('local')) {
        $this->publishes([
            __DIR__.'/../vendor/inertiajs/inertia-laravel/src/Console/stubs/app.blade.php' => resource_path('views/app.blade.php'),
        ], 'inertia');
    }
}
```

或是使用 Artisan 指令：

```bash
php artisan vendor:publish --provider="Inertia\InertiaServiceProvider" --tag="InertiaViews"
```

### 3. 設定 Laravel 應用主模板 (resources/views/app.blade.php)

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'My App' }}</title>
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- 如果你使用 Laravel Mix -->
    <!-- <link href="{{ mix('css/app.css') }}" rel="stylesheet"> -->
    
    <!-- Inertia 加載指示器 (可選) -->
    <script>
        window.addEventListener('beforeunload', (event) => {
            if(window.event && event.clientX <= 0 && event.clientY <= 0){
                console.log('User clicked browser close button');
            } else {
                const response = confirm('');
                if(response === false) {
                    event.preventDefault();
                    event.returnValue = '';
                    console.log('User cancelled navigation');
                } else {
                    console.log('User confirmed navigation');
                }
            }
        });

        // 全域加載指示器示例
        document.addEventListener('DOMContentLoaded', function() {
            const loadingIndicator = document.getElementById('loading-indicator');
            
            // 顯示加載指示器
            window.showLoading = function() {
                if(loadingIndicator) loadingIndicator.style.display = 'block';
            };

            // 隱藏加載指示器
            window.hideLoading = function() {
                if(loadingIndicator) loadingIndicator.style.display = 'none';
            };
        });
    </script>
</head>
<body>
    @routes
    <div id="app" data-page="{{ json_encode($page) }}"></div>
</body>
</html>
```

### 4. 設定前端入口點 (resources/js/app.js)

```javascript
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
```

## 基本使用範例

### 1. 創建第一個 Inertia 控制器

```php
<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Index', [
            'user' => auth()->user(),
            'posts' => auth()->user()->posts()->latest()->get(),
        ]);
    }
}
```

### 2. 在路由中定義

```php
<?php
// routes/web.php
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

### 3. 建立前端頁面組件 (resources/Pages/Dashboard/Index.vue)

```vue
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
    
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Welcome Back, {{ user.name }}!</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <p class="text-blue-800 font-medium">Total Posts</p>
          <p class="text-2xl">{{ posts.length }}</p>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
          <p class="text-green-800 font-medium">Published</p>
          <p class="text-2xl">{{ publishedPostsCount }}</p>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg">
          <p class="text-purple-800 font-medium">Drafts</p>
          <p class="text-2xl">{{ draftsCount }}</p>
        </div>
      </div>
      
      <div v-if="posts.length">
        <h3 class="text-lg font-medium mb-2">Recent Posts</h3>
        <ul class="space-y-2">
          <li v-for="post in posts" :key="post.id" class="flex justify-between items-center bg-gray-50 p-3 rounded-md">
            <span>{{ post.title }}</span>
            <span class="text-sm text-gray-500">{{ formatDate(post.created_at) }}</span>
          </li>
        </ul>
      </div>
      <div v-else class="text-center text-gray-500 py-4">
        No posts yet.
      </div>
    </div>
    
    <button 
      @click="createPost" 
      class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded flex items-center"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
      </svg>
      Create New Post
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  posts: Array,
})

const publishedPostsCount = computed(() => 
  props.posts.filter(post => post.published).length
)

const draftsCount = computed(() => 
  props.posts.filter(post => !post.published).length
)

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const createPost = () => {
  router.get('/posts/create')
}
</script>
```

## Inertia 資料共享

### 1. 全域資料共享

```php
<?php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
                'version' => config('app.version', '1.0.0'),
            ],
            'auth' => function () {
                return [
                    'user' => auth()->user() ? [
                        'id' => auth()->user()->id,
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                        'avatar' => auth()->user()->avatar_url,
                    ] : null,
                ];
            },
            'flash' => function (Request $request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'message' => $request->session()->get('message'),
                ];
            },
            'notifications' => function () {
                if (auth()->check()) {
                    return Notification::where('user_id', auth()->id())
                                     ->where('read', false)
                                     ->latest()
                                     ->limit(5)
                                     ->get()
                                     ->map(fn($notification) => [
                                         'id' => $notification->id,
                                         'message' => $notification->message,
                                         'created_at' => $notification->created_at->diffForHumans(),
                                     ]);
                }
                return collect([]);
            },
            'ziggy' => function () {
                // 要在頁面中使用 Laravel 路由生成功能
                return [
                    'location' => app('url')->full(),
                    ...route('home')->getZiggy(),
                ];
            },
        ]);

        Inertia::macro('render', function ($component, $props = []) {
            // 在渲染前對資料做一些特殊處理
            return Inertia::component($component)
                          ->with(
                              array_merge_recursive($props, [
                                  'timestamp' => now()->toISOString(),
                                  'csrf_token' => csrf_token(),
                              ])
                          );
        });
    }
}
```

### 2. 頁面特定資料

```php
<?php
// 控制器中的特定資料分享
class PostController extends Controller
{
    public function index()
    {
        return Inertia::render('Posts/Index', [
            'posts' => Post::published()
                         ->with('author', 'category')
                         ->paginate(10)
                         ->through(function ($post) {
                             return [
                                 'id' => $post->id,
                                 'title' => $post->title,
                                 'excerpt' => $post->excerpt,
                                 'published_at' => $post->published_at->format('M d Y'),
                                 'author' => [
                                     'name' => $post->author->name,
                                     'avatar' => $post->author->avatar_url,
                                 ],
                                 'category' => $post->category->name,
                             ];
                         }),
            'filters' => request()->only(['search', 'category', 'featured']),
            'categories' => Category::all()->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]),
        ]);
    }
}
```

## 表單提交與表單助手

### 1. 安裝表單助手 (Form Helper)

```bash
npm install @inertiajs/forms
```

### 2. 更新前端入口點以包含表單助手

```javascript
// resources/js/app.js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createForm } from '@inertiajs/forms'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m'

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
```

### 3. 在頁面元件中使用表單助手

```vue
<!-- resources/Pages/Contact/Create.vue -->
<template>
  <div class="container mx-auto px-4 py-8 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Contact Us</h1>
    
    <form @submit.prevent="submitForm" class="bg-white shadow rounded-lg p-6">
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
          Name
        </label>
        <input
          v-model="form.name"
          type="text"
          id="name"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{ 'border-red-500': form.errors.name }"
          placeholder="Your name"
        />
        <div v-if="form.errors.name" class="text-red-500 text-xs italic mt-1">
          {{ form.errors.name }}
        </div>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
          Email
        </label>
        <input
          v-model="form.email"
          type="email"
          id="email"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{ 'border-red-500': form.errors.email }"
          placeholder="your@email.com"
        />
        <div v-if="form.errors.email" class="text-red-500 text-xs italic mt-1">
          {{ form.errors.email }}
        </div>
      </div>
      
      <div class="mb-6">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="message">
          Message
        </label>
        <textarea
          v-model="form.message"
          id="message"
          rows="5"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{ 'border-red-500': form.errors.message }"
          placeholder="Your message"
        ></textarea>
        <div v-if="form.errors.message" class="text-red-500 text-xs italic mt-1">
          {{ form.errors.message }}
        </div>
      </div>
      
      <div class="flex items-center justify-between">
        <button
          type="submit"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Processing...</span>
          <span v-else>Send Message</span>
        </button>
        
        <button
          @click="resetForm"
          type="button"
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2"
        >
          Reset
        </button>
      </div>
    </form>
    
    <div v-if="showSuccessMessage" class="mt-4 bg-green-50 text-green-800 p-4 rounded-lg">
      Message sent successfully! We'll get back to you shortly.
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/forms'

const form = useForm({
  name: '',
  email: '',
  message: '',
})

const showSuccessMessage = ref(false)

const submitForm = () => {
  form.post('/contact', {
    onSuccess: () => {
      setTimeout(() => {
        showSuccessMessage.value = true
        setTimeout(() => {
          showSuccessMessage.value = false
          resetForm()
        }, 3000)
      }, 500)
    },
    onError: () => {
      showSuccessMessage.value = false
    }
  })
}

const resetForm = () => {
  form.reset()
  form.clearErrors()
}

defineExpose({
  form
})
</script>
```

## Inertia 路由與導航

### 1. 範例後端路由與控制器

```php
<?php
// routes/web.php
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resources([
        '/contacts' => ContactController::class,
    ]);
});

// Guest routes
Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::put('/contact', [ContactController::class, 'store'])->name('contact.store');
```

### 2. 前端頁面元件中的導航

```vue
<!-- resources/Pages/Dashboard/Index.vue -->
<template>
  <div>
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <span class="font-bold text-xl">MyApp</span>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <a href="#" 
                 @click.prevent="$inertia.visit(route('dashboard'))"
                 :class="{'border-indigo-500 text-gray-900': $page.component === 'Dashboard/Index'}"
                 class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                Dashboard
              </a>
              
              <a href="#" 
                 @click.prevent="$inertia.visit(route('contacts.index'))"
                 :class="{'border-indigo-500 text-gray-900': $page.component === 'Contacts/Index'}"
                 class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                Contacts
              </a>
            </div>
          </div>
          
          <div class="flex items-center">
            <div class="ml-3 relative">
              <div class="flex items-center space-x-4">
                <button 
                  @click="logout"
                  class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const logout = () => {
  router.post(route('logout'))
}
</script>
```

## Vue 3 深度整合

### 1. Inertia 資料轉換與處理

```vue
<!-- resources/Pages/Posts/Show.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <article class="bg-white rounded-lg shadow p-8 mb-8">
      <h1 class="text-3xl font-bold mb-4">{{ post.title }}</h1>
      
      <div class="flex items-center text-sm text-gray-500 mb-6">
        <img :src="post.author.avatar" :alt="post.author.name" class="w-8 h-8 rounded-full mr-2" />
        <span>{{ post.author.name }}</span>
        <span class="mx-2">•</span>
        <time :datetime="post.published_at">{{ formattedDate(post.published_at) }}</time>
        <span class="mx-2">•</span>
        <span>{{ post.category }}</span>
      </div>
      
      <div class="prose max-w-none" v-html="post.content"></div>
    </article>
    
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Comments ({{ comments.length }})</h2>
      
      <div v-if="comments.length" class="space-y-4">
        <div v-for="comment in comments" :key="comment.id" class="border-b pb-4 last:border-0 last:pb-0">
          <div class="flex items-start">
            <img :src="comment.user.avatar" :alt="comment.user.name" class="w-10 h-10 rounded-full mr-4" />
            <div class="flex-1">
              <h4 class="font-bold">{{ comment.user.name }}</h4>
              <p class="text-gray-600 text-sm">{{ comment.content }}</p>
              <time class="text-gray-500 text-xs">{{ moment(comment.created_at.toString()).fromNow() }}</time>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-gray-500 italic">
        No comments yet. Be the first to leave a comment.
      </div>
    </div>
  </div>
</template>

<script setup>
import moment from 'moment'
import { ref } from 'vue'

const props = defineProps({
  post: Object,
  comments: Array,
})

// 使用 computed 追蹤資料變化
const post = computed(() => props.post)
const comments = computed(() => props.comments)

// 格式化時間的幫助函式
const formattedDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// 為日期時間加入 reactive 計算
const timeAgo = computed(() => {
  return moment(props.post.published_at.toString()).fromNow()
})
</script>
```

### 2. 自訂 Composables

```javascript
// resources/js/Composables/useUser.js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useUser() {
  const page = usePage()
  
  const user = computed(() => page.props.auth?.user || null)
  const isAuthenticated = computed(() => !!user.value)
  
  const hasRole = (role) => {
    return user.value && user.value.roles?.includes(role)
  }
  
  const can = (permission) => {
    return user.value.permissions.includes(permission)
  }
  
  return {
    user,
    isAuthenticated,
    hasRole,
    can
  }
}
```

## 錯誤處理與例外

### 1. 後端異常處理 (app/Exceptions/Handler.php)

```php
<?php
// In Laravel 11+, in app/Exceptions/Handler.php
use Inertia\Inertia;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Record not found'], 404);
            }
            
            return Inertia::render('Errors/404')
                          ->toResponse($request)
                          ->setStatusCode(404);
        });
        
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->user()) {
                return Inertia::render('Errors/403')
                              ->toResponse($request)
                              ->setStatusCode(403);
            }
            
            return redirect()->guest(route('login'));
        });
        
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
        });
    }
}
```

### 2. 前端錯誤處理

```vue
<!-- resources/Pages/Errors/404.vue -->
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      
      <h1 class="mt-4 text-4xl font-bold text-gray-900">Page Not Found</h1>
      <p class="mt-2 text-gray-600">
        Sorry, we couldn't find the page you were looking for.
      </p>
      
      <div class="mt-8">
        <button 
          @click="goHome" 
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Go back home
          <svg xmlns="http://www.w3.org/2000/svg" class="-mr-1 ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

function goHome() {
  router.get('/')
}
</script>
```

## 高級功能應用

### 1. 分頁處理

```vue
<!-- resources/Pages/Posts/Index.vue -->
<template>
  <div>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        <li v-for="post in posts.data" :key="post.id">
          <div class="px-4 py-4 flex items-center sm:px-6">
            <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
              <div class="truncate">
                <div class="text-sm font-medium text-indigo-600 truncate">
                  {{ post.title }}
                </div>
                <div class="mt-2 flex">
                  <div class="flex items-center text-sm text-gray-500">
                    <UserIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                    {{ post.author.name }}
                  </div>
                </div>
              </div>
              <div class="mt-4 flex-shrink-0 sm:mt-0">
                <span v-if="post.published" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Published
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Draft
                </span>
              </div>
            </div>
          </div>
        </li>
      </ul>
      
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6" v-if="hasPagination">
        <div class="flex-1 flex justify-between sm:hidden">
          <button v-if="posts.prev_page_url" @click="prevPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Previous
          </button>
          <button @click="nextPage" class="relative inline-flex items-center px-4 py-2 ml-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ posts.from }}</span>
              to
              <span class="font-medium">{{ posts.to }}</span>
              of
              <span class="font-medium">{{ posts.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <a v-if="posts.prev_page_url" 
                 @click.prevent="prevPage" 
                 href="#"
                 class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Previous</span>
                <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
              </a>
              
              <a 
                v-for="(page, index) in paginationRange" 
                :key="index"
                href="#"
                @click.prevent="gotoPage(page)"
                :class="{ 
                  'bg-indigo-50 border-indigo-500 text-indigo-600': page.number === posts.current_page, 
                  'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': page.number !== posts.current_page 
                }"
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              >
                {{ page.label }}
              </a>
              
              <a v-if="posts.next_page_url" 
                 @click.prevent="nextPage" 
                 href="#"
                 class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Next</span>
                <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
              </a>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { ChevronRightIcon, ChevronLeftIcon, UserIcon } from '@heroicons/vue/solid'

const props = defineProps({
  posts: Object
})

const paginationRange = computed(() => {
  const current = props.posts.current_page
  const total = props.posts.last_page
  
  if (total <= 5) {
    return Array.from({ length: total }, (_, i) => ({
      number: i + 1,
      label: (i + 1).toString()
    }))
  }
  
  const pages = []
  
  // First page
  pages.push({ number: 1, label: '1' })
  
  if (current > 3) {
    pages.push({ number: -1, label: '...' })
  }
  
  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)
  
  for (let i = start; i <= end; i++) {
    pages.push({ number: i, label: i.toString() })
  }
  
  if (current < total - 2) {
    pages.push({ number: -1, label: '...' })
  }
  
  if (total > 1) {
    pages.push({ number: total, label: total.toString() })
  }
  
  return pages
})

const hasPagination = computed(() => props.posts.last_page > 1)

const gotoPage = (page) => {
  if (page.number !== -1 && page.number !== props.posts.current_page) {
    router.get(route('posts.index', { page: page.number }))
  }
}

const prevPage = () => {
  if (props.posts.current_page > 1) {
    router.get(route('posts.index', { page: props.posts.current_page - 1 }))
  }
}

const nextPage = () => {
  if (props.posts.current_page < props.posts.last_page) {
    router.get(route('posts.index', { page: props.posts.current_page + 1 }))
  }
}
</script>
```

### 2. 資料驗證

```javascript
// 在控制器中進行資料驗證
class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10',
        ]);
        
        // 儲存聯絡資料...
        
        return redirect()->back()
                         ->with('success', 'Thank you for your message! We\'ll get back to you shortly.');
    }
}
```

## 性能優化技巧

### 1. 懶加載與程式碼分割

Inertia 支援 Vue 的懒加载特性，可以在 route 定义时分割代码：

```javascript
// 在入口 js 中定义
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`, 
        import.meta.glob('./Pages/**/*.vue', { eager: false })
    ), // 设定为 false 启用懒加载
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
```

### 2. 資料最小化

```php
// 只返回頁面所需的資料，避免返回過多資料
public function show(Post $post)
{
    return Inertia::render('Posts/Show', [
        'post' => [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'published_at' => $post->published_at,
        ],
        'author' => $post->author->only(['id', 'name', 'avatar']),
        'similar_posts' => $post->similar()
                                ->limit(3)
                                ->get(['id', 'title', 'slug'])
                                ->toArray(),
    ]);
}
```

## 最佳實踐總結

1. **頁面結構一致**：確保所有頁面都遵循相似的結構和設計模式
2. **全域共享資料**：適度使用 `Inertia::share()` 共享全局資料
3. **表單處理**：利用 `@inertiajs/forms` 包裝簡化表單處理
4. **錯誤處理**：實現全局錯誤處理程式
5. **效能考量**：注意不要過度使用 Inertia 共享全站資訊
6. **安全性**：始終驗證和轉譯所有使用者輸入資料

使用 Inertia 可以讓你在保持 Laravel 傳統開發體驗的同時享受現代前端框架的好處。它是架設在傳統 PHP 框架與現代前端框架之間的理想橋樑。 