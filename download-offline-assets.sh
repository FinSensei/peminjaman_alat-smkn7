#!/bin/bash
set -e
cd /home/arfin/01.API-UJIKOM/backend/public

mkdir -p vendor/{tailwindcss,alpinejs,aos,sweetalert2,chartjs,fonts}

echo "📥 Downloading AlpineJS..."
wget -q -O vendor/alpinejs/alpine.min.js https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js

echo "📥 Downloading AOS..."
wget -q -O vendor/aos/aos.css https://unpkg.com/aos@2.3.1/dist/aos.css
wget -q -O vendor/aos/aos.js https://unpkg.com/aos@2.3.1/dist/aos.js

echo "📥 Downloading SweetAlert2..."
wget -q -O vendor/sweetalert2/sweetalert2.min.js https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js
wget -q -O vendor/sweetalert2/sweetalert2.min.css https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css

echo "📥 Downloading Chart.js..."
wget -q -O vendor/chartjs/chart.umd.min.js https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js

echo "📥 Downloading TailwindCSS CLI..."
wget -q -O vendor/tailwindcss/tailwindcss https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
chmod +x vendor/tailwindcss/tailwindcss

echo "✅ Selesai! Update view pakai asset('vendor/...')"