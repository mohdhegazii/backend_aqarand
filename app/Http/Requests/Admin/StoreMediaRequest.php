<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // 10MB default
            'collection' => ['nullable', 'string', 'in:default,gallery,floorplans,brochures,contracts,logo,hero'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');
            $collection = $this->input('collection', 'default');

            if (!$file) return;

            $mime = $file->getMimeType();

            // Image Collections
            if (in_array($collection, ['gallery', 'floorplans', 'logo', 'hero'])) {
                if (!str_starts_with($mime, 'image/')) {
                    $validator->errors()->add('file', 'The file must be an image for this collection.');
                }
            }

            // Document Collections
            if (in_array($collection, ['brochures', 'contracts'])) {
                if (!in_array($mime, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                    $validator->errors()->add('file', 'The file must be a PDF or Word document.');
                }
            }
        });
    }
}
