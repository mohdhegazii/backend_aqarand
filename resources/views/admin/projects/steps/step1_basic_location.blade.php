<div class="space-y-6">
    <h3 class="text-lg font-bold text-gray-800">البيانات الأساسية</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700">اسم المشروع (عربي)</label>
            <input
                type="text"
                name="name_ar"
                value="{{ old('name_ar', $project->name_ar ?? '') }}"
                required
                class="form-control form-control-lg w-full rounded border-gray-300"
                placeholder="مثال: كمبوند المقصد العاصمة الإدارية"
            />
            <p class="text-xs text-gray-500 mt-1">يُستخدم في الـ SEO العربي والعنوان التعريفي.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700">اسم المشروع (إنجليزي)</label>
            <input
                type="text"
                name="name_en"
                value="{{ old('name_en', $project->name_en ?? '') }}"
                required
                class="form-control form-control-lg w-full rounded border-gray-300"
                placeholder="مثال: Al Maqsad New Capital"
            />
            <p class="text-xs text-gray-500 mt-1">يُستخدم في الـ SEO الإنجليزي والعنوان التعريفي الإنجليزي.</p>
        </div>
    </div>
</div>
