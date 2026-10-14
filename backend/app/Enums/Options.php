<?php

namespace App\Enums;

/**
 * Small fixed vocabularies that do not deserve their own enum class.
 * Keys are stored in the database; labels are shown in the admin panel.
 * The mobile app has its own Bangla/English labels for the same keys.
 */
final class Options
{
    public const CROP_CATEGORIES = [
        'cereal' => 'Cereal (ধান, গম, ভুট্টা)',
        'vegetable' => 'Vegetable (সবজি)',
        'fruit' => 'Fruit (ফল)',
        'pulse' => 'Pulse (ডাল)',
        'oilseed' => 'Oilseed (তেলবীজ)',
        'fibre' => 'Fibre (আঁশ)',
        'spice' => 'Spice (মসলা)',
    ];

    public const SEASONS = [
        'rabi' => 'Rabi (রবি, Oct–Mar)',
        'kharif1' => 'Kharif-1 (খরিফ-১, Mar–Jul)',
        'kharif2' => 'Kharif-2 (খরিফ-২, Jul–Oct)',
        'year_round' => 'Year round (সারা বছর)',
    ];

    public const STAGE_TYPES = [
        'land_prep' => 'Land preparation',
        'sowing' => 'Sowing / transplanting',
        'fertilizer' => 'Fertilizer',
        'irrigation' => 'Irrigation',
        'weeding' => 'Weeding',
        'pest_watch' => 'Pest & disease watch',
        'harvest' => 'Harvest',
        'other' => 'Other',
    ];

    public const PROBLEM_TYPES = [
        'pest' => 'Insect pest (পোকা)',
        'disease' => 'Disease (রোগ)',
        'nutrient' => 'Nutrient deficiency (পুষ্টির অভাব)',
        'weed' => 'Weed (আগাছা)',
        'physiological' => 'Physiological (অন্যান্য)',
    ];

    public const AFFECTED_PARTS = [
        'leaf' => 'Leaf (পাতা)',
        'stem' => 'Stem / shoot (কাণ্ড / ডগা)',
        'fruit' => 'Fruit / pod (ফল)',
        'grain' => 'Grain / panicle (শীষ / দানা)',
        'root' => 'Root / tuber (শিকড় / কন্দ)',
        'whole' => 'Whole plant (পুরো গাছ)',
    ];

    public const SEVERITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ];

    public const ARTICLE_CATEGORIES = [
        'gardening' => 'Home & rooftop gardening',
        'ipm' => 'Integrated pest management',
        'soil' => 'Soil & fertilizer',
        'safety' => 'Safety',
        'post_harvest' => 'Post-harvest & storage',
        'climate' => 'Weather & disaster',
        'market' => 'Market & rights',
    ];

    public const AUDIENCES = [
        'all' => 'Everyone',
        'farmer' => 'Farmers',
        'gardener' => 'Home gardeners',
    ];

    public const UNITS = [
        'kg' => 'kg (কেজি)',
        'maund' => 'maund (মণ, 40 kg)',
        'piece' => 'piece (পিস)',
        'hali' => 'hali (হালি, 4 pcs)',
        'dozen' => 'dozen (ডজন)',
        'litre' => 'litre (লিটার)',
    ];

    public const MARKET_TYPES = [
        'wholesale' => 'Wholesale (পাইকারি)',
        'retail' => 'Retail (খুচরা)',
        'both' => 'Both',
    ];

    public static function keys(array $options): array
    {
        return array_keys($options);
    }
}
