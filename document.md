Laravel-App - Proje Dokümantasyonu

📌 Proje Adı

Laravel-App

📌 Proje Amacı

Bu proje, farklı finansal kaynaklardan (döviz, altın, kripto para) veri toplayarak analiz eden, temizleyen ve görselleştiren bir finansal veri işleme platformudur. Kullanıcılar, çeşitli API'lerden verileri alabilir, sistem bu verileri işleyip anlamlı hale getirebilir ve sonuçları grafiksel olarak görüntüleyebilir.

📌 Kullanıcı Senaryosu

Kullanıcı, belirli bir veri kaynağını sisteme ekler (örneğin, haber siteleri, sosyal medya API'leri).

Sistem, belirlenen zamanlarda bu verileri çeker ve temizler.

Kullanıcı, analiz edilen verileri tablo ve grafiklerle görüntüleyebilir.

Sistem, verilerin performansını artırmak için önbellekleme (caching) kullanır.

📌 Kullanılacak Teknolojiler

📌 Backend (Laravel - API Geliştirme & Veri İşleme)

Laravel - Backend geliştirme için ana framework

Laravel Queues - Büyük veri işlemlerini arka planda yürütmek için

Laravel Scheduler - Otomatik veri çekme işlemlerini zamanlamak için

GuzzleHTTP - API'lerden veri çekmek için

Redis - Önbellekleme ve performans artırma için

PostgreSQL - JSONB desteği ile veri depolamak için

📌 Frontend (Veri Görselleştirme ve Kullanıcı Arayüzü)

React + Vite - Hızlı frontend geliştirme için

Chart.js veya D3.js - Grafiksel veri gösterimi için

Axios - API çağrıları için

Tailwind CSS / Material UI - UI tasarımı için

📌 Ekstra Araçlar

Docker - Geliştirme ortamı ve veri servislerini yönetmek için

Postman - API testleri için

Python - Gelişmiş veri analizleri ve makine öğrenmesi için

📌 Proje Aşamaları & Yol Haritası

📅 Hafta 1: Proje Kurulumu ve Temel Yapının Oluşturulması

Laravel ve PostgreSQL bağlantısının kurulması

Kullanıcı yönetimi (auth, login, logout)

React + Vite kurulumu ve temel sayfa yapıları

📅 Hafta 2: Veri Kaynaklarından Veri Toplama

Guzzle ile farklı API'lerden veri çekme (örneğin: Twitter API, haber API'leri)

Kullanıcılar API üzerinden veri kaynağı ekleyebilmeli

Laravel Scheduler kullanarak belirli zamanlarda veri çekme otomasyonu

📅 Hafta 3: Veri Depolama ve İşleme

PostgreSQL JSONB formatında veri saklama

Verileri temizleme, filtreleme ve ön işleme

Redis ile önbellekleme

Laravel Queue ile büyük veri işlemlerini arka planda yürütme

📅 Hafta 4: Veri Analizi ve Görselleştirme

Laravel üzerinde temel veri analiz işlemleri (trend analizi, en sık geçen kelimeler vb.)

React içinde Chart.js veya D3.js kullanarak grafiksel gösterimler

Python veya PHP ile istatistiksel analizler ekleme

📅 Hafta 5: Gelişmiş Özellikler & Optimizasyon

Elasticsearch entegrasyonu (hızlı veri arama)

Büyük veri desteği için Kafka veya RabbitMQ kullanımı

API güvenliği için JWT authentication ekleme

Performans testleri

📅 Hafta 6: Testler ve Yayına Alma

API endpoint testleri (Postman veya PHPUnit)

React tarafında component testleri

Kullanıcı arayüzü ve mobil uyumluluk testleri

Docker ile deployment ortamı oluşturma

Sunucuya deploy etme (AWS / DigitalOcean / Linode vb.)

📌 Veri İşleme Pipeline'ı ve Optimizasyonu

🔄 Veri Toplama Katmanı

-   API Entegrasyonları (AlphaVantage, CoinGecko vb.)
-   Webhook Desteği
-   Batch İşlem Desteği
-   Hata Yönetimi ve Retry Mekanizması

🔄 Veri İşleme Katmanı

-   Veri Doğrulama ve Temizleme
-   Format Standardizasyonu
-   Veri Zenginleştirme
-   Duplikasyon Kontrolü

🔄 Veri Depolama Optimizasyonu

-   Veritabanı İndeksleme Stratejisi
-   Partitioning ve Sharding
-   Arşivleme Mekanizması
-   Önbellekleme Stratejisi

