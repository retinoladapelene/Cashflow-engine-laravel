import { select } from './utils/helpers.js';

export function startTour() {

    if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
        console.error("Driver.js not loaded");
        return;
    }

    const driver = window.driver.js.driver;

    const tour = driver({
        showProgress: true,
        animate: true,
        allowClose: true,
        doneBtnText: 'Gaspol!',
        nextBtnText: 'Lanjut',
        prevBtnText: 'Kembali',
        progressText: 'Step {{current}} dari {{total}}',
        popoverClass: 'driverjs-theme',
        steps: [
            {
                popover: {
                    title: '👋 Selamat Datang di CuanCapital!',
                    description: 'Framework eksekusi bisnis yang dirancang untuk mengubah ide menjadi cashflow nyata. Mari kita tour fitur-fiturnya!',
                }
            },
            {
                element: '#currency-selector',
                popover: {
                    title: '💱 Pilih Mata Uang',
                    description: 'Sesuaikan mata uang dengan target pasar kamu. Tersedia IDR, USD, EUR, GBP, dan mata uang lainnya. Semua kalkulasi akan otomatis menyesuaikan!',
                    side: "bottom",
                    align: 'center'
                }
            },
            {
                element: '#theme-toggle',
                popover: {
                    title: '🌓 Dark Mode Toggle',
                    description: 'Klik untuk beralih antara mode terang dan gelap. Pilih yang paling nyaman untuk mata kamu!',
                    side: "bottom",
                    align: 'end'
                }
            },
            {
                element: '#goal-income',
                popover: {
                    title: '💰 Target Income Bulanan',
                    description: 'Masukkan target cuan yang ingin kamu capai per bulan. Jangan takut mimpi besar! Sistem akan otomatis menghitung apa yang harus kamu lakukan.',
                    side: "bottom",
                    align: 'start'
                }
            },
            {
                element: '#goal-price',
                popover: {
                    title: '🏷️ Harga Produk Rata-Rata',
                    description: 'Input harga rata-rata produk kamu. Dari sini kita akan hitung berapa banyak sales yang kamu butuhkan untuk mencapai target income.',
                    side: "bottom",
                    align: 'start'
                }
            },
            {
                element: '#goalFunnelChart',
                popover: {
                    title: '📊 Funnel Visualizer',
                    description: 'Chart ini menunjukkan peta jalan kamu: Traffic → Leads → Sales. Lihat berapa visitor yang kamu butuhkan untuk mencapai target!',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#goal-qty',
                popover: {
                    title: '🎯 Target Sales Wajib',
                    description: 'Jumlah unit yang HARUS kamu jual per bulan untuk mencapai target income. Ini adalah KPI utama kamu!',
                    side: "left",
                    align: 'start'
                }
            },
            {
                element: '#goal-traffic',
                popover: {
                    title: '👥 Traffic Minimal Required',
                    description: 'Jumlah minimal pengunjung yang kamu butuhkan. Jangan komplain sepi kalau traffic belum segini!',
                    side: "left",
                    align: 'start'
                }
            },
            {
                element: '#calculator-section',
                popover: {
                    title: '💰 Mesin Simulasi\nProfit',
                    description: 'Jangan cuma nebak! Simulasikan potensi bisnis kamu dengan data nyata. Mainkan slider untuk lihat proyeksi profit.',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#price-input',
                popover: {
                    title: '🏷️ Simulasi: Harga \nJual',
                    description: 'Geser slider ini untuk bermain dengan harga produk. Lihat bagaimana harga mempengaruhi total profit kamu. Ingat: harga = persepsi value!',
                    side: "top",
                    align: 'start'
                }
            },
            {
                element: '#traffic-input',
                popover: {
                    title: '👥 Simulasi: Traffic & Pengunjung',
                    description: 'Berapa banyak orang yang melihat penawaran kamu? Atur jumlah visitor di sini. Bisa pakai slider atau ketik langsung di input box!',
                    side: "top",
                    align: 'start'
                }
            },
            {
                element: '#conv-input',
                popover: {
                    title: '📈 Simulasi: Conversion Rate',
                    description: 'Tingkat keberhasilan closing. Makin tinggi persennya, makin jago copywriting & sales funnel kamu! 1-2% = pemula, 3-5% = pro, >5% = expert.',
                    side: "top",
                    align: 'start'
                }
            },
            {
                element: '#ad-spend-input',
                popover: {
                    title: '💸 Simulasi: Ad Spend',
                    description: 'Budget iklan per bulan. Sistem akan hitung net profit setelah dikurangi biaya iklan. Pantau ROAS (Return on Ad Spend) kamu!',
                    side: "top",
                    align: 'start'
                }
            },
            {
                element: '#total-revenue',
                popover: {
                    title: '💵 Total Profit Bersih Bulanan',
                    description: 'Ini adalah net profit per bulan setelah dikurangi ad spend. Angka ini yang masuk ke kantong kamu!',
                    side: "left",
                    align: 'start'
                }
            },
            {
                element: '#magic-number',
                popover: {
                    title: '✨ Magic Number',
                    description: 'Angka ajaib yang menunjukkan potensi revenue jika konversi kamu mencapai 4% (standar industri). Ini adalah benchmark target kamu!',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#profitChart',
                popover: {
                    title: '📈 Proyeksi Pertumbuhan 12 Bulan',
                    description: 'Visualisasi pertumbuhan cuan kamu dalam 1 tahun ke depan jika konsisten. Lihat tren akumulasi profit bulanan!',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#yearly-profit',
                popover: {
                    title: '🎯 Proyeksi 1 Tahun - The North Star',
                    description: 'Ini angka target kamu setahun. Fokus kejar angka ini dengan eksekusi roadmap di bawah. Konsistensi adalah kunci!',
                    side: "left",
                    align: 'start'
                }
            },
            {
                element: '#other-products',
                popover: {
                    title: '🚀 The Missing Piece',
                    description: 'Amunisi tambahan untuk scaling bisnis kamu: Google Sheet Master, Finance Dashboard, dan Content Planner. Coming soon!',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#roadmap-progress-container',
                popover: {
                    title: '🏆 Progress Bar Eksekusi',
                    description: 'Pantau progress eksekusi kamu di sini. Setiap step yang diselesaikan akan mengisi bar ini. Target: 100% untuk jadi Top 1%!',
                    side: "bottom",
                    align: 'center'
                }
            },
            {
                element: '#rank-text',
                popover: {
                    title: '⭐ Rank System',
                    description: 'Level kamu berdasarkan progress: Beginner → Intermediate → Advanced → Expert → Master → Top 1%. Naik level dengan menyelesaikan roadmap!',
                    side: "bottom",
                    align: 'start'
                }
            },
            {
                element: '#roadmap-nav',
                popover: {
                    title: '🗺️ Navigasi Roadmap',
                    description: 'Gunakan tombol fase ini untuk melompat cepat ke setiap tahapan bisnis: dari Riset (Fase 1) hingga Scaling (Fase 4).',
                    side: "bottom",
                    align: 'center'
                }
            },
            {
                element: '#cards-container',
                popover: {
                    title: '✅ 20 Strategi Eksekusi',
                    description: 'Setiap kartu adalah misi. Klik untuk buka detail, tools rekomendasi, dan checklist aksi. Selesaikan step-by-step dari atas ke bawah!',
                    side: "top",
                    align: 'center'
                }
            },
            {
                popover: {
                    title: '🎉 Siap Gas?',
                    description: 'Kamu sudah paham semua fitur! Sekarang waktunya eksekusi. Mulai dari set target di Reverse Goal, lalu jalankan roadmap satu per satu. Let\'s build your empire! 🚀',
                }
            }
        ],
        onHighlightStarted: (element, step, options) => {
            if (step.element === '#roadmap') {
                const roadmap = select('#roadmap');
                if (roadmap) roadmap.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        onDestroyStarted: () => {
            tour.destroy();
            localStorage.setItem('cuan_tour_seen', 'true');
        },
    });

    tour.drive();
}
