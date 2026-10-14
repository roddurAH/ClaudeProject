<?php

namespace App\Services;

use Carbon\CarbonInterface;

/**
 * Turns a raw 7-day forecast into short, actionable farm advice in Bangla and
 * English. Rules follow common DAE/BAMIS agromet advisory practice:
 * do not spray or top-dress before rain, protect seedbeds from cold, watch for
 * late blight in cool foggy weather, irrigate during heat and dry spells.
 *
 * Thresholds are constants so an agronomist can tune them in one place.
 */
class FarmAdvisoryService
{
    public const HEAVY_RAIN_MM = 20;

    public const VERY_HEAVY_RAIN_MM = 50;

    public const RAIN_PROBABILITY_HIGH = 60;

    public const HEAT_C = 36;

    public const COLD_C = 10;

    public const STRONG_WIND_KMH = 40;

    public const CALM_WIND_KMH = 15;

    /**
     * @param  array<int, array{date:string,t_max:?float,t_min:?float,rain_mm:?float,rain_probability:?int,wind_max:?float,code:?int}>  $daily
     * @param  array{temperature:?float,humidity:?int,code:?int}  $current
     * @return list<array{key:string,level:string,icon:string,title_bn:string,title_en:string,body_bn:string,body_en:string}>
     */
    public function advise(array $daily, array $current, CarbonInterface $today): array
    {
        $advice = [];
        $next3 = array_slice($daily, 0, 3);
        $next7 = array_slice($daily, 0, 7);
        if ($next3 === []) {
            return [];
        }

        // 1. Heavy rain in the next 3 days
        $maxRain = max(array_map(fn ($d) => (float) ($d['rain_mm'] ?? 0), $next3));
        if ($maxRain >= self::HEAVY_RAIN_MM) {
            $advice[] = $this->item(
                'heavy_rain',
                $maxRain >= self::VERY_HEAVY_RAIN_MM ? 'danger' : 'warning',
                'rain',
                'আগামী ৩ দিনে ভারী বৃষ্টির সম্ভাবনা',
                'Heavy rain likely in the next 3 days',
                "এখন কীটনাশক বা ইউরিয়া সার দেবেন না, বৃষ্টিতে ধুয়ে যাবে।\nজমির পানি বের হওয়ার নালা পরিষ্কার রাখুন।\nপাকা ফসল থাকলে দ্রুত কেটে শুকনো জায়গায় রাখুন।",
                "Do not spray pesticide or apply urea now – rain will wash it away.\nClear drainage channels in the field.\nHarvest mature crops quickly and keep them dry."
            );
        } else {
            // 2. Rain today → postpone spraying
            $today0 = $next3[0];
            if (($today0['rain_probability'] ?? 0) >= self::RAIN_PROBABILITY_HIGH || ($today0['rain_mm'] ?? 0) >= 5) {
                $advice[] = $this->item(
                    'rain_today',
                    'info',
                    'umbrella',
                    'আজ বৃষ্টি হতে পারে',
                    'Rain expected today',
                    'আজ স্প্রে বা সার প্রয়োগ না করে শুকনো দিনের জন্য অপেক্ষা করুন।',
                    'Postpone spraying and fertilizer application until a dry day.'
                );
            }
        }

        // 3. Strong wind / storm
        $maxWind = max(array_map(fn ($d) => (float) ($d['wind_max'] ?? 0), $next3));
        if ($maxWind >= self::STRONG_WIND_KMH) {
            $advice[] = $this->item(
                'strong_wind',
                'danger',
                'storm',
                'ঝড়ো হাওয়ার সম্ভাবনা',
                'Strong winds expected',
                "কলা, পেঁপে ও সবজির মাচা খুঁটি দিয়ে শক্ত করে বাঁধুন।\n৮০% পেকে যাওয়া ধান দ্রুত কেটে ফেলুন।\nরেডিও/টিভিতে আবহাওয়া সংকেত শুনুন।",
                "Tie banana, papaya and vegetable trellises firmly to stakes.\nHarvest rice that is 80% mature.\nFollow weather signals on radio/TV."
            );
        }

        // 4. Heat stress
        $maxTemp = max(array_map(fn ($d) => (float) ($d['t_max'] ?? 0), $next3));
        if ($maxTemp >= self::HEAT_C) {
            $advice[] = $this->item(
                'heat',
                'warning',
                'heat',
                'তীব্র গরমের সম্ভাবনা',
                'Very hot days ahead',
                "সকালে বা বিকেলে সেচ দিন, দুপুরে নয়।\nসবজির গোড়ায় খড় বা কচুরিপানা দিয়ে মালচিং করুন।\nগবাদিপশু ও হাঁস-মুরগিকে ছায়ায় রাখুন ও পর্যাপ্ত পানি দিন।",
                "Irrigate in the morning or evening, not at noon.\nMulch vegetables with straw or water hyacinth.\nKeep livestock and poultry in shade with plenty of water."
            );
        }

        // 5. Cold wave (important for Boro seedbeds, Dec–Jan)
        $minTemp = min(array_map(fn ($d) => (float) ($d['t_min'] ?? 99), $next3));
        if ($minTemp <= self::COLD_C) {
            $advice[] = $this->item(
                'cold',
                'warning',
                'cold',
                'শৈত্যপ্রবাহের সম্ভাবনা',
                'Cold wave likely',
                "বোরো বীজতলা রাতে স্বচ্ছ পলিথিন দিয়ে ঢেকে রাখুন, সকালে সরিয়ে দিন।\nবীজতলায় সকালে জমে থাকা ঠান্ডা পানি বের করে নতুন পানি দিন।\nচারা হলুদ হলে প্রতি শতাংশে ২৮০ গ্রাম ইউরিয়া উপরি প্রয়োগ করুন।",
                "Cover Boro seedbeds with clear polythene at night and remove it in the morning.\nDrain cold standing water from seedbeds in the morning and add fresh water.\nIf seedlings turn yellow, top-dress 280 g urea per decimal."
            );
        }

        // 6. Late blight risk – cool + humid/foggy in winter
        $month = (int) $today->format('n');
        $isWinter = in_array($month, [11, 12, 1, 2], true);
        $humid = ($current['humidity'] ?? 0) >= 85 || in_array($current['code'] ?? -1, [45, 48], true);
        $cool = $minTemp >= 8 && $minTemp <= 16;
        if ($isWinter && $humid && $cool) {
            $advice[] = $this->item(
                'late_blight',
                'warning',
                'disease',
                'আলু ও টমেটোতে নাবি ধসা (লেট ব্লাইট) রোগের ঝুঁকি',
                'Late blight risk for potato and tomato',
                "কুয়াশা ও ঠান্ডা আবহাওয়ায় এই রোগ দ্রুত ছড়ায়।\nরোগ দেখা দেওয়ার আগেই ম্যানকোজেব জাতীয় ছত্রাকনাশক ৭ দিন পরপর স্প্রে করুন।\nএ সময় জমিতে সেচ দেওয়া বন্ধ রাখুন।",
                "The disease spreads fast in foggy, cool weather.\nSpray a mancozeb-based fungicide every 7 days before symptoms appear.\nStop irrigating the field during this period."
            );
        }

        // 7. Dry spell
        $weekRain = array_sum(array_map(fn ($d) => (float) ($d['rain_mm'] ?? 0), $next7));
        if ($weekRain < 2 && $maxTemp >= 30) {
            $advice[] = $this->item(
                'dry_spell',
                'info',
                'water',
                'আগামী সপ্তাহে বৃষ্টির সম্ভাবনা কম',
                'Little rain expected this week',
                "মাটির রস দেখে প্রয়োজনমতো সেচ দিন।\nধানে পর্যায়ক্রমে ভেজানো ও শুকানো (AWD) পদ্ধতিতে সেচ দিয়ে পানি ও খরচ বাঁচান।",
                "Check soil moisture and irrigate as needed.\nUse Alternate Wetting and Drying (AWD) in rice to save water and cost."
            );
        }

        // 8. Good spraying window
        $calm = array_slice($daily, 0, 2);
        $goodWindow = count($calm) === 2
            && collect($calm)->every(fn ($d) => ($d['rain_probability'] ?? 100) < 30 && ($d['wind_max'] ?? 99) < self::CALM_WIND_KMH);
        if ($goodWindow && $maxRain < self::HEAVY_RAIN_MM) {
            $advice[] = $this->item(
                'spray_window',
                'info',
                'spray',
                'স্প্রে ও সার দেওয়ার উপযুক্ত সময়',
                'Good time for spraying and fertilizer',
                'আগামী ২ দিন বৃষ্টি ও বাতাস কম থাকবে। প্রয়োজন হলে সকালে বা বিকেলে স্প্রে করুন। প্রয়োজন ছাড়া কীটনাশক দেবেন না।',
                'The next 2 days look dry and calm. If spraying is needed, do it in the morning or late afternoon. Do not spray unless needed.'
            );
        }

        if ($advice === []) {
            $advice[] = $this->item(
                'normal',
                'info',
                'ok',
                'আবহাওয়া স্বাভাবিক',
                'Normal weather',
                'নিয়মিত জমি পরিদর্শন করুন এবং পোকা-রোগের লক্ষণ দেখলে "ফসলের সমস্যা" অংশে মিলিয়ে নিন।',
                'Visit your field regularly and check any pest or disease signs in the "Crop problems" section.'
            );
        }

        return $advice;
    }

    private function item(string $key, string $level, string $icon, string $titleBn, string $titleEn, string $bodyBn, string $bodyEn): array
    {
        return [
            'key' => $key,
            'level' => $level,
            'icon' => $icon,
            'title_bn' => $titleBn,
            'title_en' => $titleEn,
            'body_bn' => $bodyBn,
            'body_en' => $bodyEn,
        ];
    }
}
