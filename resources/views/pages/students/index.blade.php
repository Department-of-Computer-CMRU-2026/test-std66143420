<?php

use App\Models\Student;
use Livewire\Attributes\On;
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
                ->when($this->search, function ($query) {
                    $query->where('student_id', 'like', '%' . $this->search . '%')
                          ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('major', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(20),
        ];
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('swal:confirm-delete', [
            'title' => 'Are you sure?',
            'text' => 'You won\'t be able to revert this!',
            'icon' => 'warning',
            'id' => $id
        ]);
    }

    #[On('delete-student')]
    public function deleteStudent(int $id): void
    {
        Student::findOrFail($id)->delete();
        $this->dispatch('swal:alert', [
            'title' => 'Deleted!',
            'text' => 'The student has been deleted.',
            'icon' => 'success'
        ]);
        $this->dispatch('student-deleted');
    }

    public function toggleStatus(int $id): void
    {
        $student = Student::findOrFail($id);
        
        $this->dispatch('swal:confirm-status', [
            'title' => 'Change Status?',
            'text' => "Do you want to change the status of {$student->first_name}?",
            'icon' => 'question',
            'id' => $id,
            'current' => $student->is_active
        ]);
    }

    #[On('change-status')]
    public function updateStatus(int $id): void
    {
        $student = Student::findOrFail($id);
        $student->update(['is_active' => !$student->is_active]);
        
        $this->dispatch('swal:alert', [
            'title' => 'Updated!',
            'text' => 'Student status has been changed.',
            'icon' => 'success'
        ]);
    }
};
?>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl"
        x-data="{
            init() {
                Livewire.on('swal:confirm-delete', (event) => {
                    const data = event[0];
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.dispatch('delete-student', { id: data.id });
                        }
                    });
                });

                Livewire.on('swal:confirm-status', (event) => {
                    const data = event[0];
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                        showCancelButton: true,
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.dispatch('change-status', { id: data.id });
                        }
                    });
                });

                Livewire.on('swal:alert', (event) => {
                    const data = event[0];
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            }
        }"
    >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <flux:heading size="xl" level="1">Students</flux:heading>
            
            <div class="flex items-center gap-4">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search students..." class="w-full sm:w-64" clearable />
                <flux:button variant="primary" href="{{ route('students.create') }}" wire:navigate class="whitespace-nowrap">Add Student</flux:button>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Major</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell>{{ $student->student_id }}</flux:table.cell>
                            <flux:table.cell>{{ $student->first_name }} {{ $student->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $student->email }}</flux:table.cell>
                            <flux:table.cell>{{ $student->major }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:switch wire:click="toggleStatus({{ $student->id }})" :checked="$student->is_active" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button variant="ghost" size="sm" href="{{ route('students.edit', $student) }}" wire:navigate>Edit</flux:button>
                                    <flux:button variant="danger" size="sm" wire:click="confirmDelete({{ $student->id }})">Delete</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
