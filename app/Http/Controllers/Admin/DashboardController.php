<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Enums\AnnouncementAudience;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcements,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->primaryRole() !== null, 403);

        $role = $user->primaryRole();
        $canViewTeachers = $user->can('teachers.view');
        $canViewStudents = $user->can('students.view');
        $canViewAnnouncements = $user->can('announcements.view');
        $canCreateAnnouncements = $user->can('announcements.create');
        $canManageRoles = $user->can('roles.manage');

        $stats = [
            'role' => $role,
            'roleLabel' => $user->primaryRoleLabel(),
            'roleSummary' => $user->roleSummary(),
            'permissions' => [
                'teachers_view' => $canViewTeachers,
                'students_view' => $canViewStudents,
                'announcements_view' => $canViewAnnouncements,
                'announcements_create' => $canCreateAnnouncements,
                'roles_manage' => $canManageRoles,
            ],
            'counts' => [],
            'charts' => [],
            'recent' => [],
        ];

        // 1. Counts based on permissions
        if ($canViewTeachers) {
            $stats['counts']['teachers'] = User::role('teacher')->count();
        }

        if ($canViewStudents) {
            $stats['counts']['students'] = User::role('student')->count();
            $stats['counts']['parents'] = User::role('parent')->count();
        } elseif ($user->hasRole('parent')) {
            $stats['counts']['students'] = $user->children()->count();
        } elseif ($user->hasRole('student')) {
            $stats['counts']['parents'] = $user->parents()->count();
        }

        if ($canViewAnnouncements) {
            if ($user->hasRole('admin')) {
                $stats['counts']['announcements'] = Announcement::count();
            } elseif ($user->hasRole('teacher')) {
                $stats['counts']['my_announcements'] = Announcement::where('author_id', $user->id)->count();
                $stats['counts']['announcements'] = $this->announcements->queryForUser($user)->count();
            } else {
                $stats['counts']['announcements'] = $this->announcements->queryForUser($user)->count();
            }
        }

        if ($canManageRoles) {
            $stats['counts']['roles'] = Role::count();
        }

        // 2. Charts Data based on permissions & role
        if ($canViewAnnouncements) {
            // Audience breakdown query
            $audienceQuery = $user->hasRole('admin')
                ? Announcement::query()
                : ($user->hasRole('teacher')
                    ? Announcement::where('author_id', $user->id)
                    : $this->announcements->queryForUser($user));

            $rawAudienceCounts = (clone $audienceQuery)
                ->select('audience', DB::raw('count(*) as count'))
                ->groupBy('audience')
                ->pluck('count', 'audience')
                ->toArray();

            $stats['charts']['audience'] = [
                'labels' => ['Teachers', 'Students', 'Parents', 'Both', 'Custom'],
                'data' => [
                    (int) ($rawAudienceCounts[AnnouncementAudience::Teachers->value] ?? 0),
                    (int) ($rawAudienceCounts[AnnouncementAudience::Students->value] ?? 0),
                    (int) ($rawAudienceCounts[AnnouncementAudience::Parents->value] ?? 0),
                    (int) ($rawAudienceCounts[AnnouncementAudience::Both->value] ?? 0),
                    (int) ($rawAudienceCounts[AnnouncementAudience::Custom->value] ?? 0),
                ],
                'colors' => ['#f59e0b', '#3b82f6', '#a855f7', '#10b981', '#6366f1'],
            ];

            // Monthly announcements activity over last 6 months
            $monthlyLabels = [];
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $monthlyLabels[] = $monthDate->format('M Y');
                $monthlyData[] = (clone $audienceQuery)
                    ->whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month)
                    ->count();
            }

            $stats['charts']['monthly'] = [
                'labels' => $monthlyLabels,
                'data' => $monthlyData,
            ];

            // Recent announcements (up to 5)
            $stats['recent']['announcements'] = $this->announcements->queryForUser($user)
                ->with(['author'])
                ->limit(5)
                ->get();
        }

        // Admin-specific distributions
        if ($user->hasRole('admin')) {
            $stats['charts']['role_distribution'] = [
                'labels' => ['Admins', 'Teachers', 'Students', 'Parents'],
                'data' => [
                    User::role('admin')->count(),
                    User::role('teacher')->count(),
                    User::role('student')->count(),
                    User::role('parent')->count(),
                ],
                'colors' => ['#ef4444', '#f59e0b', '#3b82f6', '#a855f7'],
            ];
        }

        // Teacher-specific distributions
        if ($user->hasRole('teacher') && $canViewStudents) {
            $students = User::role('student')
                ->withCount('parents')
                ->get();

            $withParents = $students->where('parents_count', '>', 0)->count();
            $withoutParents = $students->where('parents_count', '==', 0)->count();

            $stats['charts']['parent_linkage'] = [
                'labels' => ['With Linked Parent', 'No Linked Parent'],
                'data' => [$withParents, $withoutParents],
                'colors' => ['#10b981', '#f43f5e'],
            ];

            $stats['recent']['students'] = User::role('student')
                ->with(['parents'])
                ->latest()
                ->limit(5)
                ->get();
        }

        // Student-specific data
        if ($user->hasRole('student')) {
            $stats['student_info'] = [
                'parents' => $user->parents()->withPivot('relationship')->get(),
            ];

            $myAnnouncements = $this->announcements->queryForUser($user)->get();
            $withAttach = $myAnnouncements->whereNotNull('attachment_path')->count();
            $withoutAttach = $myAnnouncements->whereNull('attachment_path')->count();

            $stats['charts']['attachments'] = [
                'labels' => ['With Attachment', 'Text Only'],
                'data' => [$withAttach, $withoutAttach],
                'colors' => ['#6366f1', '#cbd5e1'],
            ];
        }

        // Parent-specific data
        if ($user->hasRole('parent')) {
            $stats['parent_info'] = [
                'children' => $user->children()->with('teacher')->get(),
            ];
        }

        return view('admin.dashboard.index', $stats);
    }
}
