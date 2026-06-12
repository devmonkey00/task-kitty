<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Http\Requests\DenyLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\Leave\ApproveLeaveRequestAction;
use App\Services\Leave\DenyLeaveRequestAction;
use App\Services\Leave\LeaveReviewPresenter;
use App\Services\Leave\SubmitLeaveRequestAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveReviewPresenter $leaveReviewPresenter,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $user = $request->user();
        $canReview = $user->canApproveLeave();
        $scope = $request->input('scope', $canReview ? 'my_action' : 'mine');

        $query = LeaveRequest::query()
            ->with(['user:id,name,email', 'reviewer:id,name', 'approvals'])
            ->latest();

        if ($scope === 'mine' || ! $canReview) {
            $query->where('user_id', $user->id);
        }

        $leaveRequests = $query->get();

        if ($scope === 'my_action') {
            $leaveRequests = $leaveRequests->filter(
                fn (LeaveRequest $leave) => $user->can('review', $leave),
            )->values();
        }

        $rows = $leaveRequests->map(function (LeaveRequest $leave) use ($user) {
            return [
                ...(new LeaveRequestResource($leave))->resolve(),
                ...$this->leaveReviewPresenter->forIndexRow($leave, $user),
            ];
        });

        return Inertia::render('Leave/Index', [
            'leaveRequests' => $rows,
            'canReview' => $canReview,
            'canFinalizeLeave' => $user->canFinalizeLeave(),
            'scope' => $scope,
            'statuses' => collect(LeaveRequestStatus::cases())->map(fn (LeaveRequestStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values()->all(),
        ]);
    }

    public function store(
        StoreLeaveRequestRequest $request,
        SubmitLeaveRequestAction $submit,
    ): RedirectResponse {
        $submit->execute($request->user(), $request->validated());

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request submitted.');
    }

    public function approve(
        Request $request,
        LeaveRequest $leaveRequest,
        ApproveLeaveRequestAction $approve,
    ): RedirectResponse {
        $this->authorize('review', $leaveRequest);

        $approve->execute($request->user(), $leaveRequest);

        return back()->with('success', 'Leave request approved.');
    }

    public function deny(
        DenyLeaveRequestRequest $request,
        LeaveRequest $leaveRequest,
        DenyLeaveRequestAction $deny,
    ): RedirectResponse {
        $this->authorize('review', $leaveRequest);

        $deny->execute(
            $request->user(),
            $leaveRequest,
            $request->validated('comments'),
        );

        return back()->with('success', 'Leave request denied.');
    }
}
