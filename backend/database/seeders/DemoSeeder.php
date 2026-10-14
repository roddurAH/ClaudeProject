<?php

namespace Database\Seeders;

use App\Enums\QuestionStatus;
use App\Enums\Role;
use App\Models\Alert;
use App\Models\Commodity;
use App\Models\Crop;
use App\Models\District;
use App\Models\Market;
use App\Models\MarketPrice;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * LOCAL TESTING DATA ONLY – known passwords and invented prices.
 * Prices are clearly labelled "Demo data" so nobody mistakes them for real
 * market information. Replace with real data (admin panel or CSV import).
 */
class DemoSeeder extends Seeder
{
    public const DEMO_SOURCE = 'Demo data – not real prices';

    public function run(): void
    {
        $dhaka = District::where('name_en', 'Dhaka')->value('id');
        $bogura = District::where('name_en', 'Bogura')->value('id');

        $admin = $this->staff('Super Admin', '01700000001', 'admin@krishisathi.test', 'Admin@12345', Role::SuperAdmin, $dhaka);
        $expert = $this->staff('Dr. Agro Expert', '01700000002', 'expert@krishisathi.test', 'Expert@12345', Role::Expert, $dhaka);
        $dataEntry = $this->staff('Price Officer', '01700000003', 'prices@krishisathi.test', 'Prices@12345', Role::DataEntry, $bogura);

        $farmer = User::firstOrNew(['phone' => '01711111111']);
        $farmer->fill([
            'name' => 'রহিম উদ্দিন',
            'password' => '12345',
            'user_type' => 'farmer',
            'district_id' => $bogura,
            'preferred_language' => 'bn',
        ]);
        $farmer->role = Role::Farmer;
        $farmer->is_active = true;
        $farmer->save();

        $this->demoPrices($dataEntry);
        $this->demoQuestions($farmer, $expert);
        $this->demoAlert($admin);
    }

    private function staff(string $name, string $phone, string $email, string $password, Role $role, ?int $districtId): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->fill(['name' => $name, 'phone' => $phone, 'password' => $password, 'district_id' => $districtId]);
        $user->role = $role;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    /** 30 days of plausible but invented wholesale prices (and Dhaka retail). */
    private function demoPrices(User $enteredBy): void
    {
        // Typical 2026 wholesale level in Taka per unit.
        $base = [
            'Paddy (coarse)' => 1250, 'Rice (coarse)' => 52, 'Rice (fine)' => 74, 'Wheat' => 40, 'Maize' => 31,
            'Potato' => 22, 'Onion (local)' => 58, 'Garlic (local)' => 130, 'Green chilli' => 90, 'Tomato' => 45,
            'Brinjal' => 48, 'Cauliflower' => 35, 'Bottle gourd' => 45, 'Red amaranth' => 30,
            'Lentil (coarse)' => 98, 'Mustard seed' => 95, 'Jute' => 3200, 'Lemon' => 22,
        ];

        mt_srand(2026); // deterministic demo data
        $commodities = Commodity::all()->keyBy('name_en');
        $rows = [];
        $now = now();

        foreach (Market::all() as $marketIndex => $market) {
            $marketFactor = 1 + (($marketIndex % 5) - 2) * 0.03; // ±6% between markets
            foreach ($base as $name => $price) {
                $commodity = $commodities[$name] ?? null;
                if (! $commodity) {
                    continue;
                }
                $level = $price * $marketFactor;
                for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
                    $level *= 1 + (mt_rand(-40, 45) / 1000); // daily random walk ±4%
                    $min = round($level * 0.95, 0);
                    $max = round($level * 1.05, 0);
                    $date = $now->copy()->subDays($daysAgo)->toDateString();
                    $rows[] = $this->priceRow($commodity->id, $market->id, $date, 'wholesale', $min, $max, $enteredBy->id);
                    if ($market->name_en === 'Kawran Bazar') {
                        $rows[] = $this->priceRow($commodity->id, $market->id, $date, 'retail', round($min * 1.3), round($max * 1.35), $enteredBy->id);
                    }
                }
            }
        }

