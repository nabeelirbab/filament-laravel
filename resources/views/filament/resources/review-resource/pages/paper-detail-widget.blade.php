<div class="bg-white shadow rounded-lg p-6">
    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-2">{{ $this->record->paper->title }}</h2>
        <p class="text-gray-600 mb-4">Submitted by: {{ $this->record->paper->author->name }}</p>
    </div>

    <div class="mb-4 ">
        <h3 class="font-semibold text-gray-800">Abstract:</h3>
        <p class="text-gray-700 mt-1">{{ $this->record->paper->abstract }}</p>
    </div>

    <div class="mb-4">
        <h3 class="font-semibold text-gray-800">Status:</h3>
        <p class="text-gray-700 mt-1 capitalize">{{ $this->record->paper->status }}</p>
    </div>

    <div class="mb-4">
        <h3 class="font-semibold text-gray-800">Submitted On:</h3>
        <p class="text-gray-700 mt-1">{{ $this->record->paper->created_at->format('M d, Y') }}</p>
    </div>

    <div class="mb-4">
        <h3 class="font-semibold text-gray-800">Paper File:</h3>
        <a href="{{ Storage::url($this->record->paper->file_path) }}" class="text-blue-600 hover:text-blue-800 underline" target="_blank">View Paper</a>
    </div>
    
    <div class="mt-6">
        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Assign Reviewers
        </button>
    </div>
</div>