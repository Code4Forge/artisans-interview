<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Enums\AnnouncementAudience;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Announcements\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Services\AnnouncementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class AnnouncementController extends Controller
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcements,
        private AnnouncementService $announcementService,
    ) {}

    public function index(): View|JsonResponse
    {
        $this->authorizeAnnouncement('viewAny');

        if (request()->ajax()) {
            return DataTables::eloquent($this->announcements->queryForUser(request()->user()))
                ->editColumn('title', fn (Announcement $a) => '<a href="'.route('admin.announcements.show', $a).'" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">'.e($a->title).'</a>')
                ->addColumn('author_name', fn (Announcement $a) => $a->author?->name ?? '—')
                ->addColumn('audience_label', function (Announcement $a): string {
                    $color = match ($a->audience) {
                        AnnouncementAudience::Teachers => 'bg-amber-100 text-amber-800',
                        AnnouncementAudience::Students => 'bg-blue-100 text-blue-800',
                        AnnouncementAudience::Parents => 'bg-purple-100 text-purple-800',
                        AnnouncementAudience::Both => 'bg-emerald-100 text-emerald-800',
                        AnnouncementAudience::Custom => 'bg-indigo-100 text-indigo-800',
                    };

                    return '<span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold '.$color.'">'.$a->audience->label().'</span>';
                })
                ->addColumn('has_attachment', function (Announcement $a): string {
                    if (! $a->attachment_path) {
                        return '<span class="text-xs text-slate-400">—</span>';
                    }

                    return '<a href="'.route('admin.announcements.download', $a).'" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        '.e($a->attachment_name ?: 'File').'
                    </a>';
                })
                ->addColumn('formatted_created_at', fn (Announcement $a) => $a->created_at?->format('M d, Y h:i A') ?? '—')
                ->addColumn('actions', function (Announcement $a): string {
                    $user = request()->user();
                    $actions = '<div class="flex items-center justify-center gap-1.5">';

                    $actions .= '<a href="'.route('admin.announcements.show', $a).'" title="View details" class="action-btn action-btn-view">';
                    $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
                    $actions .= '</a>';

                    $canDelete = $user->can('announcements.delete') && ($user->hasRole('admin') || (int) $a->author_id === (int) $user->id);
                    if ($canDelete) {
                        $actions .= '<form method="POST" action="'.route('admin.announcements.destroy', $a).'" class="inline ajax-delete-form" onsubmit="return confirmDelete(event, \'This will permanently delete this announcement.\')">';
                        $actions .= csrf_field();
                        $actions .= method_field('DELETE');
                        $actions .= '<button type="submit" title="Delete announcement" class="action-btn action-btn-delete">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>';
                        $actions .= '</button>';
                        $actions .= '</form>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['title', 'audience_label', 'has_attachment', 'actions'])
                ->toJson();
        }

        return view('admin.announcements.index');
    }

    public function create(): View
    {
        $this->authorizeAnnouncement('create');

        return view('admin.announcements.create', [
            'audiences' => $this->availableAudiences(),
            'customUsers' => $this->availableCustomUsers(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse|JsonResponse
    {
        $announcement = $this->announcementService->create(
            $request->announcementAttributes(),
            $request->attachmentFile(),
            $request->user(),
            $request->recipientIds()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement posted and emails queued successfully.',
                'announcement' => $announcement,
            ]);
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Announcement posted successfully.');
    }

    public function show(Announcement $announcement): View|JsonResponse
    {
        $this->authorizeAnnouncement('view', $announcement);

        $announcement->load(['author.roles', 'recipients.roles']);
        $audienceUsers = $announcement->audienceUsers();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'announcement' => $announcement,
                'audience_users' => $audienceUsers,
            ]);
        }

        return view('admin.announcements.show', [
            'announcement' => $announcement,
            'audienceUsers' => $audienceUsers,
        ]);
    }

    public function destroy(Announcement $announcement): RedirectResponse|JsonResponse
    {
        $this->authorizeAnnouncement('delete', $announcement);

        try {
            $this->announcementService->delete($announcement);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Announcement deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.announcements.index')
                ->with('status', 'Announcement deleted.');
        } catch (\Throwable $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Cannot delete announcement.',
                ], 422);
            }

            return redirect()
                ->route('admin.announcements.index')
                ->with('error', $e->getMessage());
        }
    }

    public function recipientsSearch(): JsonResponse
    {
        $currentUser = request()->user();
        $query = $this->customUsersBaseQuery($currentUser);

        $search = trim((string) request('search', ''));
        $role = trim((string) request('role', 'all'));

        if ($role !== 'all') {
            $query->role($role);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'email']);

        $results = $users->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->roles->first()?->name ?? 'user',
        ]);

        return response()->json($results);
    }

    public function download(Announcement $announcement): StreamedResponse
    {
        $this->authorizeAnnouncement('view', $announcement);

        abort_unless($announcement->attachment_path && Storage::disk('public')->exists($announcement->attachment_path), 404);

        return Storage::disk('public')->download(
            $announcement->attachment_path,
            $announcement->attachment_name ?: 'attachment'
        );
    }

    private function availableAudiences(): array
    {
        $user = request()->user();

        // Admin can send to Teachers, Students, Parents, Both, or Custom
        if ($user->hasRole('admin')) {
            return [
                AnnouncementAudience::Teachers->value => AnnouncementAudience::Teachers->label(),
                AnnouncementAudience::Students->value => AnnouncementAudience::Students->label(),
                AnnouncementAudience::Parents->value => AnnouncementAudience::Parents->label(),
                AnnouncementAudience::Both->value => AnnouncementAudience::Both->label(),
                AnnouncementAudience::Custom->value => AnnouncementAudience::Custom->label(),
            ];
        }

        // Teachers can send to Students, Parents, Both, or Custom
        return [
            AnnouncementAudience::Students->value => AnnouncementAudience::Students->label(),
            AnnouncementAudience::Parents->value => AnnouncementAudience::Parents->label(),
            AnnouncementAudience::Both->value => AnnouncementAudience::Both->label(),
            AnnouncementAudience::Custom->value => AnnouncementAudience::Custom->label(),
        ];
    }

    /**
     * @return Builder
     */
    private function customUsersBaseQuery(User $currentUser)
    {
        return User::query()->with('roles')->where('id', '!=', $currentUser->id);
    }

    /**
     * Users available for custom audience selection.
     *
     * @return Collection<int, User>
     */
    private function availableCustomUsers(?Announcement $announcement = null)
    {
        $currentUser = request()->user();

        return $this->customUsersBaseQuery($currentUser)
            ->orderBy('name')
            ->get();
    }

    private function authorizeAnnouncement(string $ability, ?Announcement $announcement = null): void
    {
        $policy = app(AnnouncementPolicy::class);
        $allowed = $announcement === null
            ? $policy->{$ability}(request()->user())
            : $policy->{$ability}(request()->user(), $announcement);

        abort_unless($allowed, 403);
    }
}
