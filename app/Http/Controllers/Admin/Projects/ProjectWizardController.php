<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectWizardController extends Controller
{
    /**
     * Show Step 1: Basics and Location
     * Note: Currently only Basics (name_ar, name_en) are implemented as per requirements.
     */
    public function showBasicsStep($id = null)
    {
        $project = $id ? Project::findOrFail($id) : new Project();
        $developers = Developer::where('is_active', true)->get();

        return view('admin.projects.steps.basics', compact('project', 'developers'));
    }

    /**
     * Store/Update Step 1: Basics
     */
    public function storeBasicsStep(Request $request, $id = null)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'developer_id' => 'required|integer|exists:developers,id',
        ]);

        if ($id) {
            $project = Project::findOrFail($id);
        } else {
            $project = new Project();
        }

        $project->name_ar = $validated['name_ar'];
        $project->name_en = $validated['name_en'];
        $project->developer_id = $validated['developer_id'];

        // Auto-generate slug from English name if not already set or if creating new
        if (!$project->exists) {
            $project->slug = $this->generateUniqueSlug($validated['name_en']);

            // Set 'is_active' to false by default until published?
            // Memory says "The 'Soft Delete' behavior is implemented via an is_active boolean column... Inactive records are hidden by default".
            // Let's default to inactive or active depending on business logic. Usually drafts are inactive.
            // But I won't touch it if it has a default in DB.
        }

        $project->save();

        return redirect()->route('admin.projects.steps.basics', ['project' => $project->id])
                         ->with('success', __('admin.saved_successfully'));
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