🔄 Veri Erişim Katmanı

-   API Rate Limiting
-   Pagination
-   Filtreleme ve Sıralama
-   Önbellek Yönetimi

📊 Veri Modeli Optimizasyonu

1. Veritabanı Şeması

    - Normalize edilmiş tablolar
    - İlişkisel bağlantılar
    - İndeks stratejisi
    - Partition planlaması

2. Performans İyileştirmeleri

    - Query optimizasyonu
    - Composite indeksler
    - Lazy loading vs. Eager loading
    - N+1 sorgu problemi çözümleri

3. Ölçeklenebilirlik

    - Horizontal scaling desteği
    - Read/Write splitting
    - Sharding stratejisi
    - Load balancing

4. Veri Güvenliği

    - Encryption at rest
    - Encryption in transit
    - Audit logging
    - Access control

5. Monitoring ve Logging
    - Performans metrikleri
    - Error tracking
    - Query logging
    - System health monitoring

📌 Sonuç

Bu proje sayesinde veri toplama, işleme, analiz etme ve görselleştirme süreçlerini eksiksiz şekilde tamamlamış olacağız. Geliştirme adımlarını takip ederek Big-Data-Analyzer ile güçlü bir iş zekâsı platformu oluşturacağız. 🚀

📊 Finansal Veri Analiz Sistemi

1. Analiz Bileşenleri

🔄 Temel Analizler

-   Günlük ortalama hesaplama (5 dakika önbellekli)
-   Anomali tespiti (eşik değeri: %3)
-   Trend analizi (7 günlük varsayılan periyot)
-   Volatilite hesaplama

🔄 Veri Modelleri

-   FinancialData: Ham finansal veriler
-   AnalysisResult: Analiz sonuçları ve metrikler
-   DataSource: Veri kaynakları

🔄 Servisler

-   AnalysisService: Temel analiz işlemleri
-   DataCleanerService: Veri temizleme ve doğrulama

2. Analiz Sonuçları Yapısı

```sql
CREATE TABLE analysis_results (
    id BIGINT PRIMARY KEY,
    currency_pair VARCHAR(255),
    daily_avg DECIMAL(12,4),
    anomaly_detected BOOLEAN,
    deviation DECIMAL(8,4),
    trend_direction VARCHAR(255),
    volatility DECIMAL(8,4),
    additional_metrics JSON,
    calculation_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

3. Job Sistemi

🔄 İşlem Adımları

1. Veri toplama (ProcessFinancialDataJob)
2. Veri temizleme (DataCleanerService)
3. Analiz işlemleri (AnalysisService)
4. Sonuçları kaydetme (AnalysisResult)
5. Meta veri güncelleme (FinancialData)

6. Sistemi Çalıştırma ve Test

```bash
# Queue Worker'ı Başlat
php artisan queue:work

# Supervisor Yapılandırması (Production)
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

🔄 Test Senaryoları

1. Temel Analiz Testi

```php
# Tinker üzerinden test
php artisan tinker

# Veri kaynağından analiz başlat
$source = App\Models\DataSource::first();
dispatch(new App\Jobs\ProcessFinancialDataJob($source));

# Analiz sonuçlarını kontrol et
$analysis = new App\Services\AnalysisService();
$dailyAvg = $analysis->calculateDailyAverage('USD/TRY');
$anomalies = $analysis->detectAnomalies('USD/TRY');
```

2. Sonuçları Sorgulama

```php
# Son analiz sonucu
$latest = AnalysisResult::latestForPair('USD/TRY');

# Anomali tespiti
$anomalies = AnalysisResult::withAnomalies()->get();

# Trend analizi
$bullishTrends = AnalysisResult::withTrend('bullish')->get();

# Yüksek volatilite
$volatile = AnalysisResult::highVolatility(0.03)->get();
```

5. Örnek Analiz Sonucu

```json
{
    "currency_pair": "USD/TRY",
    "daily_avg": 30.4567,
    "anomaly_detected": true,
    "deviation": 0.0456,
    "trend_direction": "bullish",
    "volatility": 0.0234,
    "additional_metrics": {
        "anomaly_details": {
            "has_anomaly": true,
            "threshold": 0.03,
            "message": "Anormal değişim tespit edildi: %4.56"
        },
        "trend_details": {
            "trend": "bullish",
            "change_percent": 2.45,
            "period": "7 gün"
        },
        "data_points": 145
    }
}
```

6. Performans Optimizasyonları

🔄 Veritabanı

