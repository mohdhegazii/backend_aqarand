<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFaq extends Model
{
    use HasFactory;

    protected $table = 'project_faqs';

    protected $fillable = [
        'project_id',
        'question_ar',
        'answer_ar',
        'question_en',
        'answer_en',
        'sort_order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
