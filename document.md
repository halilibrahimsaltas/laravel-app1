# Finansal Veri Analiz Platformu - Teknik Dokümantasyon

## 📌 Proje Yapısı

### Backend (Laravel)

#### Servisler

-   `FinancialDataService`: Finansal veri işleme
-   `AlphaVantageService`: Döviz kuru API entegrasyonu
-   `GoldApiService`: Altın fiyatları API entegrasyonu
-   `AnalysisService`: Veri analizi
-   `DataCleanerService`: Veri temizleme

#### Kontrolcüler

-   `FinanceController`: Finansal veri endpoint'leri
-   `ChartController`: Grafik verisi endpoint'leri
-   `CryptoController`: Kripto para endpoint'leri

#### API Endpoint'leri

```
GET /api/finance/gold-price
GET /api/finance/exchange-rate
GET /api/v1/analysis/daily-average/{currencyPair}
GET /api/v1/analysis/anomalies/{currencyPair}
GET /api/v1/analysis/trend/{currencyPair}
```

### Frontend (React)

#### Servisler

-   `FinanceService`: Backend API entegrasyonu

#### Bileşenler

-   `FinanceOverview`: Finansal genel bakış
-   `GoldPrices`: Altın fiyatları gösterimi
-   `ExchangeRates`: Döviz kurları gösterimi
-   `TrendAnalysis`: Trend analizi grafiği

## 📌 Veri Modeli

### financial_data Tablosu

```sql
CREATE TABLE financial_data (
    id BIGINT PRIMARY KEY,
    type VARCHAR(255),           -- 'forex', 'gold'
    from_code VARCHAR(10),       -- 'USD', 'XAU'
    to_code VARCHAR(10),         -- 'TRY', 'USD'
    rate DECIMAL(20,8),
    timestamp TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 📌 Veri Akışı

1. API'lerden veri toplama (5 dakika aralıkla)
2. Veri temizleme ve doğrulama
3. Veritabanına kaydetme
4. Redis önbellekleme
5. Frontend'e veri gönderme

## 📌 Önbellekleme Stratejisi

-   Döviz kurları: 5 dakika
-   Altın fiyatları: 5 dakika
-   Analiz sonuçları: 30 dakika

## 📌 Hata Yönetimi

-   API bağlantı hataları
-   Veri doğrulama hataları
-   Rate limiting
-   Fallback mekanizmaları

## 📌 Güvenlik

-   API anahtarı yönetimi
-   CORS yapılandırması
-   Rate limiting
-   Veri doğrulama

## 📌 Monitoring

-   API yanıt süreleri
-   Veri güncelleme durumu
-   Sistem sağlığı
-   Hata logları
