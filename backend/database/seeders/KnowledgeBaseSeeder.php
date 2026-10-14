<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Crop;
use App\Models\Fertilizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Loads crops (with calendars, problems, fertilizer doses) and articles from
 * database/seeders/data/*.php. Re-running replaces the seeded crops' stages,
 * problems and doses, so edit the data files – or use the admin panel.
 */
class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $crops = array_merge(
            require __DIR__.'/data/crops_field.php',
            require __DIR__.'/data/crops_garden.php',
        );
        $fertilizers = Fertilizer::pluck('id', 'key');

        DB::transaction(function () use ($crops, $fertilizers) {
            foreach ($crops as $index => $data) {
                $crop = Crop::updateOrCreate(['slug' => $data['slug']], [
                    'name_bn' => $data['name_bn'],
                    'name_en' => $data['name_en'],
                    'category' => $data['category'],
                    'icon' => $data['icon'],
                    'season' => $data['season'],
                    'sowing_months' => $data['sowing_months'],
                    'harvest_months' => $data['harvest_months'],
                    'duration_days' => $data['duration_days'],
                    'is_garden_friendly' => $data['is_garden_friendly'],
                    'summary_bn' => $data['summary_bn'],
                    'summary_en' => $data['summary_en'],
                    'cultivation_bn' => $this->text($data['cultivation_bn']),
                    'cultivation_en' => $this->text($data['cultivation_en']),
                    'sort_order' => $index + 1,
                    'is_published' => true,
                ]);

                $crop->stages()->delete();
                foreach ($data['stages'] as [$day, $type, $titleBn, $titleEn, $detailsBn, $detailsEn]) {
                    $crop->stages()->create([
                        'day_offset' => $day,
                        'stage_type' => $type,
                        'title_bn' => $titleBn,
                        'title_en' => $titleEn,
                        'details_bn' => $detailsBn,
                        'details_en' => $detailsEn,
                    ]);
                }

                $crop->fertilizerDoses()->delete();
                foreach ($data['doses'] as $key => [$kg, $appBn, $appEn]) {
                    $crop->fertilizerDoses()->create([
                        'fertilizer_id' => $fertilizers[$key],
                        'kg_per_hectare' => $kg,
                        'application_bn' => $appBn,
                        'application_en' => $appEn,
                    ]);
                }

                $crop->problems()->delete();
                foreach ($data['problems'] as $p) {
                    $crop->problems()->create([
                        'type' => $p['type'],
                        'affected_part' => $p['part'],
                        'severity' => $p['sev'],
                        'name_bn' => $p['bn'],
                        'name_en' => $p['en'],
                        'symptoms_bn' => $this->text($p['sym_bn']),
                        'symptoms_en' => $this->text($p['sym_en']),
                        'prevention_bn' => $this->text($p['pre_bn']),
                        'prevention_en' => $this->text($p['pre_en']),
                        'organic_control_bn' => $this->text($p['org_bn']),
                        'organic_control_en' => $this->text($p['org_en']),
                        'chemical_control_bn' => $this->text($p['chem_bn']),
                        'chemical_control_en' => $this->text($p['chem_en']),
                        'is_published' => true,
                    ]);
                }
            }

            foreach (require __DIR__.'/data/articles.php' as $a) {
                Article::updateOrCreate(['slug' => $a['slug']], [
                    'category' => $a['category'],
                    'audience' => $a['audience'],
                    'icon' => $a['icon'],
                    'title_bn' => $a['title_bn'],
                    'title_en' => $a['title_en'],
                    'summary_bn' => $a['summary_bn'],
                    'summary_en' => $a['summary_en'],
                    'body_bn' => $this->text($a['body_bn']),
                    'body_en' => $this->text($a['body_en']),
                    'sort_order' => $a['sort_order'],
                    'is_published' => true,
                ]);
            }
        });
    }

    /** @param  string|list<string>  $value */
    private function text(string|array $value): ?string
    {
        $text = is_array($value) ? implode("\n", $value) : $value;

        return trim($text) === '' ? null : $text;
    }
}
