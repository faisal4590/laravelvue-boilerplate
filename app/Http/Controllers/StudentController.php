<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StudentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        return Inertia::render('Students/Index', [
            'students' => Student::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('roll', 'like', "%{$search}%");
                })
                ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
            'can' => [
                'create' => auth()->user()->can('create', Student::class),
//                'edit' => auth()->user()->can('update', Student::class),
//                'delete' => auth()->user()->can('delete', Student::class),
                'edit' => true,
                'delete' => true,
                'bulk_delete' => auth()->user()->can('bulkDelete', Student::class),
            ]
        ]);
    }

    public function store(StudentRequest $request)
    {
        $this->authorize('create', Student::class);

        $student = Student::create($request->validated());

        return redirect()->back()->with([
            'message' => 'Student added successfully',
            'type' => 'success'
        ]);
    }

    public function update(StudentRequest $request, Student $student)
    {
        $this->authorize('update', $student);

        $student->update($request->validated());
        Cache::tags(['students'])->flush();

        return redirect()->back()->with([
            'message' => 'Student updated successfully',
            'type' => 'success'
        ]);
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();
        Cache::tags(['students'])->flush();

        return redirect()->back()->with([
            'message' => 'Student deleted successfully',
            'type' => 'success'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $this->authorize('bulkDelete', Student::class);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id'
        ]);

        Student::whereIn('id', $validated['ids'])->delete();
        Cache::tags(['students'])->flush();

        return redirect()->back()->with([
            'message' => 'Selected students deleted successfully',
            'type' => 'success'
        ]);
    }
}