# Laravel LogWeaver - Geliştirme Planı

> **Vizyon:** Kullanımı son derece kolay, bir o kadar güçlü bir Laravel log yönetim paketi.
> Tüm Laravel best practice'leri zorunludur.

---

## Mevcut Durum Analizi

### Güçlü Yanlar
- Fluent builder pattern (chainable API)
- Facade desteği
- Temel validation mekanizması
- S3/local/ftp/sftp disk desteği
- Pest PHP test altyapısı
- PHPStan static analysis (level 5)
- CI/CD pipeline'ları

### Zayıf Yanlar & Eksiklikler
| # | Sorun | Etki |
|---|-------|------|
| 1 | Laravel'in native Log sistemiyle entegrasyon yok | Ekosistem uyumsuzluğu |
| 2 | Database logging yok, sadece dosya | Sorgulama/arama imkansız |
| 3 | Middleware yok | Her yere manual log yazılmak zorunda |
| 4 | Artisan komutları yok | DevX zayıf |
| 5 | Event/Listener sistemi yok | Hook/extension imkansız |
| 6 | Context enrichment yok (user, request, trace) | Loglar bağlamsız |
| 7 | Log rotation/retention yok | Disk şişer |
| 8 | Exception handler entegrasyonu yok | Otomatik hata yakalama yok |
| 9 | Async implementasyon crude (sleep polling) | Performans sorunu |
| 10 | Config çok minimal (sadece sleep) | Yapılandırılabilirlik düşük |
| 11 | Batch logging yok | Yüksek hacimli senaryolar verimsiz |
| 12 | Log query/search API yok | Logları aramak imkansız |
| 13 | Structured logging standardı yok | Tutarsız log formatı |
| 14 | Rate limiting yok | Spam koruması yok |
| 15 | Log channel/driver abstraction yok | Extensibility düşük |

---

## Mimari Tasarım

### Hedef Mimari

```
┌─────────────────────────────────────────────────────┐
│                   LogWeaver Facade                   │
│          (Basit, akıcı, güçlü public API)           │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                  LogManager                           │
│  (Central orchestrator - channel routing, pipeline)  │
└──┬───────────┬───────────┬───────────┬──────────────┘
   │           │           │           │
┌──▼──┐   ┌───▼───┐   ┌───▼───┐   ┌───▼───┐
│ DB  │   │ File  │   │ Stack │   │Custom │
│Chan.│   │ Chan. │   │ Chan. │   │Driver │
└──┬──┘   └───┬───┘   └───┬───┘   └───┬───┘
   │          │           │            │
┌──▼──────────▼───────────▼────────────▼──────────────┐
│              Driver Interface (Contracts)             │
│     DatabaseDriver | FileDriver | NullDriver | ...   │
└─────────────────────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                 Support Layer                         │
│  Context | Formatter | Serializer | RateLimiter      │
└─────────────────────────────────────────────────────┘
```

### Dizin Yapısı (Hedef)

