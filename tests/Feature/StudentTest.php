<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

test('authenticated users can export students', function () {
    $user = User::factory()->create();
    Student::factory()->count(5)->create();

    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->call('export')
        ->assertFileDownloaded('students.csv');
});

test('authenticated users can import students', function () {
    $user = User::factory()->create();
    
    $csvContent = "ID,Student ID,First Name,Last Name,Email,Phone,Major,Active\n" .
                  ",STD-8888,Test,User,test8888@example.com,1234567890,IT,Yes\n";
                  
    $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('importFile', $file)
        ->call('import')
        ->assertDispatched('swal:alert');

    $this->assertDatabaseHas('students', [
        'student_id' => 'STD-8888',
        'first_name' => 'Test',
        'email' => 'test8888@example.com'
    ]);
});

test('authenticated users can bulk delete students', function () {
    $user = User::factory()->create();
    $students = Student::factory()->count(3)->create();
    $studentIds = $students->pluck('id')->map(fn($id) => (string) $id)->toArray();

    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('selectedStudents', $studentIds)
        ->call('confirmBulkDelete')
        ->assertDispatched('swal:confirm-bulk-delete');

    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('selectedStudents', $studentIds)
        ->call('bulkDeleteStudents')
        ->assertDispatched('swal:alert');

    foreach ($students as $student) {
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
});

test('authenticated users can bulk activate students', function () {
    $user = User::factory()->create();
    $students = Student::factory()->count(2)->create(['is_active' => false]);
    $studentIds = $students->pluck('id')->map(fn($id) => (string) $id)->toArray();

    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('selectedStudents', $studentIds)
        ->call('bulkActivate')
        ->assertDispatched('swal:alert');

    foreach ($students as $student) {
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'is_active' => true
        ]);
    }
});

test('authenticated users can bulk deactivate students', function () {
    $user = User::factory()->create();
    $students = Student::factory()->count(2)->create(['is_active' => true]);
    $studentIds = $students->pluck('id')->map(fn($id) => (string) $id)->toArray();

    Livewire::actingAs($user)
        ->test('pages::students.index')
        ->set('selectedStudents', $studentIds)
        ->call('bulkDeactivate')
        ->assertDispatched('swal:alert');

    foreach ($students as $student) {
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'is_active' => false
        ]);
    }
});

test('authenticated users can view the student directory with cards', function () {
    $user = User::factory()->create();
    $student1 = Student::factory()->create(['is_active' => true, 'first_name' => 'ActiveStudent']);
    $student2 = Student::factory()->create(['is_active' => false, 'first_name' => 'InactiveStudent']);

    $response = $this->actingAs($user)->get(route('directory'));
    $response->assertOk();

    // Verify correct component is rendered and has card view wrapper classes
    Livewire::actingAs($user)
        ->test('pages::directory')
        ->assertSee($student1->first_name)
        ->assertDontSee($student2->first_name) // only active returned
        ->assertSee('Student Directory');
});

test('authenticated users can view a student profile detail', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->get(route('directory.show', $student));
    $response->assertOk();

    Livewire::actingAs($user)
        ->test('pages::directory.show', ['student' => $student])
        ->assertSee($student->first_name)
        ->assertSee($student->major)
        ->assertSee('Student Information');
});

test('authenticated users cannot view inactive student profile details', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user)->get(route('directory.show', $student));
    $response->assertNotFound();
});
