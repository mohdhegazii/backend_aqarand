<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MediaSetting;

class MediaSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use model if it exists, otherwise raw DB.
        // We will assume Model will be created in next step, but Seeder can use DB facade for safety if Models not loaded.
        // However, standard practice is to use Models. I'll use DB to avoid Model dependency if run before Model creation (unlikely but safe).
        // Actually, let's use the Model in the next step, but here I'll use DB to be safe with just schema.
        // Wait, prompt says "Create a database seeder... Check if a media_settings row exists...".

        $exists = DB::table('media_settings')->exists();

        if (!$exists) {
            DB::table('media_settings')->insert([
                'disk_default' => 's3_media_local',
                'disk_system_assets' => 'local_work',
                'image_max_size_mb' => 2,
                'pdf_max_size_mb' => 10,
                'image_quality' => 80,
                'image_max_width' => 2000,
                'generate_webp' => true,
                'generate_thumb' => true,
                'thumb_width' => 400,
                'medium_width' => 800,
                'large_width' => 1600,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