```
src/
├── LogWeaver.php                    # Fluent API (refactored)
├── LogManager.php                   # Channel manager
├── LogWeaverServiceProvider.php     # Service provider (enhanced)
│
├── Contracts/
│   ├── LogDriverInterface.php       # Driver contract
│   ├── FormatterInterface.php       # Formatter contract
│   ├── ContextResolverInterface.php # Context resolver contract
│   └── LogQueryInterface.php        # Query builder contract
│
├── Drivers/
│   ├── DatabaseDriver.php           # Eloquent/DB logging
│   ├── FileDriver.php               # Filesystem logging (mevcut, refactored)
│   ├── StackDriver.php              # Multi-driver birleşik
│   └── NullDriver.php               # Test/dev için
│
├── Models/
│   └── LogEntry.php                 # Eloquent model
│
├── Context/
│   ├── ContextManager.php           # Context collection
│   ├── Resolvers/
│   │   ├── RequestResolver.php      # HTTP request context
│   │   ├── UserResolver.php         # Auth user context
│   │   ├── AppResolver.php          # App environment context
│   │   └── ExceptionResolver.php    # Exception context
│   └── ContextBag.php               # Context value object
│
├── Middleware/
│   ├── LogRequestMiddleware.php     # HTTP request/response logging
│   └── LogSlowRequestMiddleware.php # Slow request detection
│
├── Events/
│   ├── LogCreated.php               # After log write
│   ├── LogFailed.php                # Log write failure
│   └── LogPruned.php                # After cleanup
│
├── Listeners/
│   └── ... (user-extensible)
│
├── Exceptions/
│   ├── LogWeaverException.php       # Base exception
│   ├── InvalidDriverException.php   # Unknown driver
│   ├── ValidationException.php      # Validation failure
│   └── DriverWriteException.php     # Write failure
│
├── Commands/
│   ├── PruneLogsCommand.php         # Log cleanup (schedulable)
│   ├── TailLogCommand.php           # Real-time log tail
│   ├── StatsCommand.php             # Log statistics
│   └── InstallCommand.php           # php artisan logweaver:install
│
├── Facades/
│   └── LogWeaver.php                # Facade (enhanced)
│
├── Query/
│   ├── LogQueryBuilder.php          # Fluent query API
│   └── Filters/
│       ├── LevelFilter.php
│       ├── DateRangeFilter.php
│       ├── ResourceFilter.php
│       └── ContentFilter.php
│
├── Formatters/
│   ├── JsonFormatter.php            # JSON output
│   ├── LineFormatter.php            # Single-line text
│   └── ArrayFormatter.php           # Array output
│
├── Support/
│   ├── LogBatch.php                 # Batch operations
│   ├── LogBuffer.php                # Buffer for batch write
│   └── RateLimiter.php              # Log throttling
│
└── Traits/
    ├── HasLogWeaver.php             # Model trait (loggable models)
    └── InteractsWithLogWeaver.php   # Controller/service trait
```

---

## Faz 1: Temel Altyapı & Contracts (Foundation)

> Tüm üst katmanların dayandığı sözleşmeler ve soyutlamalar.

### 1.1 Contract'lar (Interface'ler)

```php
// Contracts/LogDriverInterface.php
interface LogDriverInterface
{
    public function write(LogEntry $entry): bool;
    public function read(string $id): ?LogEntry;
    public function delete(string|array $ids): bool;
    public function query(): LogQueryInterface;
}
```

```php
// Contracts/FormatterInterface.php
interface FormatterInterface
{
    public function format(array $data): string;
    public function formatBatch(array $records): string;
}
```

```php
// Contracts/ContextResolverInterface.php
interface ContextResolverInterface
{
    public function resolve(): array;
    public function key(): string;
}
```

```php
// Contracts/LogQueryInterface.php
interface LogQueryInterface
{
    public function level(string|array $level): static;
    public function resource(string $resource): static;
    public function between(Carbon $from, Carbon $to): static;
    public function search(string $keyword): static;
    public function latest(): static;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function get(): Collection;
    public function count(): int;
}
```

**Neden:** Laravel'in kendisi her yerde contract kullanır (Queue, Cache, Mail). Loose coupling ve testability sağlar. Custom driver yazmak isteyen kullanıcılar interface'i implemente eder.

### 1.2 Exception Hiyerarşisi

```php
LogWeaverException (base)
├── InvalidDriverException
├── ValidationException
└── DriverWriteException
```

**Neden:** Laravel best practice - her paketin kendi exception sınıfları olmalı. `try/catch` ile paket hatalarını ayırt edebilmek kritik.

### 1.3 Value Objects & DTO'lar

```php
// LogEntry - Immutable value object
final class LogEntry implements Arrayable, Jsonable
{
    public function __construct(
        public readonly string $id,
        public readonly string $level,
        public readonly string $resource,
        public readonly string $description,
        public readonly array $content,
        public readonly array $context,
        public readonly ?array $relation,
        public readonly CarbonImmutable $createdAt,
    ) {}
}
```

**Neden:** Readonly properties (PHP 8.2+), immutability, type safety.

---

## Faz 2: Driver Sistemi

### 2.1 DatabaseDriver

```php
// Migration
Schema::create('log_weaver_entries', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('level', 20)->index();
    $table->string('resource', 50)->index();
    $table->string('description');
    $table->json('content');
    $table->json('context')->nullable();
    $table->json('relation')->nullable();
    $table->string('batch_id')->nullable()->index();
    $table->timestamps();

    $table->index(['level', 'created_at']);
    $table->index(['resource', 'created_at']);
});
```

