<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaSetting;
use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Rely on route middleware for admin check
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Fetch settings or use defaults
        try {
            $settings = MediaSetting::first();
        } catch (\Throwable $e) {
            $settings = null;
        }

        $imageMaxMb = $settings?->image_max_size_mb ?? 10;
        $pdfMaxMb = $settings?->pdf_max_size_mb ?? 10;

        $maxImageKb = $imageMaxMb * 1024;
        $maxPdfKb = $pdfMaxMb * 1024;
        $overallMaxKb = max($maxImageKb, $maxPdfKb);

        // Validation logic for a single file instance
        $fileValidationClosure = function ($attribute, $value, $fail) use ($maxImageKb, $maxPdfKb) {
            if (!$value || !$value->isValid()) return;

            $mime = $value->getMimeType();
            $sizeKb = $value->getSize() / 1024;

            if (str_starts_with($mime, 'image/')) {
                if ($sizeKb > $maxImageKb) {
                    $fail("The image may not be greater than {$maxImageKb} KB (checking against {$this->formatBytes($maxImageKb * 1024)}).");
                }
            } elseif ($mime === 'application/pdf') {
                if ($sizeKb > $maxPdfKb) {
                    $fail("The PDF may not be greater than {$maxPdfKb} KB (checking against {$this->formatBytes($maxPdfKb * 1024)}).");
                }
            }
        };

        $baseFileRules = [
            'file',
            'mimes:jpeg,png,webp,pdf',
            "max:{$overallMaxKb}",
            $fileValidationClosure,
        ];

        // Ensure alts array matches files array if necessary
        $altsValidationClosure = function ($attribute, $value, $fail) {
             $files = [];
            if ($this->hasFile('files')) {
                $files = $this->file('files');
            } elseif ($this->hasFile('file')) {
                $files = [$this->file('file')];
            }

            if (is_array($value) && count($value) !== count($files)) {
                $fail("The number of alt text entries must match the number of uploaded files.");
            }
        };

        $rules = [
            'alts' => [
                'nullable', // Changed to nullable for backward compatibility
                'array',
                $altsValidationClosure
            ],
            'alts.*' => [
                'required_with:alts', // Only required if alts array is present
                'string',
                'max:255'
            ],
            // Legacy single alt_text fallback
            // Ensure logic: if images uploaded, either alts or alt_text is required
            'alt_text' => [
                'nullable',
                'string',
                 function ($attribute, $value, $fail) {
                    // If alts array is present and valid, we don't need alt_text
                    if ($this->filled('alts') && is_array($this->input('alts'))) {
                        return;
                    }

                    // Normalize files input
                    $files = [];
                    if ($this->hasFile('files')) {
                        $files = $this->file('files');
                    } elseif ($this->hasFile('file')) {
                        $files = [$this->file('file')];
                    }

                    foreach ($files as $file) {
                        if ($file && $file->isValid()) {
                            $mime = $file->getMimeType();
                            // If it's an image and no alt_text provided (and alts is empty), fail
                            if (str_starts_with($mime, 'image/') && empty($value)) {
                                $fail('The alt text field is required for images (or provide alts array).');
                                return;
                            }
                        }
                    }
                },
            ],

            'entity_type' => 'nullable|string',
            'entity_id'   => 'nullable|integer',
            'country'     => 'nullable|string',
            'city'        => 'nullable|string',
            'slug'        => 'nullable|string',
            'is_private'  => 'nullable|boolean',
        ];

        if ($this->hasFile('files')) {
            $rules['files'] = ['required', 'array', 'min:1'];
            // Apply file rules to each item in the array
            $rules['files.*'] = array_merge(['required'], $baseFileRules);
        } else {
            // Fallback to legacy single file
            $rules['file'] = array_merge(['required'], $baseFileRules);
        }

        return $rules;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
