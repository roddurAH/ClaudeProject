<?php

namespace Database\Seeders;

use App\Models\Commodity;
use App\Models\Crop;
use App\Models\District;
use App\Models\Market;
use Illuminate\Database\Seeder;

/** Major wholesale markets and the commodities tracked in the price board. */
class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $markets = [
            ['Dhaka', 'কারওয়ান বাজার', 'Kawran Bazar', 'both'],
            ['Bogura', 'মহাস্থান হাট', 'Mahasthan Haat', 'wholesale'],
            ['Chattogram', 'রেয়াজউদ্দিন বাজার', 'Reazuddin Bazar', 'both'],
            ['Khulna', 'খুলনা বড় বাজার', 'Khulna Boro Bazar', 'wholesale'],
            ['Rajshahi', 'সাহেব বাজার', 'Saheb Bazar', 'both'],
            ['Rangpur', 'রংপুর সিটি বাজার', 'Rangpur City Bazar', 'wholesale'],
            ['Jashore', 'বারীনগর বাজার', 'Barinagar Bazar', 'wholesale'],
            ['Mymensingh', 'মেছুয়া বাজার', 'Mechua Bazar', 'wholesale'],
            ['Sylhet', 'কালিঘাট বাজার', 'Kalighat Bazar', 'wholesale'],
            ['Barishal', 'বরিশাল বাজার রোড', 'Barishal Bazar Road', 'wholesale'],
        ];

        foreach ($markets as [$district, $bn, $en, $type]) {
            Market::updateOrCreate(['name_en' => $en], [
                'district_id' => District::where('name_en', $district)->value('id'),
                'name_bn' => $bn,
                'type' => $type,
                'is_active' => true,
            ]);
        }

        // [name_bn, name_en, category, unit, icon, crop slug]
        $commodities = [
            ['ধান (মোটা)', 'Paddy (coarse)', 'cereal', 'maund', '🌾', 'boro-rice'],
            ['চাল (মোটা)', 'Rice (coarse)', 'cereal', 'kg', '🍚', null],
            ['চাল (সরু)', 'Rice (fine)', 'cereal', 'kg', '🍚', null],
            ['গম', 'Wheat', 'cereal', 'kg', '🌾', 'wheat'],
            ['ভুট্টা', 'Maize', 'cereal', 'kg', '🌽', 'maize'],
            ['আলু', 'Potato', 'vegetable', 'kg', '🥔', 'potato'],
            ['পেঁয়াজ (দেশি)', 'Onion (local)', 'spice', 'kg', '🧅', 'onion'],
            ['রসুন (দেশি)', 'Garlic (local)', 'spice', 'kg', '🧄', null],
            ['কাঁচা মরিচ', 'Green chilli', 'spice', 'kg', '🌶️', 'chilli'],
            ['টমেটো', 'Tomato', 'vegetable', 'kg', '🍅', 'tomato'],
            ['বেগুন', 'Brinjal', 'vegetable', 'kg', '🍆', 'brinjal'],
            ['ফুলকপি', 'Cauliflower', 'vegetable', 'piece', '🥦', 'cauliflower'],
            ['লাউ', 'Bottle gourd', 'vegetable', 'piece', '🥒', 'bottle-gourd'],
            ['লালশাক', 'Red amaranth', 'vegetable', 'kg', '🥬', 'red-amaranth'],
            ['মসুর ডাল (মোটা)', 'Lentil (coarse)', 'pulse', 'kg', '🌱', 'lentil'],
            ['সরিষা', 'Mustard seed', 'oilseed', 'kg', '🌼', 'mustard'],
            ['পাট', 'Jute', 'fibre', 'maund', '🌿', 'jute'],
            ['লেবু', 'Lemon', 'fruit', 'hali', '🍋', 'lemon'],
        ];

        $crops = Crop::pluck('id', 'slug');
        foreach ($commodities as $i => [$bn, $en, $category, $unit, $icon, $cropSlug]) {
            Commodity::updateOrCreate(['name_en' => $en], [
                'name_bn' => $bn,
                'category' => $category,
                'unit' => $unit,
                'icon' => $icon,
                'crop_id' => $cropSlug ? ($crops[$cropSlug] ?? null) : null,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
