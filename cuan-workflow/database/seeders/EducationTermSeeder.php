<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationTermSeeder extends Seeder
{
    public function run()
    {
        $terms = [
            [
                'term_key' => 'business_model',
                'short_text' => 'Blueprint utama gimana bisnis kamu nyetak cuan.',
                'long_text' => 'Model bisnis itu roadmap gimana kamu dapet profit. Margin, biaya iklan, sampe scalability semua berawal dari sini. Contoh: Dropship itu low risk tapi margin tipis, kalo Produk Digital itu high margin tapi butuh skill teknis yang legit.',
                'contextual_template' => 'Model {{ business_model }} biasanya punya margin sekitar {{ margin }}.',
                'context_type' => 'general'
            ],
            [
                'term_key' => 'target_profit',
                'short_text' => 'Gaji bersih buat diri kamu sendiri yang pengen dibawa pulang tiap bulan.',
                'long_text' => 'Profit itu sisa duit setelah dipotong HPP, iklan, dan biaya operasional. Pastiin target kamu masuk akal ya, jangan sampe halu dibandingin sama modal yang ready.',
                'contextual_template' => 'Buat dapet profit {{ revenue }}, kamu perlu jualan {{ traffic }} unit dengan margin kamu sekarang.',
                'context_type' => 'planner'
            ],
            [
                'term_key' => 'capital_available',
                'short_text' => 'Bensin (duit cash) yang ready buat operasional & iklan.',
                'long_text' => 'Modal ini bakal dipake buat stok barang (HPP) dan bensin iklan. Inget, makin gede target cuan kamu, biasanya bensin yang dibutuhin juga makin banyak.',
                'contextual_template' => 'Modal kamu Rp {{ capital }} mungkin butuh di-adjust dikit kalo pengen ngejer target secepet itu.',
                'context_type' => 'planner'
            ],
            [
                'term_key' => 'selling_price',
                'short_text' => 'Harga tiket biar orang bisa nikmatin value produk kamu.',
                'long_text' => 'Harga jual nentuin berapa banyak unit yang harus kamu jual. Menaikkan harga (pricing power) itu cara paling "hack" buat ningkatkan profit tanpa perlu pusing nambah traffic.',
                'contextual_template' => 'Dengan harga Rp {{ price }}, kamu butuh {{ traffic }} buyer buat capai target revenue.',
                'context_type' => 'general'
            ],
            [
                'term_key' => 'traffic_strategy',
                'short_text' => 'Cara kamu narik massa biar mampir ke lapak/produk kamu.',
                'long_text' => 'Paid Ads itu jalur cepet tapi butuh modal. Organic Content itu aset jangka panjang tapi butuh napas panjang (konsistensi). Pilih yang paling masuk sama budget kamu.',
                'contextual_template' => 'Strategi {{ traffic_strategy }} butuh atensi lebih di {{ conversion }} biar gak boncos.',
                'context_type' => 'planner'
            ]
        ];

        foreach ($terms as $term) {
            DB::table('term_definitions')->updateOrInsert(
                ['term_key' => $term['term_key']],
                array_merge($term, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
