<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Repositories\Activity\ActivityRepository;
use App\Repositories\Activity\FileRepository;
use App\Repositories\Contact\PersonRepository;
use App\Repositories\Lead\LeadRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    /**
     * FileRepository object
     *
     * @var \App\Activity\Repositories\FileRepository
     */
    protected $fileRepository;

    /**
     * ActivityRepository object
     *
     * @var \App\Activity\Repositories\ActivityRepository
     */
    protected $activityRepository;

    /**
     * LeadRepository object
     *
     * @var \App\Lead\Repositories\LeadRepository
     */
    protected $leadRepository;

    /**
     * UserRepository object
     *
     * @var \App\User\Repositories\UserRepository
     */
    protected $userRepository;

    /**
     * PersonRepository object
     *
     * @var \App\Contact\Repositories\PersonRepository
     */
    protected $personRepository;

    /**
     * Create a new controller instance.
     *
     * @param \App\Activity\Repositories\ActivityRepository  $activityRepository
     * @param \App\Activity\Repositories\FileRepository  $fileRepository
     * @param \App\Activity\Repositories\LeadRepository  $leadRepository
     * @param \App\User\Repositories\UserRepository  $userRepository
     * @param \App\Contact\Repositories\PersonRepository  $personRepository
     *
     * @return void
     */
    public function __construct(
        ActivityRepository $activityRepository,
        FileRepository $fileRepository,
        LeadRepository $leadRepository,
        UserRepository $userRepository,
        PersonRepository $personRepository
    ) {
        $this->activityRepository = $activityRepository;

        $this->fileRepository = $fileRepository;

        $this->leadRepository = $leadRepository;

        $this->userRepository = $userRepository;

        $this->personRepository = $personRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('activities.index');
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        if (request('view_type')) {
            $startDate = request()->get('startDate')
                        ? Carbon::createFromTimeString(request()->get('startDate') . " 00:00:01")
                        : Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');

            $endDate = request()->get('endDate')
                    ? Carbon::createFromTimeString(request()->get('endDate') . " 23:59:59")
                    : Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');

            $activities = $this->activityRepository->getActivities([$startDate, $endDate])->toArray();

            return response()->json([
                'activities' => $activities,
            ]);
        } else {
            return app(\App\DataGrids\Activity\ActivityDataGrid::class)->toJson();
        }
    }

    /**
     * Check if activity duration is overlapping with another activity duration.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkIfOverlapping()
    {
        $isOverlapping = $this->activityRepository->isDurationOverlapping(
            request('schedule_from'),
            request('schedule_to'),
            request('participants'),
            request('id')
        );

        return response()->json([
            'overlapping' => $isOverlapping,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'type'          => 'required',
            'comment'       => 'required_if:type,note',
            'schedule_from' => 'required_unless:type,note',
            'schedule_to'   => 'required_unless:type,note',
        ]);

        // Event::dispatch('activity.create.before');

        $activity = $this->activityRepository->create(array_merge(request()->all(), [
            'is_done' => request('type') == 'note' ? 1 : 0,
            'user_id' => auth()->user()->id,
        ]));

        if (request('participants')) {
            if (is_array(request('participants.users'))) {
                foreach (request('participants.users') as $userId) {
                    $activity->participants()->create([
                        'user_id' => $userId
                    ]);
                }
            }

            if (is_array(request('participants.persons'))) {
                foreach (request('participants.persons') as $personId) {
                    $activity->participants()->create([
                        'person_id' => $personId,
                    ]);
                }
            }
        }

        if (request('lead_id')) {
            $lead = $this->leadRepository->find(request('lead_id'));

            $lead->activities()->attach($activity->id);
        }

        // Event::dispatch('activity.create.after', $activity);

        // session()->flash('success', trans('app.activities.create-success', ['type' => trans('app.activities.' . $activity->type)]));
        Log::warning("redirecting back");
        return ['success' => 1];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $activity = $this->activityRepository->findOrFail($id);

        return view('activities.edit', compact('activity'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        Event::dispatch('activity.update.before', $id);

        $activity = $this->activityRepository->update(request()->all(), $id);

        if (request('participants')) {
            $activity->participants()->delete();

            if (is_array(request('participants.users'))) {
                foreach (request('participants.users') as $userId) {
                    $activity->participants()->create([
                        'user_id' => $userId
                    ]);
                }
            }

            if (is_array(request('participants.persons'))) {
                foreach (request('participants.persons') as $personId) {
                    $activity->participants()->create([
                        'person_id' => $personId,
                    ]);
                }
            }
        }

        if (request('lead_id')) {
            $lead = $this->leadRepository->find(request('lead_id'));

            if (! $lead->activities->contains($id)) {
                $lead->activities()->attach($id);
            }
        }

        Event::dispatch('activity.update.after', $activity);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('app.activities.update-success', ['type' => trans('app.activities.' . $activity->type)]),
            ]);
        } else {
            session()->flash('success', trans('app.activities.update-success', ['type' => trans('app.activities.' . $activity->type)]));

            return redirect()->route('admin.activities.index');
        }
    }

    /**
     * Mass Update the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function massUpdate()
    {
        $count = 0;

        $data = request()->all();

        foreach (request('rows') as $activityId) {
            Event::dispatch('activity.update.before', $activityId);

            $activity = $this->activityRepository->update([
                'is_done' => request('value'),
            ], $activityId);

            Event::dispatch('activity.update.after', $activity);

            $count++;
        }

        if (! $count) {
            return response()->json([
                'message' => trans('app.activities.mass-update-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('app.activities.mass-update-success'),
        ]);
    }

    /**
     * Search participants results
     *
     * @return \Illuminate\Http\Response
     */
    public function searchParticipants()
    {
        $users = $this->userRepository->findWhere([
            ['name', 'like', '%' . urldecode(request()->input('query')) . '%']
        ]);

        $persons = $this->personRepository->findWhere([
            ['name', 'like', '%' . urldecode(request()->input('query')) . '%']
        ]);

        return response()->json([
            'users'   => $users,
            'persons' => $persons,
        ]);
    }

    /**
     * Upload files to storage
     *
     * @return \Illuminate\View\View
     */
    public function upload()
    {
        $this->validate(request(), [
            'file' => 'required',
        ]);

        Event::dispatch('activities.file.create.before');

        $file = $this->fileRepository->upload(request()->all());

        if ($file) {
            if ($leadId = request('lead_id')) {
                $lead = $this->leadRepository->find($leadId);

                $lead->activities()->attach($file->activity->id);
            }
            Log::info('uploaded...');
            Event::dispatch('activities.file.create.after', $file);

            session()->flash('success', trans('app.activities.file-upload-success'));
            return [
                'message' => trans('app.activities.file-upload-success')
            ];
        } else {
            session()->flash('error', trans('app.activities.file-upload-error'));
            return [
                'message' => trans('app.activities.file-upload-error')
            ];
        }

        return redirect()->back();
    }

    /**
     * Download file from storage
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function download($id)
    {
        $file = $this->fileRepository->findOrFail($id);

        return Storage::download($file->path);
    }

    /*
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $activity = $this->activityRepository->findOrFail($id);

        try {
            Event::dispatch('activity.delete.before', $id);

            $this->activityRepository->delete($id);

            Event::dispatch('activity.delete.after', $id);

            return response()->json([
                'message' => trans('app.activities.destroy-success', ['type' => trans('app.activities.' . $activity->type)]),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('app.activities.destroy-failed', ['type' => trans('app.activities.' . $activity->type)]),
            ], 400);
        }
    }

    /**
     * Mass Delete the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy()
    {
        foreach (request('rows') as $activityId) {
            Event::dispatch('activity.delete.before', $activityId);

            $this->activityRepository->delete($activityId);

            Event::dispatch('activity.delete.after', $activityId);
        }

        return response()->json([
            'message' => trans('app.response.destroy-success', ['name' => trans('app.activities.title')])
        ]);
    }
}
