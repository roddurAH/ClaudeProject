<?php

namespace Database\Seeders;

use App\Models\Fertilizer;
use Illuminate\Database\Seeder;

class FertilizerSeeder extends Seeder
{
    /**
     * Official farmer-level prices fixed by the Ministry of Agriculture
     * (April 2023, still in force in 2026). Update from the admin panel.
     */
    public function run(): void
    {
        $fertilizers = [
            ['urea', 'ইউরিয়া', 'Urea', 27, 'নাইট্রোজেন সার। কয়েক কিস্তিতে দিন, একবারে নয়।', 'Nitrogen fertilizer. Apply in splits, not all at once.'],
            ['tsp', 'টিএসপি', 'TSP (triple super phosphate)', 27, 'ফসফরাস সার। সাধারণত শেষ চাষে দেওয়া হয়।', 'Phosphorus fertilizer, usually applied at final ploughing.'],
            ['mop', 'এমওপি (পটাশ)', 'MoP (muriate of potash)', 20, 'পটাশ সার। রোগ প্রতিরোধ ক্ষমতা বাড়ায়।', 'Potash fertilizer; improves disease resistance.'],
            ['dap', 'ডিএপি', 'DAP (diammonium phosphate)', 21, 'ডিএপি দিলে টিএসপি লাগে না; প্রতি কেজি ডিএপির জন্য প্রায় ৪০০ গ্রাম ইউরিয়া কম দিন।', 'DAP replaces TSP; reduce urea by about 400 g per kg of DAP.'],
            ['gypsum', 'জিপসাম', 'Gypsum', null, 'সালফার ও ক্যালসিয়ামের উৎস।', 'Source of sulphur and calcium.'],
            ['zinc', 'দস্তা (জিংক সালফেট)', 'Zinc sulphate', null, 'দস্তার অভাব (ধানের খৈরা রোগ) প্রতিরোধ করে।', 'Prevents zinc deficiency (Khaira in rice).'],
            ['boron', 'বোরিক এসিড (বোরন)', 'Boric acid (boron)', null, 'সরিষা, ফুলকপি, ভুট্টা ও আলুতে প্রয়োজন।', 'Needed by mustard, cauliflower, maize and potato.'],
        ];

        foreach ($fertilizers as $i => [$key, $bn, $en, $price, $notesBn, $notesEn]) {
            Fertilizer::updateOrCreate(['key' => $key], [
                'name_bn' => $bn,
                'name_en' => $en,
                'official_price_per_kg' => $price,
                'notes_bn' => $notesBn,
                'notes_en' => $notesEn,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
