<?php

namespace Database\Seeders;

use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            [
                'title_id' => '4D3N BANGKOK – PATTAYA',
                'title_en' => '4D3N BANGKOK – PATTAYA',
                'title_ru' => '4 дня 3 ночи БАНГКОК – ПАТТАЙЯ',
                'description_id' => 'Serunya 4 hari 3 malam di Bangkok & Pattaya bareng pemandu ramah berbahasa Indonesia!',
                'description_en' => 'Experience 4 days and 3 nights in Bangkok & Pattaya with friendly Indonesian-speaking guide!',
                'description_ru' => 'Незабываемые 4 дня и 3 ночи в Бангкоке и Паттайе с дружелюбным русскоязычным гидом!',
                'price' => 'THB 15,800',
                'old_price' => 'THB 17,000',
                'image' => '/promo1.jpeg',
                'end_date' => '2025-07-18 00:00:00',
                'pdf_url' => 'https://drive.google.com/file/d/1rMOd6B5Eks15yu-o6QuAuOll4BL2APCi/view?usp=drive_link',
            ],
            [
                'title_id' => '3D2N KOTA BANGKOK',
                'title_en' => '3D2N BANGKOK CITY',
                'title_ru' => '3 дня 2 ночи в БАНГКОКЕ',
                'description_id' => 'Yuk, jalan-jalan seru 3 hari 2 malam di Bangkok, kulineran dan shopping puas!',
                'description_en' => 'Let\'s have an exciting 3 days and 2 nights in Bangkok, enjoy delicious food and shopping to the fullest!',
                'description_ru' => 'Насладитесь 3 днями и 2 ночами в Бангкоке — вкусная еда и шопинг без границ!',
                'price' => 'THB 12,400',
                'old_price' => 'THB 14,000',
                'image' => '/promo2.jpeg',
                'end_date' => '2025-07-18 00:00:00',
                'pdf_url' => 'https://drive.google.com/file/d/1Npii7NzqIk4Pi73O1FYGct2E5ZnxL41p/view?usp=drive_link',
            ],
            [
                'title_id' => '4D3N PHUKET-PHI PHI ISLANDS',
                'title_en' => '4D3N PHUKET-PHI PHI ISLANDS',
                'title_ru' => '4 дня 3 ночи ПХУКЕТ – ОСТРОВА ПХИ-ПХИ',
                'description_id' => 'Nikmati 4 hari 3 malam di Phuket & Phi Phi, island hopping seru Laut Andaman!',
                'description_en' => 'Enjoy 4 days and 3 nights in Phuket & Phi Phi, exciting island hopping in the Andaman Sea!',
                'description_ru' => 'Проведите 4 дня и 3 ночи на Пхукете и островах Пхи-Пхи — незабываемое путешествие по островам Андаманского моря',
                'price' => 'THB 13,000',
                'old_price' => 'THB 14,500',
                'image' => '/promo3.jpeg',
                'end_date' => '2025-07-18 00:00:00',
                'pdf_url' => 'https://drive.google.com/file/d/15_d7HSEMkpsaXlA7HzTRZu9l2usnWvMh/view?usp=drive_link',
            ],
        ];

        foreach ($promos as $promo) {
            Promo::create($promo);
        }
    }
}