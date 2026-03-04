<?php

use App\Models\Student;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'students' => Student::query()
                ->where('is_active', true) // Only show active students to users
                ->when($this->search, function ($query) {
                    $query->where(function($q) {
                        $q->where('student_id', 'like', '%' . $this->search . '%')
                          ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('major', 'like', '%' . $this->search . '%');
                    });
                })
                ->latest()
                ->paginate(12),
        ];
    }
};
?>

<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <flux:heading size="xl" level="1">Student Directory</flux:heading>
        
        <div class="flex items-center gap-4">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search students..." class="w-full sm:w-64" clearable />
        </div>
    </div>

    @if ($students->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($students as $student)
                <a href="{{ route('directory.show', $student) }}" wire:navigate class="block outline-none focus:ring-2 focus:ring-zinc-500 rounded-xl">
                    <flux:card class="flex flex-col gap-4 hover:shadow-md transition-shadow h-full cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <flux:avatar size="lg" :name="$student->first_name . ' ' . $student->last_name" />
                                <div>
                                    <flux:heading size="lg" class="group-hover:text-blue-600 transition-colors">{{ $student->first_name }} {{ $student->last_name }}</flux:heading>
                                    <flux:text class="text-sm font-medium text-neutral-500">{{ $student->student_id }}</flux:text>
                                </div>
                            </div>
                            <flux:badge size="sm" color="zinc">{{ $student->major }}</flux:badge>
                        </div>

                        <flux:separator />

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                <flux:icon.envelope class="size-4" />
                                <span class="truncate">{{ $student->email }}</span>
                            </div>
                            @if ($student->phone)
                                <div class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                    <flux:icon.phone class="size-4" />
                                    <span>{{ $student->phone }}</span>
                                </div>
                            @endif
                        </div>
                    </flux:card>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $students->links() }}
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800">
            <flux:icon.users class="size-12 text-neutral-400 mb-4" />
            <flux:heading size="lg">No students found</flux:heading>
            <flux:text class="text-neutral-500">Try adjusting your search terms.</flux:text>
        </div>
    @endif
</div>