-   İndeksler (currency_pair, calculation_date)
-   Composite indeksler (anomaly + date, trend + date)
-   JSON sütunu için metrikler

🔄 Önbellekleme

-   Redis ile günlük ortalama (5 dakika TTL)
-   Batch işlemler için kuyruk sistemi
-   Analiz sonuçları için lazy loading

7. Monitoring ve Logging

🔄 Log Seviyeleri

-   INFO: Rutin analiz sonuçları
-   WARNING: Anomali tespitleri
-   ERROR: İşlem hataları

🔄 Metrikler

-   Günlük analiz sayısı
-   Anomali oranları
-   İşlem süreleri
-   Başarı/hata oranları

📊 Finansal Veri API Entegrasyonları

1. Döviz Kuru API (AlphaVantage)

🔄 Özellikler

-   Gerçek zamanlı döviz kurları
-   Desteklenen para birimleri: USD, EUR, TRY, GBP vb.
-   Bid/Ask fiyatları
-   Zaman damgası ve saat dilimi bilgisi

Örnek Yanıt:

```json
{
    "status": "success",
    "data": {
        "from": {
            "code": "USD",
            "name": "United States Dollar"
        },
        "to": {
            "code": "TRY",
            "name": "Turkish Lira"
        },
        "rate": 31.2345,
        "last_updated": "2024-02-19T20:10:24+00:00",
        "timezone": "UTC",
        "bid_price": 31.23,
        "ask_price": 31.239
    }
}
```

2. Altın Fiyatları API (GoldAPI.io)

🔄 Özellikler

-   Gerçek zamanlı altın fiyatları
-   Troy ons ve gram cinsinden fiyatlar
-   Desteklenen para birimleri: USD, EUR, TRY vb.
-   5 dakikalık önbellekleme
-   Yüksek doğruluk oranı

Örnek Yanıt:

```json
{
    "status": "success",
    "data": {
        "troy_ounce": {
            "price": 2931.88,
            "unit": "troy ounce"
        },
        "gram": {
            "price": 94.26,
            "unit": "gram"
        },
        "from_currency": "GOLD",
        "to_currency": "USD",
        "last_updated": "2024-02-19T20:10:24+00:00",
        "source": "goldapi"
    }
}
```

3. Veri Analiz Özellikleri

🔄 Döviz Kuru Analizleri

-   Anlık kur takibi
-   Alış/Satış fiyat farkı analizi
-   Para birimi çiftleri arası çapraz kur hesaplama
-   Tarihsel kur değişimi analizi

🔄 Altın Fiyat Analizleri

-   Troy ons/gram dönüşümleri
-   Farklı para birimlerinde altın değeri
-   Altın/Döviz korelasyon analizi
-   Fiyat trend analizi

4. Önbellekleme Stratejisi

🔄 Döviz Kurları

-   AlphaVantage API limitlerine uygun çağrı yönetimi
-   Yüksek trafikli kurlar için özel önbellekleme
-   API yanıt süreleri optimizasyonu

🔄 Altın Fiyatları

-   5 dakikalık önbellekleme süresi
-   Otomatik önbellek yenileme
-   Yedek veri kaynağı desteği

5. Hata Yönetimi

🔄 API Hataları

-   Bağlantı kopması durumunda yeniden deneme
-   API limit aşımı kontrolü
-   Veri tutarsızlığı kontrolü

🔄 Veri Doğrulama

-   Fiyat aralığı kontrolleri
-   Para birimi geçerlilik kontrolleri
-   Tarih/saat formatı doğrulaması

📌 Veri Kaynakları

1. AlphaVantage API (Döviz Kurları)

    - Gerçek zamanlı döviz kurları
    - Bid/Ask fiyatları
    - Desteklenen para birimleri: USD, EUR, TRY, GBP vb.

2. GoldAPI.io (Altın Fiyatları)
    - Gerçek zamanlı altın fiyatları (XAU)
    - Troy ons ve gram cinsinden fiyatlar
    - Farklı para birimlerinde değerler

📌 Veritabanı Yapısı

1. financial_data Tablosu

