<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-4">
        {{ $this->form }}

        <x-filament::button type="submit" form="submit" class="w-full">
            Register
        </x-filament::button>
    </form>

    @if (session()->has('success'))
        <div class="mt-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif
</x-filament::page>
