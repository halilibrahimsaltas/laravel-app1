# Finansal Veri Analiz Platformu

Bu proje, finansal verileri (döviz, altın) analiz eden ve görselleştiren bir web uygulamasıdır.

## 🚀 Özellikler

-   Gerçek zamanlı döviz kurları takibi
-   Altın fiyatları analizi
-   Finansal veri görselleştirme
-   Trend analizi ve anomali tespiti
-   Otomatik veri güncelleme (5 dakika)

## 🛠️ Teknolojiler

### Backend

-   Laravel 10.x
-   PostgreSQL
-   Redis (Önbellekleme)
-   Docker

### Frontend

-   React + Vite
-   Material UI
-   Chart.js
-   Axios

## 📦 Kurulum

### Gereksinimler

-   PHP 8.1+
-   Composer
-   Node.js & npm
-   Docker & Docker Compose

### Adımlar

1. Projeyi klonlayın:

```bash
git clone <proje-url>
cd laravel-app
```

2. Backend kurulumu:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

3. Frontend kurulumu:

```bash
cd frontend
npm install
```

4. Docker ile çalıştırma:

```bash
docker-compose up -d
```

5. Veritabanı migration:

```bash
php artisan migrate
```

## 🚀 Çalıştırma

1. Backend API'yi başlatın:

```bash
php artisan serve
```

2. Frontend geliştirme sunucusunu başlatın:

```bash
cd frontend
npm run dev
```

3. Tarayıcıda açın:

```
http://localhost:5173
```

## 📊 API Servisleri

-   `/api/finance/gold-price`: Altın fiyatları
-   `/api/finance/exchange-rate`: Döviz kurları
-   `/api/v1/analysis/*`: Analiz endpointleri

## 🔄 Veri Kaynakları

-   AlphaVantage API (Döviz kurları)
-   GoldAPI.io (Altın fiyatları)

## 📝 Notlar

-   API anahtarları `.env` dosyasında saklanmalıdır
-   Veri güncelleme sıklığı 5 dakikadır
-   Redis önbellek süresi 5 dakikadır

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.