```sql
CREATE TABLE financial_data (
    id BIGINT PRIMARY KEY,
    data_source_id BIGINT NULLABLE,
    type VARCHAR(255),           -- 'forex', 'gold'
    from_code VARCHAR(10),       -- 'USD', 'XAU'
    to_code VARCHAR(10),         -- 'TRY', 'USD'
    rate DECIMAL(20,8),          -- Döviz kuru veya altın fiyatı
    bid_price DECIMAL(20,8),     -- Alış fiyatı
    ask_price DECIMAL(20,8),     -- Satış fiyatı
    data JSONB,                  -- Ham veri
    params JSONB,                -- İstek parametreleri
    status VARCHAR(50),          -- 'success', 'error'
    error_message TEXT,          -- Hata mesajı
    timestamp TIMESTAMP,         -- Veri zamanı
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

📌 Veri İşleme Pipeline'ı

1. Veri Toplama (ProcessFinancialDataJob)

    - Döviz Kurları (AlphaVantage)
        ```json
        {
            "from": { "code": "USD", "name": "United States Dollar" },
            "to": { "code": "TRY", "name": "Turkish Lira" },
            "rate": 31.2345,
            "last_updated": "2024-02-20T12:10:24+00:00",
            "timezone": "UTC",
            "bid_price": 31.23,
            "ask_price": 31.24
        }
        ```
    - Altın Fiyatları (GoldAPI)
        ```json
        {
            "from": { "code": "XAU", "name": "Gold" },
            "to": { "code": "USD", "name": "United States Dollar" },
            "rate": 2948.8,
            "last_updated": "2024-02-20T12:10:24+00:00",
            "timezone": "UTC",
            "bid_price": 2948.8,
            "ask_price": 2948.8
        }
        ```

2. Veri Temizleme (DataCleanerService)

    - Sayısal değer doğrulama
    - Para birimi kodu kontrolü
    - Tarih formatı standardizasyonu
    - Eksik alan kontrolü

3. Veri Kaydetme
    - Standart format kullanımı
    - İlişkisel veri yapısı
    - JSON veri desteği

📌 Zamanlanmış Görevler

1. Döviz Kurları

    - USD/TRY: Her 5 dakikada
    - EUR/TRY: Her 5 dakikada
    - Diğer kurlar: Saatlik

2. Altın Fiyatları
    - XAU/USD: Her 5 dakikada
    - XAU/TRY: Her 5 dakikada

📌 Hata Yönetimi

1. API Hataları

    - Bağlantı kopması: Otomatik yeniden deneme
    - API limit aşımı: Bekleme süresi
    - Veri doğrulama: Hata kaydı

2. Veri İşleme Hataları
    - Eksik veri kontrolü
    - Format dönüşüm hataları
    - Veritabanı kayıt hataları

📌 Önbellekleme Stratejisi

1. API Yanıtları

    - Döviz kurları: 5 dakika
    - Altın fiyatları: 5 dakika

2. İşlenmiş Veriler
    - Günlük ortalamalar: 1 saat
    - Trend analizleri: 30 dakika

📌 Kullanım Örnekleri

1. Döviz Kuru Çekme

```bash
php artisan financial:fetch forex --debug
```

2. Altın Fiyatı Çekme

```bash
php artisan financial:fetch gold --debug
```

📌 Veri Analizi

1. Temel Analizler

    - Günlük ortalama hesaplama
    - Volatilite analizi
    - Trend tespiti

2. İleri Analizler
    - Anomali tespiti
    - Korelasyon analizi
    - Tahmin modelleri

📌 Güvenlik Önlemleri

1. API Güvenliği

    - API anahtarları güvenli depolama
    - Rate limiting
    - IP kısıtlamaları

2. Veri Güvenliği
    - Hassas veri şifreleme
    - Yetkilendirme kontrolleri
    - Audit logging

📌 Monitoring ve Logging

1. Performans Metrikleri

    - API yanıt süreleri
    - İşlem süreleri
    - Başarı/hata oranları

2. Log Seviyeleri
    - INFO: Rutin işlemler
    - WARNING: Olası sorunlar
    - ERROR: Kritik hatalar

📌 Geliştirme Ortamı

1. Gereksinimler

    - PHP 8.1+
    - Laravel 10.x
    - PostgreSQL 13+
    - Redis 6+

2. Kurulum

```bash
composer install
php artisan migrate
php artisan queue:work
```

📌 Deployment

1. Sunucu Gereksinimleri

    - 2 CPU
    - 4GB RAM
    - 50GB SSD

2. Servisler
    - Nginx/Apache
    - PHP-FPM
    - PostgreSQL
    - Redis
    - Supervisor

📌 Bakım ve İzleme

1. Düzenli Bakım

    - Günlük veri temizliği
    - Log rotasyonu
    - Performans optimizasyonu

2. İzleme
    - Sistem sağlığı
    - API durumu
    - Veri tutarlılığı
