# KH Downloader 🚀

**KH Downloader** is a high-performance web application designed for fast video downloading and free online streaming from popular Cambodian platforms. Built with Laravel and Docker, it provides a seamless experience for users to enjoy their favorite content offline or online without registration.

[![Live Demo](https://img.shields.io/badge/Live-khdownloader.xyz-teal?style=for-the-badge)](https://khdownloader.xyz)

## 🌟 Features

- **KHDiamond Watch Free:** Integrated player to watch videos online without an account.
- **High-Quality Downloads:** Support for KHAnime, KHDiamond, and KHFullHD in high definition.
- **No Friction:** No registration, no ads, and no desktop apps required.
- **Fast & Lightweight:** Optimized for speed and low server load.
- **Docker Ready:** Easy deployment using Docker and Docker Compose.

## 🛠️ Tech Stack

- **Backend:** Laravel 11 (PHP 8.4)
- **Frontend:** Tailwind CSS, Vanilla JS, Vite
- **Infrastructure:** Docker, Nginx, Redis, MySQL
- **Core Engine:** yt-dlp for reliable video extraction

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose

### Local Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/chheunphannet/KH-Downloader.git
   cd KH-Downloader
   ```

2. **Setup environment:**
   ```bash
   cp .env.example .env
   ```

3. **Start the application:**
   ```bash
   docker-compose up -d --build
   ```

4. **Initialize Laravel:**
   ```bash
   docker exec kh_app php artisan key:generate
   docker exec kh_app php artisan migrate
   ```

Visit `http://localhost` to see the app in action!

## 🌐 SEO & Keywords

To help others find this project, it targets the following keywords:
- **KHDownloader**
- **KH Downloader**
- **KHDiamond watch free**
- **KHAnime download**
- **KHFullHD downloader**
- **Cambodian video downloader**

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