        MarketPrice::where('source', self::DEMO_SOURCE)->delete();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('market_prices')->insert($chunk);
        }
    }

    private function priceRow(int $commodityId, int $marketId, string $date, string $type, float $min, float $max, int $userId): array
    {
        return [
            'commodity_id' => $commodityId, 'market_id' => $marketId, 'price_date' => $date, 'price_type' => $type,
            'min_price' => $min, 'max_price' => $max, 'source' => self::DEMO_SOURCE, 'entered_by' => $userId,
            'created_at' => now(), 'updated_at' => now(),
        ];
    }

    private function demoQuestions(User $farmer, User $expert): void
    {
        if (Question::where('user_id', $farmer->id)->exists()) {
            return;
        }

        $brinjal = Crop::where('slug', 'brinjal')->value('id');
        $boro = Crop::where('slug', 'boro-rice')->value('id');
        $tomato = Crop::where('slug', 'tomato')->value('id');

        $answered = new Question([
            'body' => 'বেগুনের ডগা নেতিয়ে পড়ছে, কিছু বেগুনে ছিদ্র দেখা যাচ্ছে। প্রতি সপ্তাহে স্প্রে করেও কাজ হচ্ছে না। কী করব?',
            'crop_id' => $brinjal,
            'district_id' => $farmer->district_id,
        ]);
        $answered->user()->associate($farmer);
        $answered->status = QuestionStatus::Answered;
        $answered->is_public = true;
        $answered->answered_at = now()->subDays(2);
        $answered->created_at = now()->subDays(3);
        $answered->save();
        $answered->answers()->create([
            'user_id' => $expert->id,
            'body' => "এটি ডগা ও ফল ছিদ্রকারী পোকার আক্রমণ।\n১. সপ্তাহে একবার নেতানো ডগা ও ছিদ্রযুক্ত বেগুন কেটে মাটিতে পুঁতে ফেলুন।\n২. ১০ মিটার পরপর সেক্স ফেরোমন ফাঁদ লাগান (কৃষি অফিস বা বীজের দোকানে পাবেন)।\n৩. প্রতি সপ্তাহে স্প্রে বন্ধ করুন – এতে উপকারী পোকা মরে, খরচ বাড়ে।\n৪. খুব বেশি আক্রমণ হলে স্পিনোস্যাড গ্রুপের কীটনাশক দিন এবং লেবেলে লেখা দিন পার হওয়ার আগে বেগুন তুলবেন না।\nপরের মৌসুমে বারি বিটি বেগুন চাষ করে দেখতে পারেন।",
        ]);

        $pending = new Question([
            'body' => 'বোরো বীজতলার চারা হলুদ হয়ে যাচ্ছে, কিছু চারা মরে যাচ্ছে। খুব ঠান্ডা পড়ছে।',
            'crop_id' => $boro,
            'district_id' => $farmer->district_id,
        ]);
        $pending->user()->associate($farmer);
        $pending->created_at = now()->subHours(5);
        $pending->save();

        $pending2 = new Question([
            'body' => 'ছাদের টবের টমেটো গাছের পাতা কুঁকড়ে যাচ্ছে আর ফুল ঝরে যাচ্ছে।',
            'crop_id' => $tomato,
            'district_id' => $farmer->district_id,
        ]);
        $pending2->user()->associate($farmer);
        $pending2->created_at = now()->subHour();
        $pending2->save();
    }

    private function demoAlert(User $admin): void
    {
        Alert::firstOrCreate(
            ['title_en' => 'Demo: Cold wave expected in the north'],
            [
                'severity' => 'warning',
                'title_bn' => 'নমুনা সতর্কবার্তা: উত্তরাঞ্চলে শৈত্যপ্রবাহের সম্ভাবনা',
                'body_bn' => 'আগামী কয়েক দিন তাপমাত্রা কমতে পারে। বোরো বীজতলা রাতে পলিথিন দিয়ে ঢেকে রাখুন এবং সকালে সরিয়ে দিন। আলু ও টমেটোতে নাবি ধসা রোগের দিকে নজর রাখুন।',
                'body_en' => 'Temperatures may drop in the coming days. Cover Boro seedbeds with polythene at night and remove it in the morning. Watch potato and tomato for late blight.',
                'is_national' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(10),
                'created_by' => $admin->id,
            ],
        );
    }
}
