<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Amenity;
use App\Models\Country;
use App\Models\Developer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projectsQuery = Project::with(['developer', 'city', 'district'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $projectsQuery->where(function ($query) use ($search) {
                $likeSearch = '%'.$search.'%';

                $query->where('name_ar', 'like', $likeSearch)
                      ->orWhere('name_en', 'like', $likeSearch)
                      ->orWhere('name', 'like', $likeSearch);
            });
        }

        if ($request->filled('developer_id')) {
            $projectsQuery->where('developer_id', $request->input('developer_id'));
        }

        $projects = $projectsQuery->paginate(20)->withQueryString();
        $developers = Developer::where('is_active', true)->orderBy('name')->get();

        return view('admin.projects.index', compact('projects', 'developers'));
    }

    public function create()
    {
        $developers = Developer::where('is_active', true)->orderBy('name')->get();
        $countries = Country::all();
        $amenities = Amenity::where('amenity_type', 'project')
                            ->orWhere('amenity_type', 'both')
                            ->get()
                            ->groupBy('type_label'); // Or group by category if implemented

        // For Master Project Dropdown
        $existingProjects = Project::select('id', 'name_en', 'name_ar', 'name')->get();

        return view('admin.projects.create', compact('developers', 'countries', 'amenities', 'existingProjects'));
    }

    public function store(StoreProjectRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Slug Generation
            $slugBase = $data['name_en'] ?? $data['name_ar'];
            $data['slug'] = Project::generateSlug($slugBase);

            // Fallback for 'name' legacy column
            $data['name'] = $data['name_en'] ?? $data['name_ar'];

            $project = Project::create($data);

            // Amenities
            if ($request->has('amenities')) {
                $project->amenities()->sync($request->amenities);
            }

            // FAQs
            if ($request->has('faqs')) {
                foreach ($request->faqs as $index => $faqData) {
                    if (!empty($faqData['question_en']) || !empty($faqData['question_ar'])) {
                        $project->faqs()->create([
                            'question_ar' => $faqData['question_ar'],
                            'answer_ar' => $faqData['answer_ar'],
                            'question_en' => $faqData['question_en'],
                            'answer_en' => $faqData['answer_en'],
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e); // As per instructions for 500 error debugging
            return back()->with('error', 'Error creating project: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Project $project)
    {
        $project->load(['amenities', 'faqs', 'propertyModels', 'masterProject']);

        $developers = Developer::where('is_active', true)->orderBy('name')->get();
        $countries = Country::all();
        $amenities = Amenity::where('amenity_type', 'project')
                            ->orWhere('amenity_type', 'both')
                            ->get(); // Grouping in view if needed

        $existingProjects = Project::where('id', '!=', $project->id)->select('id', 'name_en', 'name_ar', 'name')->get();

        return view('admin.projects.edit', compact('project', 'developers', 'countries', 'amenities', 'existingProjects'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $slugBase = $data['name_en'] ?? $data['name_ar'];
            if (empty($project->slug)) {
                $data['slug'] = Project::generateSlug($slugBase, $project->id);
            }

             // Fallback for 'name' legacy column
            $data['name'] = $data['name_en'] ?? $data['name_ar'];

            $project->update($data);

            // Amenities
            if ($request->has('amenities')) {
                $project->amenities()->sync($request->amenities);
            } else {
                $project->amenities()->detach();
            }

            // FAQs - Replace Strategy
            $project->faqs()->delete();
            if ($request->has('faqs')) {
                foreach ($request->faqs as $index => $faqData) {
                    if (!empty($faqData['question_en']) || !empty($faqData['question_ar'])) {
                        $project->faqs()->create([
                            'question_ar' => $faqData['question_ar'],
                            'answer_ar' => $faqData['answer_ar'],
                            'question_en' => $faqData['question_en'],
                            'answer_en' => $faqData['answer_en'],
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            return back()->with('error', 'Error updating project: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Project $project)
    {
        // Check dependencies if needed
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }
}
