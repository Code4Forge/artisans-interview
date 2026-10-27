<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Students\StoreStudentRequest;
use App\Http\Requests\Admin\Students\UpdateStudentRequest;
use App\Models\User;
use App\Policies\StudentPolicy;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function __construct(
        private StudentRepositoryInterface $students,
        private StudentService $studentService,
    ) {}

    public function index(): View|JsonResponse
    {
        $this->authorizeStudent('viewAny');

        if (request()->ajax()) {
            return DataTables::eloquent($this->students->queryForUser(request()->user()))
                ->addColumn('parents_list', function (User $student): string {
                    if ($student->parents->isEmpty()) {
                        return '<span class="text-xs text-slate-400 italic">None</span>';
                    }

                    return $student->parents->map(function (User $parent): string {
                        $relation = $parent->pivot?->relationship ?: 'Parent';
                        $phone = $parent->phone ? ' • '.e($parent->phone) : '';

                        return '<span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 mr-1 mb-1">'
                            .e($parent->name)
                            .' <span class="text-slate-400">('.e($relation).$phone.')</span></span>';
                    })->implode('');
                })
                ->addColumn('actions', function (User $student): string {
                    $actions = '<div class="flex items-center justify-center gap-1.5">';
                    if (request()->user()->can('students.update')) {
                        $actions .= '<a href="'.route('admin.students.edit', $student).'" title="Edit student" class="action-btn action-btn-edit">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>';
                        $actions .= '</a>';
                    }
                    if (request()->user()->can('students.delete')) {
                        $actions .= '<form method="POST" action="'.route('admin.students.destroy', $student).'" class="inline ajax-delete-form" onsubmit="return confirmDelete(event, \'This will permanently delete this student account and unlinked parents.\')">';
                        $actions .= csrf_field();
                        $actions .= method_field('DELETE');
                        $actions .= '<button type="submit" title="Delete student" class="action-btn action-btn-delete">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>';
                        $actions .= '</button>';
                        $actions .= '</form>';
                    }
                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['parents_list', 'actions'])
                ->toJson();
        }

        return view('admin.students.index');
    }

    public function create(): View
    {
        $this->authorizeStudent('create');

        return view('admin.students.create');
    }

    public function store(StoreStudentRequest $request): RedirectResponse|JsonResponse
    {
        $student = $this->studentService->create(
            $request->studentAttributes(),
            $request->parentsData(),
            $request->user()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'student' => $student,
            ]);
        }

        return redirect()
            ->route('admin.students.index')
            ->with('status', 'Student created.');
    }

    public function show(User $student): RedirectResponse|JsonResponse
    {
        $this->authorizeStudent('view', $student);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'student' => $student->load(['parents']),
            ]);
        }

        return redirect()->route('admin.students.edit', $student);
    }

    public function edit(User $student): View|JsonResponse
    {
        $this->authorizeStudent('update', $student);

        $student->load(['parents']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'student' => $student,
            ]);
        }

        return view('admin.students.edit', [
            'student' => $student,
        ]);
    }

    public function update(UpdateStudentRequest $request, User $student): RedirectResponse|JsonResponse
    {
        $this->studentService->update(
            $student,
            $request->studentAttributes(),
            $request->parentsData(),
            $request->user()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
                'student' => $student->fresh(['teacher', 'parents']),
            ]);
        }

        return redirect()
            ->route('admin.students.index')
            ->with('status', 'Student updated.');
    }

    public function destroy(User $student): RedirectResponse|JsonResponse
    {
        $this->authorizeStudent('delete', $student);

        try {
            $this->studentService->delete($student);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.students.index')
                ->with('status', 'Student deleted.');
        } catch (\Throwable $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Cannot delete this student.',
                ], 422);
            }

            return redirect()
                ->route('admin.students.index')
                ->with('error', $e->getMessage());
        }
    }

    private function authorizeStudent(string $ability, ?User $student = null): void
    {
        $policy = app(StudentPolicy::class);
        $allowed = $student === null
            ? $policy->{$ability}(request()->user())
            : $policy->{$ability}(request()->user(), $student);

        abort_unless($allowed, 403);
    }
}
