<?php

use App\Models\Student;
use Livewire\Component;

new class extends Component {
    public Student $student;

    public function mount(Student $student): void
    {
        // Only allow viewing active students
        if (! $student->is_active) {
            abort(404);
        }
        
        $this->student = $student;
    }
};
?>

<div>
    <div class="mb-6 flex justify-between items-center">
        <flux:button href="{{ route('directory') }}" wire:navigate icon="arrow-left" variant="ghost">Back to Directory</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Sidebar Profile Card -->
        <flux:card class="md:col-span-1 flex flex-col items-center text-center gap-4">
            <flux:avatar size="xl" :name="$student->first_name . ' ' . $student->last_name" class="size-32 text-3xl" />
            
            <div class="space-y-1">
                <flux:heading size="xl">{{ $student->first_name }} {{ $student->last_name }}</flux:heading>
                <flux:text class="text-neutral-500 font-medium">{{ $student->student_id }}</flux:text>
            </div>

            <flux:badge size="lg" color="zinc">{{ $student->major }}</flux:badge>

            <flux:separator class="w-full my-2" />
            
            <div class="w-full space-y-3 text-start">
                <div class="flex items-center gap-3 text-neutral-600 dark:text-neutral-400">
                    <flux:icon.envelope class="size-5" />
                    <a href="mailto:{{ $student->email }}" class="hover:underline">{{ $student->email }}</a>
                </div>
                
                @if ($student->phone)
                    <div class="flex items-center gap-3 text-neutral-600 dark:text-neutral-400">
                        <flux:icon.phone class="size-5" />
                        <a href="tel:{{ $student->phone }}" class="hover:underline">{{ $student->phone }}</a>
                    </div>
                @endif
            </div>
        </flux:card>

        <!-- Main Details Area -->
        <div class="md:col-span-2 space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Student Information</flux:heading>
                
                <dl class="divide-y divide-neutral-200 dark:divide-neutral-800">
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Full name</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">{{ $student->first_name }} {{ $student->last_name }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Student ID</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">{{ $student->student_id }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Major / Program</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">{{ $student->major }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Email address</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">{{ $student->email }}</dd>
                    </div>
                    @if ($student->phone)
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Phone number</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">{{ $student->phone }}</dd>
                    </div>
                    @endif
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Status</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">
                            <flux:badge size="sm" color="green" icon="check-circle">Active</flux:badge>
                        </dd>
                    </div>
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm/6 font-medium text-neutral-900 dark:text-white">Enrolled On</dt>
                        <dd class="mt-1 text-sm/6 text-neutral-700 dark:text-neutral-300 sm:col-span-2 sm:mt-0">
                            {{ $student->created_at->format('F j, Y') }}
                        </dd>
                    </div>
                </dl>
            </flux:card>
        </div>
    </div>
</div>