**Eloquent Model:**
```php
class LogEntry extends Model
{
    use HasUlids;

    protected $table = 'log_weaver_entries';
    protected $casts = [
        'content'  => 'array',
        'context'  => 'array',
        'relation' => 'array',
    ];
}
```

**Neden:**
- ULID: Sıralı, unique, UUID'den daha performanslı (index-friendly)
- JSON kolonlar: Esnek yapı, PostgreSQL/MySQL native JSON desteği
- Composite indexler: Yaygın sorgu pattern'leri için optimize

### 2.2 FileDriver (Mevcut Refactoring)

Mevcut `log()` metodu bu driver'a taşınır. Tek sorumluluk prensibine uygun hale gelir.

### 2.3 StackDriver

Birden fazla driver'a aynı anda yazma:

```php
// config/logweaver.php
'channels' => [
    'production' => [
        'driver' => 'stack',
        'channels' => ['database', 'file'],
    ],
],
```

**Neden:** Laravel'in kendi Log sistemi de stack channel destekler. Kritik loglar hem DB'ye hem dosyaya yazılabilmeli.

### 2.4 NullDriver

Test ortamlarında log yazımını devre dışı bırakır.

### 2.5 LogManager (Channel Orchestrator)

```php
class LogManager
{
    public function channel(?string $name = null): LogDriverInterface;
    public function driver(?string $name = null): LogDriverInterface;
    public function extend(string $name, Closure $callback): static;
}
```

**Neden:** `Cache::store('redis')`, `Queue::connection('sqs')` pattern'i. Laravel'de standart.

---

## Faz 3: Context Sistemi

### 3.1 ContextManager

```php
// Otomatik context zenginleştirme
LogWeaver::description('Order placed')
    ->content(['order_id' => 123])
    ->level('info')
    ->log();

// Arka planda eklenen context:
// {
//   "request": { "url": "/orders", "method": "POST", "ip": "..." },
//   "user": { "id": 1, "email": "..." },
//   "app": { "environment": "production", "version": "2.1.0" },
//   "trace_id": "01HQ5..."
// }
```

### 3.2 Resolver'lar

| Resolver | Ne Ekler | Ne Zaman |
|----------|----------|----------|
| `RequestResolver` | URL, method, IP, user-agent, headers | HTTP request |
| `UserResolver` | User ID, email, roles | Auth varsa |
| `AppResolver` | Environment, version, hostname | Her zaman |
| `ExceptionResolver` | Class, message, trace, file, line | Exception varsa |

### 3.3 Custom Context

```php
// Kullanıcı kendi resolver'ını ekleyebilir
LogWeaver::resolveContextUsing('tenant', function () {
    return ['tenant_id' => tenant()->id, 'plan' => tenant()->plan];
});
```

**Neden:** 12-Factor App: Loglar zengin context taşımalı. Debugging ve monitoring için kritik.

---

## Faz 4: Middleware & Otomatik Loglama

### 4.1 HTTP Request Logger

```php
// Kernel.php veya bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(LogRequestMiddleware::class);
})
```

**Otomatik olarak loglar:**
- Request method, URL, headers
- Response status code, süre
- Authenticated user
- Request body (configurable, sensitive fields maskelenir)

### 4.2 Slow Request Detector

```php
// Config ile threshold belirlenir
'slow_request_threshold' => 1000, // ms

// Otomatik olarak uyarı log'u:
// "Slow request detected: GET /api/reports took 2340ms"
```

### 4.3 Sensitive Data Maskeleme

```php
'masking' => [
    'fields' => ['password', 'token', 'secret', 'credit_card'],
    'replacement' => '********',
    'headers' => ['Authorization', 'Cookie'],
],
```

**Neden:** GDPR/KVKK uyumluluk, güvenlik best practice.

---

## Faz 5: Query & Search API

### 5.1 Fluent Query Builder

```php
// Database driver ile
$logs = LogWeaver::query()
    ->level('error')
    ->resource('system')
    ->between(now()->subDays(7), now())
    ->search('payment')
    ->latest()
    ->paginate(25);

// Zincirleme filtreler
$criticalToday = LogWeaver::query()
    ->level(['error', 'critical'])
    ->today()
    ->count();
```

### 5.2 Aggregation & Stats

