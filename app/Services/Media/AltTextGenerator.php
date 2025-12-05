<?php

namespace App\Services\Media;

use App\Models\Project;
use App\Models\BlogPost;

class AltTextGenerator
{
    /**
     * Generate ALT text for Project images.
     * Rule: Main keyword (2x) + Secondary keywords (1x each in order).
     */
    public function generateForProject(Project $project): array
    {
        return [
            'en' => $this->buildAltText(
                $project->main_keyword_en,
                $project->secondary_keywords_en ?? []
            ),
            'ar' => $this->buildAltText(
                $project->main_keyword_ar,
                $project->secondary_keywords_ar ?? []
            ),
            'keywords_en' => [
                'main' => $project->main_keyword_en,
                'secondary' => $project->secondary_keywords_en
            ],
            'keywords_ar' => [
                'main' => $project->main_keyword_ar,
                'secondary' => $project->secondary_keywords_ar
            ],
        ];
    }

    /**
     * Generate ALT text for Blog images.
     * (Conceptual implementation)
     */
    public function generateForBlog(BlogPost $post): array
    {
        // Simple fallback logic since blog SEO fields might differ
        $mainEn = $post->title_en;
        $mainAr = $post->title_ar;

        return [
            'en' => $mainEn,
            'ar' => $mainAr,
             'keywords_en' => ['main' => $mainEn],
             'keywords_ar' => ['main' => $mainAr],
        ];
    }

    protected function buildAltText($main, $secondaries)
    {
        if (empty($main)) {
            return '';
        }

        if (empty($secondaries) || !is_array($secondaries)) {
            return "$main – $main";
        }

        // Main keyword appears twice.
        // Secondary keywords appear once each, in order.
        // Format: "Main – Main with Sec1, Sec2, Sec3"

        $secondaryString = implode(', ', $secondaries);

        return "{$main} – {$main} {$secondaryString}";
    }
}
