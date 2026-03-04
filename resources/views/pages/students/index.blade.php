<?php

use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    public function with(): array
    {
        return [
            'students' => Student::latest()->paginate(10),
        ];
    }

    public function deleteStudent(Student $student): void
    {
        $student->delete();
        $this->dispatch('student-deleted');
    }
};
?>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <flux:heading size="xl" level="1">Students</flux:heading>
            <flux:button variant="primary" href="{{ route('students.create') }}" wire:navigate>Add Student</flux:button>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Major</flux:table.column>
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
                                <div class="flex gap-2">
                                    <flux:button variant="ghost" size="sm" href="{{ route('students.edit', $student) }}" wire:navigate>Edit</flux:button>
                                    
                                    <flux:modal.trigger :name="'delete-student-'.$student->id">
                                        <flux:button variant="danger" size="sm">Delete</flux:button>
                                    </flux:modal.trigger>

                                    <flux:modal :name="'delete-student-'.$student->id" class="min-w-[22rem]">
                                        <form wire:submit="deleteStudent({{ $student->id }})">
                                            <flux:heading size="lg">Delete Student?</flux:heading>
                                            <flux:text class="mt-2 text-sm text-neutral-500">
                                                Are you sure you want to delete {{ $student->first_name }} {{ $student->last_name }}? This action cannot be undone.
                                            </flux:text>
                                            
                                            <div class="mt-4 flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>
                                                <flux:button type="submit" variant="danger">Delete</flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
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
