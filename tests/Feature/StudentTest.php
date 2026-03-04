<?php

use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('unauthenticated users cannot view students', function () {
    $this->get(route('students.index'))
         ->assertRedirect(route('login'));
});

test('authenticated users can view students', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
         ->get(route('students.index'))
         ->assertOk();
});

test('authenticated users can view create form', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
         ->get(route('students.create'))
         ->assertOk();
});

test('authenticated users can create a student', function () {
    $user = User::factory()->create();
    
    Livewire::actingAs($user)
        ->test('pages::students.create')
        ->set('student_id', 'STD-9999')
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '1234567890')
        ->set('major', 'Computer Science')
        ->call('save')
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', [
        'student_id' => 'STD-9999',
        'email' => 'john@example.com',
    ]);
});

test('authenticated users can edit a student', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    
    Livewire::actingAs($user)
        ->test('pages::students.edit', ['student' => $student])
        ->set('first_name', 'Jane')
        ->set('email', 'jane@example.com')
        ->call('save')
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'first_name' => 'Jane',
        'email' => 'jane@example.com',
    ]);
});

test('authenticated users can delete a student', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    
    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->call('confirmDelete', $student->id)
        ->assertDispatched('swal:confirm-delete');
        
    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->call('deleteStudent', $student->id)
        ->assertDispatched('student-deleted')
        ->assertDispatched('swal:alert');

    $this->assertDatabaseMissing('students', [
        'id' => $student->id,
    ]);
});

test('authenticated users can toggle student status', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['is_active' => true]);
    
    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->call('toggleStatus', $student->id)
        ->assertDispatched('swal:confirm-status');
        
    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->call('updateStatus', $student->id)
        ->assertDispatched('swal:alert');

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'is_active' => false,
    ]);
});

test('authenticated users can search students', function () {
    $user = User::factory()->create();
    
    // Create specific students to search for
    Student::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.com']);
    Student::factory()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'email' => 'bob@example.com']);
    Student::factory()->create(['first_name' => 'Charlie', 'last_name' => 'Brown', 'email' => 'charlie@example.com']);

    // Search for Alice
    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('search', 'Alice')
        ->assertSee('Alice')
        ->assertDontSee('Bob')
        ->assertDontSee('Charlie');

    // Search for Jones (Bob)
    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('search', 'Jones')
        ->assertSee('Bob')
        ->assertDontSee('Alice');
});