```php
$stats = LogWeaver::stats()
    ->between(now()->subMonth(), now())
    ->groupByLevel()
    ->get();

// Sonuç:
// ['info' => 1250, 'warning' => 89, 'error' => 23, 'critical' => 2]
```

**Neden:** Logları yazmak kadar okumak da önemli. Monitoring dashboard'lar bu API'yi kullanabilmeli.

---

## Faz 6: Artisan Komutları

### 6.1 Install Command

```bash
php artisan logweaver:install
```

- Config dosyasını publish eder
- Migration'ı publish eder
- .env'ye gerekli değişkenleri ekler
- Kullanıcıya setup wizard sunar

### 6.2 Prune Command (Schedulable)

```bash
# Manuel
php artisan logweaver:prune --days=30 --level=info

# Schedule
$schedule->command('logweaver:prune --days=90')->daily();
```

### 6.3 Tail Command

```bash
php artisan logweaver:tail --level=error
# Real-time log stream (database driver)
```

### 6.4 Stats Command

```bash
php artisan logweaver:stats --days=7
# ┌──────────┬───────┐
# │ Level    │ Count │
# ├──────────┼───────┤
# │ info     │ 1,250 │
# │ warning  │    89 │
# │ error    │    23 │
# │ critical │     2 │
# └──────────┴───────┘
```

**Neden:** Laravel ekosisteminde artisan komutları standart. DevX için kritik.

---

## Faz 7: Event Sistemi

### 7.1 Event'ler

```php
// Log yazıldıktan sonra
event(new LogCreated($entry));

// Log yazma başarısız olursa
event(new LogFailed($entry, $exception));

// Prune işlemi sonrası
event(new LogPruned($count, $criteria));
```

### 7.2 Kullanım Senaryoları

```php
// Listener: Kritik logları Slack'e gönder
class SendCriticalLogToSlack
{
    public function handle(LogCreated $event): void
    {
        if ($event->entry->level === 'critical') {
            Notification::route('slack', config('...'))
                ->notify(new CriticalLogNotification($event->entry));
        }
    }
}
```

**Neden:** Laravel'in event-driven mimarisi. Paket extensible olmalı, hook point'ler sunmalı.

---

## Faz 8: Model Trait & Helpers

### 8.1 HasLogWeaver Trait

```php
class Order extends Model
{
    use HasLogWeaver;
}

// Kullanım
$order->logWeave('Order shipped', 'info', ['tracking' => 'XX123']);

// Model'e ait tüm loglar
$order->logWeaverEntries()->latest()->get();
```

### 8.2 Global Helper (Opsiyonel)

```php
// Kolay erişim
log_weave('User logged in', 'info', ['user_id' => 1]);
```

**Neden:** Eloquent model'lerle entegrasyon - Laravel'in relationship sistemiyle uyumlu.

---

## Faz 9: Batch & Performance

### 9.1 Batch Logging

```php
LogWeaver::batch(function ($batch) {
    $batch->add('Item processed', 'info', ['item_id' => 1]);
    $batch->add('Item processed', 'info', ['item_id' => 2]);
    $batch->add('Item processed', 'info', ['item_id' => 3]);
}); // Tek DB insert ile 3 log

// Veya
LogWeaver::buffer(); // Buffering başlat
// ... birçok log yazımı ...
LogWeaver::flush();  // Hepsini tek seferde yaz
```

### 9.2 Queue Desteği

```php
// Config: async logları queue'ya gönder
'queue' => [
    'enabled' => true,
    'connection' => 'redis',
    'queue' => 'logs',
],
```

**Neden:** Mevcut sleep-polling yerine Laravel Queue sistemi. Production-grade async.

### 9.3 Rate Limiting

```php
'rate_limit' => [
    'enabled' => true,
    'max_per_minute' => 1000,
    'per_key' => true, // level+resource bazında
],
```

---

## Faz 10: Gelişmiş Config

