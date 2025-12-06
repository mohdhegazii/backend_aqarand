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
        $amenities = Amenity::whereIn('amenity_type', ['project', 'both'])
                            ->orderBy('sort_order')
                            ->orderBy('name_en')
                            ->get()
                            ->groupBy(fn ($amenity) => $amenity->amenity_type ?? 'other');

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
            $project->amenities()->sync($request->input('amenities', []));

            // FAQs
            $faqItems = collect($request->input('faqs', []))
                ->filter(function ($faq) {
                    return !empty(array_filter([
                        $faq['question_ar'] ?? null,
                        $faq['answer_ar'] ?? null,
                        $faq['question_en'] ?? null,
                        $faq['answer_en'] ?? null,
                    ], fn($value) => $value !== null && $value !== ''));
                });

            foreach ($faqItems as $index => $faqData) {
                $project->faqs()->create([
                    'question_ar' => $faqData['question_ar'] ?? null,
                    'answer_ar' => $faqData['answer_ar'] ?? null,
                    'question_en' => $faqData['question_en'] ?? null,
                    'answer_en' => $faqData['answer_en'] ?? null,
                    'sort_order' => $faqData['sort_order'] ?? $index,
                ]);
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
        $project->load(['amenities', 'faqs', 'propertyModels.unitType', 'masterProject']);

        $developers = Developer::where('is_active', true)->orderBy('name')->get();
        $countries = Country::all();
        $amenities = Amenity::whereIn('amenity_type', ['project', 'both'])
                            ->orderBy('sort_order')
                            ->orderBy('name_en')
                            ->get()
                            ->groupBy(fn ($amenity) => $amenity->amenity_type ?? 'other');

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
            $project->amenities()->sync($request->input('amenities', []));

            // FAQs - Update existing, add new, delete removed based on submitted IDs
            $existingFaqIds = $project->faqs()->pluck('id')->toArray();
            $keptFaqIds = [];

            $faqItems = collect($request->input('faqs', []))
                ->filter(function ($faq) {
                    return !empty(array_filter([
                        $faq['question_ar'] ?? null,
                        $faq['answer_ar'] ?? null,
                        $faq['question_en'] ?? null,
                        $faq['answer_en'] ?? null,
                    ], fn($value) => $value !== null && $value !== ''));
                })
                ->values();

            foreach ($faqItems as $index => $faqData) {
                $payload = [
                    'question_ar' => $faqData['question_ar'] ?? null,
                    'answer_ar' => $faqData['answer_ar'] ?? null,
                    'question_en' => $faqData['question_en'] ?? null,
                    'answer_en' => $faqData['answer_en'] ?? null,
                    'sort_order' => $faqData['sort_order'] ?? $index,
                ];

                if (!empty($faqData['id']) && in_array((int) $faqData['id'], $existingFaqIds, true)) {
                    $project->faqs()->where('id', $faqData['id'])->update($payload);
                    $keptFaqIds[] = (int) $faqData['id'];
                } else {
                    $newFaq = $project->faqs()->create($payload);
                    $keptFaqIds[] = $newFaq->id;
                }
            }

            // Detect removed FAQs by comparing submitted IDs to existing ones.
            $faqsToDelete = array_diff($existingFaqIds, $keptFaqIds);
            if (!empty($faqsToDelete)) {
                $project->faqs()->whereIn('id', $faqsToDelete)->delete();
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
