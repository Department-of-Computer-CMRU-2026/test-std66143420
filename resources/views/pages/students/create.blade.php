<?php

use App\Models\Student;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|string|unique:students,student_id')]
    public string $student_id = '';

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email|unique:students,email')]
    public string $email = '';

    #[Validate('nullable|string')]
    public string $phone = '';

    #[Validate('required|string|max:255')]
    public string $major = '';

    public function save(): void
    {
        $validated = $this->validate();

        Student::create($validated);

        $this->redirect(route('students.index'), navigate: true);
    }
};
?>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('students.index') }}" wire:navigate class="hidden sm:inline-flex" />
            <flux:heading size="xl" level="1">Add Student</flux:heading>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 max-w-2xl">
            <form wire:submit="save" class="space-y-6">
                
                <flux:input wire:model="student_id" label="Student ID" placeholder="STD-1234" required />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <flux:input wire:model="first_name" label="First Name" required />
                    <flux:input wire:model="last_name" label="Last Name" required />
                </div>

                <flux:input wire:model="email" type="email" label="Email Address" required />
                
                <flux:input wire:model="phone" type="tel" label="Phone Number" />

                <flux:select wire:model="major" label="Major" placeholder="Select major" required>
                    <flux:select.option value="Computer Science">Computer Science</flux:select.option>
                    <flux:select.option value="Information Technology">Information Technology</flux:select.option>
                    <flux:select.option value="Software Engineering">Software Engineering</flux:select.option>
                    <flux:select.option value="Data Science">Data Science</flux:select.option>
                </flux:select>

                <div class="flex gap-2 pt-4 justify-end">
                    <flux:button href="{{ route('students.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Create Student</flux:button>
                </div>
            </form>
        </div>
    </div>
