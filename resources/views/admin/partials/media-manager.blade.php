@props(['model', 'collection' => 'default', 'title' => 'Media Gallery'])

<div class="bg-white p-6 rounded-lg shadow mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
        <button type="button" onclick="document.getElementById('upload-{{ $collection }}').click()"
                class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">
            @lang('admin.upload_files')
        </button>
        <input type="file" id="upload-{{ $collection }}" class="hidden" multiple
               onchange="uploadFiles('{{ $collection }}', this.files)">
    </div>

    <div id="gallery-{{ $collection }}" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($model->getMedia($collection) as $media)
            <div class="relative group border rounded overflow-hidden">
                @if(str_starts_with($media->mime_type, 'image/'))
                    <img src="{{ $media->url }}" class="w-full h-32 object-cover">
                @else
                    <div class="w-full h-32 flex items-center justify-center bg-gray-100 text-gray-500">
                        <span class="text-xs uppercase">{{ $media->extension }}</span>
                    </div>
                @endif

                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center space-x-2">
                    <a href="{{ $media->url }}" target="_blank" class="text-white hover:text-indigo-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <button onclick="deleteMedia({{ $media->id }})" class="text-red-500 hover:text-red-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>

                @if($media->is_private)
                    <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded-bl">Private</div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
function uploadFiles(collection, files) {
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('file', files[i]);
    }
    formData.append('collection', collection);

    // Add CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // This route should be dynamic in a real application or passed via props
    // For now we assume a general endpoint or entity specific one
    fetch('{{ route("admin.projects.media.store", $model->id) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            window.location.reload();
        } else {
            alert('Upload failed: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteMedia(id) {
    if(!confirm('Are you sure you want to delete this file?')) return;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/admin/media/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if(response.ok) {
            window.location.reload();
        } else {
            alert('Delete failed.');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
