<x-filament-panels::page>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-4">
            <h2 class="text-xl font-semibold mb-2">{{ $this->record->title }}</h2>
            <p class="text-gray-600 mb-4">Submitted by: {{ $this->record->author->name }}</p>
        </div>

        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">Abstract:</h3>
            <p class="text-gray-700 mt-1">{{ $this->record->abstract }}</p>
        </div>

        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">Status:</h3>
            <p class="text-gray-700 mt-1 capitalize">{{ $this->record->status }}</p>
        </div>

        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">Submitted On:</h3>
            <p class="text-gray-700 mt-1">{{ $this->record->created_at->format('d.m.Y') }}</p>
        </div>

        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">Paper File:</h3>
            <a href="{{ Storage::url($this->record->file_path) }}" class="text-blue-600 hover:text-blue-800 underline" target="_blank">View Paper</a>
        </div>

        <!-- Referee Comments and Decisions Section -->
        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">Referee Comments & Decisions:</h3>
            @if ($this->record->reviews->isNotEmpty())
                <ul class="space-y-4 mt-2">
                    @foreach ($this->record->reviews as $review)
                        <li class="border border-gray-300 p-4 rounded-lg">
                            <p><strong>Referee:</strong> {{ $review->referee->name ?? 'N/A' }}</p>
                            <p><strong>Decision:</strong> {{ ucfirst($review->decision) }}</p>
                            <p><strong>Comments:</strong> {{ $review->comments ?? 'No comments provided' }}</p>
                            <p class="text-gray-500 text-sm mt-1"><strong>Reviewed On:</strong> {{ $review->created_at->format('d.m.Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600 mt-2">No reviews submitted yet.</p>
            @endif
        </div>

        <div class="mt-6">
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Assign Reviewers
            </button>
        </div>
    </div>
</x-filament-panels::page>
