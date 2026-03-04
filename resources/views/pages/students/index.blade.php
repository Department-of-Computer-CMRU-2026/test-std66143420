<?php

use App\Models\Student;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

new class extends Component {
    use WithPagination, WithFileUploads;

    #[Url(history: true)]
    public string $search = '';

    public $importFile;

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

    public function export()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=students.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Student ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Major', 'Active']);

            Student::query()
                ->when($this->search, function ($query) {
                    $query->where('student_id', 'like', '%' . $this->search . '%')
                          ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('major', 'like', '%' . $this->search . '%');
                })
                ->chunk(100, function($students) use($file) {
                    foreach ($students as $student) {
                        fputcsv($file, [
                            $student->id,
                            $student->student_id,
                            $student->first_name,
                            $student->last_name,
                            $student->email,
                            $student->phone,
                            $student->major,
                            $student->is_active ? 'Yes' : 'No'
                        ]);
                    }
                });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:csv,txt|max:10240',
        ]);

        $file = fopen($this->importFile->getRealPath(), 'r');
        $isHeader = true;
        $count = 0;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            if ($isHeader) {
                $isHeader = false;
                continue;
            }

            if (count($row) >= 7) {
                Student::updateOrCreate(
                    ['student_id' => $row[1] ?? ''],
                    [
                        'first_name' => $row[2] ?? '',
                        'last_name' => $row[3] ?? '',
                        'email' => $row[4] ?? '',
                        'phone' => $row[5] ?? null,
                        'major' => $row[6] ?? '',
                        'is_active' => isset($row[7]) ? (strtolower($row[7]) === 'yes' || $row[7] === '1') : true,
                    ]
                );
                $count++;
            }
        }

        fclose($file);
        $this->reset('importFile');
        $this->dispatch('close-modal');
        $this->dispatch('swal:alert', [
            'title' => 'Imported!',
            'text' => "$count students imported successfully.",
            'icon' => 'success'
        ]);
        $this->resetPage(); // Refresh table
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
            
            <div class="flex flex-wrap items-center gap-2">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search students..." class="w-full sm:w-auto" clearable />
                
                <flux:modal.trigger name="import-modal">
                    <flux:button variant="ghost" icon="document-arrow-up" class="hidden sm:inline-flex">Import</flux:button>
                </flux:modal.trigger>
                
                <flux:button wire:click="export" variant="ghost" icon="document-arrow-down" class="hidden sm:inline-flex">Export</flux:button>
                
                <flux:button variant="primary" href="{{ route('students.create') }}" wire:navigate class="whitespace-nowrap">Add Student</flux:button>

                <flux:modal name="import-modal" class="min-w-[22rem]">
                    <form wire:submit="import">
                        <flux:heading size="lg">Import Students CSV</flux:heading>
                        <flux:text class="mt-2 text-sm text-neutral-500">
                            Upload a CSV file containing student records. The file must contain a header row and these columns in order: <strong>ID, Student ID, First Name, Last Name, Email, Phone, Major, Active</strong>.
                        </flux:text>
                        
                        <div class="mt-4">
                            <input type="file" wire:model="importFile" accept=".csv" class="block w-full text-sm text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700 cursor-pointer" required />
                            @error('importFile') 
                                <flux:text class="mt-2 text-red-500">{{ $message }}</flux:text>
                            @enderror
                        </div>
                        
                        <div class="mt-6 flex gap-2">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">Cancel</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" variant="primary">Run Import</flux:button>
                        </div>
                    </form>
                </flux:modal>
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