### Hedef config/logweaver.php

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    */
    'default' => env('LOG_WEAVER_CHANNEL', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'database' => [
            'driver' => 'database',
            'connection' => env('LOG_WEAVER_DB_CONNECTION', null), // default DB
            'table' => 'log_weaver_entries',
        ],

        'file' => [
            'driver' => 'file',
            'disk' => env('LOG_WEAVER_DISK', 'local'),
            'directory' => env('LOG_WEAVER_DIR', 'logweaver'),
        ],

        'stack' => [
            'driver' => 'stack',
            'channels' => ['database', 'file'],
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Resolvers
    |--------------------------------------------------------------------------
    */
    'context' => [
        'enabled' => true,
        'resolvers' => [
            'request' => \HalilCosdu\LogWeaver\Context\Resolvers\RequestResolver::class,
            'user'    => \HalilCosdu\LogWeaver\Context\Resolvers\UserResolver::class,
            'app'     => \HalilCosdu\LogWeaver\Context\Resolvers\AppResolver::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Levels
    |--------------------------------------------------------------------------
    */
    'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'enabled' => true,
        'strict' => false, // Strict mode: bilinmeyen alanları reject et
    ],

    /*
    |--------------------------------------------------------------------------
    | Masking - Sensitive Data Protection
    |--------------------------------------------------------------------------
    */
    'masking' => [
        'enabled' => true,
        'fields' => ['password', 'password_confirmation', 'token', 'secret', 'credit_card', 'cvv'],
        'replacement' => '********',
        'headers' => ['Authorization', 'Cookie', 'X-CSRF-TOKEN'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue (Async Logging)
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => false,
        'connection' => env('LOG_WEAVER_QUEUE_CONNECTION', null),
        'queue' => env('LOG_WEAVER_QUEUE', 'logweaver'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruning (Log Retention)
    |--------------------------------------------------------------------------
    */
    'pruning' => [
        'enabled' => true,
        'keep_days' => 90,
        'keep_levels' => ['critical', 'emergency'], // Bunlar silinmez
        'chunk_size' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => false,
        'max_per_minute' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'log_requests' => false,
        'slow_threshold' => 1000, // ms
        'except' => ['_debugbar/*', 'telescope/*', 'horizon/*'],
    ],
];
```

---

## Faz Sıralaması & Bağımlılıklar

```
Faz 1: Contracts & Foundation ──────┐
                                     │
Faz 2: Driver Sistemi ◄─────────────┘
         │
         ├── Faz 3: Context Sistemi
         │
         ├── Faz 5: Query API
         │
         └── Faz 9: Batch & Performance
                │
Faz 4: Middleware ◄──── Faz 3
                │
Faz 6: Artisan Commands ◄──── Faz 2, Faz 5
                │
Faz 7: Event Sistemi ◄──── Faz 2
                │
Faz 8: Model Trait ◄──── Faz 2, Faz 5
                │
Faz 10: Config ──── (Tüm fazlarla paralel ilerler)
```

### Uygulama Sırası

| Sıra | Faz | Bağımlılık | Açıklama |
|------|-----|------------|----------|
| 1 | Faz 1 | - | Contracts, Exceptions, Value Objects |
| 2 | Faz 10 | - | Config yapısı (diğer fazlar bunu kullanır) |
| 3 | Faz 2 | Faz 1, 10 | Driver'lar ve LogManager |
| 4 | Faz 3 | Faz 1 | Context resolver'lar |
| 5 | Faz 7 | Faz 2 | Event sistemi |
| 6 | Faz 5 | Faz 2 | Query builder |
| 7 | Faz 9 | Faz 2 | Batch, queue, rate limiting |
| 8 | Faz 4 | Faz 2, 3 | Middleware |
| 9 | Faz 8 | Faz 2, 5 | Model trait |
| 10 | Faz 6 | Faz 2, 5 | Artisan komutları |

---

## Laravel Best Practice Checklist

### Kod Kalitesi
- [ ] PHP 8.2+ typed properties, union types, readonly
- [ ] Constructor promotion
- [ ] Enum'lar (LogLevel, LogResource) - string-backed
- [ ] Named arguments desteği
- [ ] PHPStan Level 8+ (mevcut 5'ten yükseltme)
- [ ] Laravel Pint (PSR-12) strict mode

### Mimari
- [ ] Service Provider pattern (proper binding, deferred loading)
- [ ] Contract-based design (interface'e bağımlılık, implementasyona değil)
- [ ] Manager pattern (Laravel'in Cache, Queue, Mail pattern'i)
- [ ] Repository pattern yerine Driver pattern (log-specific)
- [ ] Dependency Injection everywhere (new keyword kullanımı minimize)

### Testing
- [ ] Pest PHP (mevcut, genişletilecek)
- [ ] Feature + Unit test ayrımı
- [ ] Fake/Mock desteği: `LogWeaver::fake()` (a la `Queue::fake()`)
- [ ] Architecture tests (`->expect('HalilCosdu\LogWeaver')->toUseStrictTypes()`)
- [ ] %90+ code coverage hedefi
- [ ] Integration test'ler (SQLite in-memory)

### Güvenlik
- [ ] Sensitive data masking (KVKK/GDPR)
- [ ] SQL injection koruması (Eloquent/Query Builder)
- [ ] Mass assignment koruması ($fillable)
- [ ] Input validation (FormRequest pattern)
- [ ] Rate limiting

### Performance
- [ ] Lazy collection'lar (büyük veri setleri)
- [ ] Chunk processing (prune, batch)
- [ ] Queue desteği (async logging)
- [ ] Database indexleme stratejisi
- [ ] Buffer/batch write

### Developer Experience
- [ ] Artisan install komutu
- [ ] Otomatik facade completion (IDE helper)
- [ ] `LogWeaver::fake()` test helper
- [ ] Kapsamlı config dosyası
- [ ] Migration publish
- [ ] PHP 8.2 Enum'lar (autocomplete-friendly)

### Paket Standartları
- [ ] Spatie package-tools (mevcut)
- [ ] Publishable config, migrations, stubs
- [ ] Tag-based publishing (`--tag=logweaver-config`, `--tag=logweaver-migrations`)
- [ ] Deferred service provider (performance)
- [ ] Laravel 10.x & 11.x uyumluluk
- [ ] Testbench (mevcut)

---

## API Tasarımı (Kullanım Örnekleri)

### Basit Kullanım (Easy)

```php
// Tek satırda log
LogWeaver::info('User logged in', ['user_id' => 1]);

// Shorthand metodlar
LogWeaver::error('Payment failed', ['order_id' => 123]);
LogWeaver::warning('Low stock', ['product_id' => 456, 'stock' => 2]);
LogWeaver::critical('Database unreachable');
```

### Builder Pattern (Powerful)

```php
LogWeaver::description('Order lifecycle event')
    ->resource('event')
    ->level('info')
    ->content(['order_id' => 123, 'status' => 'shipped'])
    ->relation(['order_id' => 123, 'user_id' => 1])
    ->channel('database')
    ->log();
```

### Query (Search & Read)

```php
// Son 24 saatteki hatalar
$errors = LogWeaver::query()
    ->level('error')
    ->since(now()->subDay())
    ->latest()
    ->get();

// Pagination ile
$logs = LogWeaver::query()
    ->resource('event')
    ->search('payment')
    ->paginate(20);
```

### Model Entegrasyonu

```php
class Order extends Model
{
    use HasLogWeaver;
}

$order->logWeave('Status changed to shipped', 'info');
$order->logWeaverEntries; // İlişkili tüm loglar
```

### Testing

```php
LogWeaver::fake();

// ... test kodu ...

LogWeaver::assertLogged('info', function ($entry) {
    return str_contains($entry->description, 'User logged in');
});

LogWeaver::assertLoggedCount(3);
LogWeaver::assertNothingLogged();
```

### Batch

```php
LogWeaver::batch(function ($batch) {
    foreach ($items as $item) {
        $batch->info("Processed item #{$item->id}", ['item' => $item->toArray()]);
    }
}); // Tek transaction
```

---

## Breaking Changes & Migration Stratejisi

### v1 -> v2 Migration

| v1 API | v2 API | Not |
|--------|--------|-----|
| `LogWeaver::logResource('event')` | `LogWeaver::resource('event')` | Kısaltma |
| `->log($path, $wait)` | `->log()` veya `->toChannel('file')->log()` | Driver-based |
| `->disk('s3')` | Channel config'de | Config-driven |
| `->directory('logs')` | Channel config'de | Config-driven |
| `->validate(false)` | `->withoutValidation()` | Daha okunabilir |
| `->toArray()` / `->toJson()` | Korunuyor | Geriye uyumlu |
| `->get($path)` | `LogWeaver::query()->find($id)` | Query API |
| `->delete($paths)` | `LogWeaver::query()->delete($ids)` | Query API |
| `->download($path)` | `LogWeaver::download($id)` | Sadeleştirildi |

### v1 Uyumluluk

- `logResource()` deprecated alias olarak kalır 1 major versiyon
- `disk()` ve `directory()` file driver'a özel olarak kalır
- `validate()` -> `withoutValidation()` alias eklenir

---

## Dosya Listesi (Oluşturulacak)

### Yeni Dosyalar
1. `src/Contracts/LogDriverInterface.php`
2. `src/Contracts/FormatterInterface.php`
3. `src/Contracts/ContextResolverInterface.php`
4. `src/Contracts/LogQueryInterface.php`
5. `src/LogManager.php`
6. `src/Drivers/DatabaseDriver.php`
7. `src/Drivers/FileDriver.php`
8. `src/Drivers/StackDriver.php`
9. `src/Drivers/NullDriver.php`
10. `src/Models/LogEntry.php`
11. `src/Context/ContextManager.php`
12. `src/Context/ContextBag.php`
13. `src/Context/Resolvers/RequestResolver.php`
14. `src/Context/Resolvers/UserResolver.php`
15. `src/Context/Resolvers/AppResolver.php`
16. `src/Context/Resolvers/ExceptionResolver.php`
17. `src/Middleware/LogRequestMiddleware.php`
18. `src/Middleware/LogSlowRequestMiddleware.php`
19. `src/Events/LogCreated.php`
20. `src/Events/LogFailed.php`
21. `src/Events/LogPruned.php`
22. `src/Exceptions/LogWeaverException.php`
23. `src/Exceptions/InvalidDriverException.php`
24. `src/Exceptions/ValidationException.php`
25. `src/Exceptions/DriverWriteException.php`
26. `src/Commands/InstallCommand.php`
27. `src/Commands/PruneLogsCommand.php`
28. `src/Commands/TailLogCommand.php`
29. `src/Commands/StatsCommand.php`
30. `src/Query/LogQueryBuilder.php`
31. `src/Query/Filters/LevelFilter.php`
32. `src/Query/Filters/DateRangeFilter.php`
33. `src/Query/Filters/ResourceFilter.php`
34. `src/Query/Filters/ContentFilter.php`
35. `src/Formatters/JsonFormatter.php`
36. `src/Formatters/LineFormatter.php`
37. `src/Formatters/ArrayFormatter.php`
38. `src/Support/LogBatch.php`
39. `src/Support/LogBuffer.php`
40. `src/Support/RateLimiter.php`
41. `src/Traits/HasLogWeaver.php`
42. `src/Traits/InteractsWithLogWeaver.php`
43. `src/Enums/LogLevel.php`
44. `src/Enums/LogResource.php`
45. `database/migrations/create_log_weaver_entries_table.php`

### Değiştirilecek Dosyalar
1. `src/LogWeaver.php` - Tamamen refactor
2. `src/LogWeaverServiceProvider.php` - Genişletme
3. `src/Facades/LogWeaver.php` - Yeni metodlar
4. `config/logweaver.php` - Tam yeniden yazım
5. `composer.json` - Yeni bağımlılıklar
6. `phpstan.neon.dist` - Level yükseltme

### Yeni Test Dosyaları
1. `tests/Unit/LogEntryTest.php`
2. `tests/Unit/LogManagerTest.php`
3. `tests/Unit/Drivers/DatabaseDriverTest.php`
4. `tests/Unit/Drivers/FileDriverTest.php`
5. `tests/Unit/Drivers/StackDriverTest.php`
6. `tests/Unit/Drivers/NullDriverTest.php`
7. `tests/Unit/Context/ContextManagerTest.php`
8. `tests/Unit/Query/LogQueryBuilderTest.php`
9. `tests/Unit/Formatters/JsonFormatterTest.php`
10. `tests/Unit/Support/LogBatchTest.php`
11. `tests/Feature/LogWeaverIntegrationTest.php`
12. `tests/Feature/MiddlewareTest.php`
13. `tests/Feature/Commands/InstallCommandTest.php`
14. `tests/Feature/Commands/PruneCommandTest.php`
15. `tests/Feature/FakeLogWeaverTest.php`
16. `tests/Arch/ArchTest.php`
