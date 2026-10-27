<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Teachers\StoreTeacherRequest;
use App\Http\Requests\Admin\Teachers\UpdateTeacherRequest;
use App\Models\User;
use App\Policies\TeacherPolicy;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TeacherService $teacherService,
    ) {}

    public function index(): View|JsonResponse
    {
        $this->authorizeTeacher('viewAny');

        if (request()->ajax()) {
            return DataTables::eloquent($this->users->queryByRole('teacher'))
                ->addColumn('actions', function (User $teacher): string {
                    $actions = '<div class="flex items-center justify-center gap-1.5">';
                    if (request()->user()->can('teachers.update')) {
                        $actions .= '<a href="'.route('admin.teachers.edit', $teacher).'" title="Edit teacher" class="action-btn action-btn-edit">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>';
                        $actions .= '</a>';
                    }
                    if (request()->user()->can('teachers.delete')) {
                        $actions .= '<form method="POST" action="'.route('admin.teachers.destroy', $teacher).'" class="inline ajax-delete-form" onsubmit="return confirmDelete(event, \'This will permanently delete this teacher account.\')">';
                        $actions .= csrf_field();
                        $actions .= method_field('DELETE');
                        $actions .= '<button type="submit" title="Delete teacher" class="action-btn action-btn-delete">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>';
                        $actions .= '</button>';
                        $actions .= '</form>';
                    }
                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.teachers.index', [
            'teachers' => $this->users->paginateByRole('teacher'),
        ]);
    }

    public function create(): View
    {
        $this->authorizeTeacher('create');

        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse|JsonResponse
    {
        $teacher = $this->teacherService->create($request->teacherAttributes());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully.',
                'teacher' => $teacher,
            ]);
        }

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', 'Teacher created.');
    }

    public function show(User $teacher): RedirectResponse|JsonResponse
    {
        $this->authorizeTeacher('view', $teacher);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'teacher' => $teacher,
            ]);
        }

        return redirect()->route('admin.teachers.edit', $teacher);
    }

    public function edit(User $teacher): View|JsonResponse
    {
        $this->authorizeTeacher('update', $teacher);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'teacher' => $teacher,
            ]);
        }

        return view('admin.teachers.edit', [
            'teacher' => $teacher,
        ]);
    }

    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse|JsonResponse
    {
        $this->teacherService->update($teacher, $request->teacherAttributes());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully.',
                'teacher' => $teacher->fresh(),
            ]);
        }

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', 'Teacher updated.');
    }

    public function destroy(User $teacher): RedirectResponse|JsonResponse
    {
        $this->authorizeTeacher('delete', $teacher);

        try {
            $this->teacherService->delete($teacher);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Teacher deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.teachers.index')
                ->with('status', 'Teacher deleted.');
        } catch (\Throwable $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Cannot delete this teacher.',
                ], 422);
            }

            return redirect()
                ->route('admin.teachers.index')
                ->with('error', $e->getMessage());
        }
    }

    private function authorizeTeacher(string $ability, ?User $teacher = null): void
    {
        $policy = app(TeacherPolicy::class);
        $allowed = $teacher === null
            ? $policy->{$ability}(request()->user())
            : $policy->{$ability}(request()->user(), $teacher);

        abort_unless($allowed, 403);
    }
}
