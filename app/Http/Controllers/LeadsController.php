<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Repositories\Lead\LeadRepository;
use App\Repositories\Lead\PipelineRepository;
use App\Repositories\Lead\StageRepository;
use App\Http\Requests\Lead\LeadForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

class LeadsController extends Controller
{
    //

    /**
     * Lead repository instance.
     *
     * @var \App\Repositories\Lead\LeadRepository
     */
    protected $leadRepository;

    /**
     * Pipeline repository instance.
     *
     * @var \App\Repositories\Lead\PipelineRepository
     */
    protected $pipelineRepository;

    /**
     * Stage repository instance.
     *
     * @var \App\Repositories\Lead\StageRepository
     */
    protected $stageRepository;

    /**
     * Create a new controller instance.
     *
     * @param \App\Repositories\Lead\LeadRepository  $leadRepository
     * @param \App\Repositories\Lead\PipelineRepository  $pipelineRepository
     * @param \App\Repositories\Lead\StageRepository  $stageRepository
     *
     * @return void
     */
    public function __construct(
        LeadRepository $leadRepository,
        PipelineRepository $pipelineRepository,
        StageRepository $stageRepository
    ) {
        $this->leadRepository = $leadRepository;

        $this->pipelineRepository = $pipelineRepository;

        $this->stageRepository = $stageRepository;

        request()->request->add(['entity_type' => 'leads']);
    }

    public function leads_index_page()
    {
        return Inertia::render('dashboards/leads/index');
    }

    public function create_lead_page()
    {
        $stage_id = request('stage_id');
        $customAttributes = app('App\Repositories\Attribute\AttributeRepository')->findWhere([
            'entity_type' => 'leads',
            'quick_add'   => 1
        ]);
        $organizationAttributes = app('App\Repositories\Attribute\AttributeRepository')->findWhere([
            'entity_type' => 'organizations',
        ]);
        $customAttributes->transform(function ($attribute){
            $options = $attribute->lookup_type ? app('App\Repositories\Attribute\AttributeRepository')->getLookUpOptions($attribute->lookup_type)
                : $attribute->options()->orderBy('sort_order')->get();
            $attribute['options'] = $options;
            return $attribute;
        });
        $organizationAttributes->transform(function ($attribute){
            $options = $attribute->lookup_type ? app('App\Repositories\Attribute\AttributeRepository')->getLookUpOptions($attribute->lookup_type)
                : $attribute->options()->orderBy('sort_order')->get();
            $attribute['options'] = $options;
            return $attribute;
        });
        return Inertia::render('dashboards/leads/create', [
            'customAttributes' => $customAttributes,
            'organizationAttributes' => $organizationAttributes,
            'stage_id' => $stage_id ?? 1,
        ]);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        if (request('view_type')) {
            return app(\App\DataGrids\Lead\LeadDataGrid::class)->toJson();
        } else {
            $createdAt = request('created_at') ?? null;

            if ($createdAt && isset($createdAt["bw"])) {
                $createdAt = explode(",", $createdAt["bw"]);

                $createdAt[0] .= ' 00:01';

                $createdAt[1] = $createdAt[1]
                    ? $createdAt[1] . ' 23:59'
                    : Carbon::now()->format('Y-m-d 23:59');
            } else {
                $createdAt = null;
            }

            if (request('pipeline_id')) {
                $pipeline = $this->pipelineRepository->find(request('pipeline_id'));
            } else {
                $pipeline = $this->pipelineRepository->getDefaultPipeline();
            }

            $leads = $this->leadRepository->getLeads($pipeline->id, request('search') ?? '', $createdAt)->toArray();

            $totalCount = [];

            foreach ($leads as $key => $lead) {
                $totalCount[$lead['status']] = ($totalCount[$lead['status']] ?? 0) + (float) $lead['lead_value'];

                $leads[$key]['lead_value'] = core()->formatBasePrice($lead['lead_value']);
            }

            $totalCount = array_map(function ($count) {
                return core()->formatBasePrice($count);
            }, $totalCount);

            return response()->json([
                'blocks'      => $leads,
                'stage_names' => $pipeline->stages->pluck('name'),
                'stages'      => $pipeline->stages->toArray(),
                'total_count' => $totalCount,
            ]);
        }
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param \App\Http\Requests\Lead\LeadForm $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(LeadForm $request)
    // {

    //     Event::dispatch('lead.create.before');

    //     $data = request()->all();

    //     $data['status'] = 1;

    //     Log::info('aaaa ' . $data['lead_pipeline_stage_id']);
    //     if ($data['lead_pipeline_stage_id']) {
    //         $stage = $this->stageRepository->findOrFail($data['lead_pipeline_stage_id']);
    //         $data['lead_pipeline_id'] = $stage->lead_pipeline_id;
    //     } else {
    //         $pipeline = $this->pipelineRepository->getDefaultPipeline();

    //         $stage = $pipeline->stages()->first();

    //         $data['lead_pipeline_id'] = $pipeline->id;

    //         $data['lead_pipeline_stage_id'] = $stage->id;
    //     }
        
    //     if (in_array($stage->code, ['won', 'lost'])) {
    //         $data['closed_at'] = Carbon::now();
    //     }
    //     $lead = $this->leadRepository->create($data);
    //     Event::dispatch('lead.create.after', $lead);

    //     // session()->flash('success', trans('admin::app.leads.create-success'));

    //     return redirect()->route('dashboards.leads.index');
    // }

    /**
 * Store a newly created resource in storage.
 *
 * @param \App\Http\Requests\Lead\LeadForm $request
 * @return \Illuminate\Http\Response
 */
public function store(LeadForm $request)
{

    Event::dispatch('lead.create.before');

    $data = request()->all();

    $data['status'] = 1;

    Log::info('aaaa ' . ($data['lead_pipeline_stage_id'] ?? '')); // Check if lead_pipeline_stage_id exists

    if (isset($data['lead_pipeline_stage_id'])) {
        $stage = $this->stageRepository->findOrFail($data['lead_pipeline_stage_id']);
        $data['lead_pipeline_id'] = $stage->lead_pipeline_id;
    } else {
        $pipeline = $this->pipelineRepository->getDefaultPipeline();

        $stage = $pipeline->stages()->first();

        $data['lead_pipeline_id'] = $pipeline->id;

        $data['lead_pipeline_stage_id'] = $stage->id;
    }
    
    if (in_array($stage->code, ['won', 'lost'])) {
        $data['closed_at'] = Carbon::now();
    }
    $lead = $this->leadRepository->create($data);
    Event::dispatch('lead.create.after', $lead);

    // session()->flash('success', trans('admin::app.leads.create-success'));

    return redirect()->route('dashboards.leads.index');
}


    /**
     * Display a resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $lead = $this->leadRepository->findOrFail($id);

        $currentUser = auth()->guard('user')->user();

        if ($currentUser->view_permission != 'global') {
            if ($currentUser->view_permission == 'group') {
                $userIds = app('\App\User\Repositories\UserRepository')->getCurrentUserGroupsUserIds();

                if (!in_array($lead->user_id, $userIds)) {
                    return redirect()->route('admin.leads.index');
                }
            } else {
                if ($lead->user_id != $currentUser->id) {
                    return redirect()->route('admin.leads.index');
                }
            }
        }

        return view('admin::leads.view', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\LeadForm $request
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LeadForm $request, $id)
    {
        Event::dispatch('lead.update.before', $id);
        $data = request()->all();

        if ($data['lead_pipeline_stage_id']) {
            $stage = $this->stageRepository->findOrFail($data['lead_pipeline_stage_id']);

            $data['lead_pipeline_id'] = $stage->lead_pipeline_id;
        } else {
            $pipeline = $this->pipelineRepository->getDefaultPipeline();

            $stage = $pipeline->stages()->first();
            
            $data['lead_pipeline_id'] = $pipeline->id;

            $data['lead_pipeline_stage_id'] = $stage->id;
        }

        $lead = $this->leadRepository->update($data, $id);

        Event::dispatch('lead.update.after', $lead);
    }

    /**
     * Search person results.
     *
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        $results = $this->leadRepository->findWhere([
            ['title', 'like', '%' . urldecode(request()->input('query')) . '%']
        ]);

        return response()->json($results);
    }

    /*
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->leadRepository->findOrFail($id);

        try {
            Event::dispatch('lead.delete.before', $id);

            $this->leadRepository->delete($id);

            Event::dispatch('lead.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.response.destroy-success', ['name' => trans('admin::app.leads.lead')]),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.response.destroy-failed', ['name' => trans('admin::app.leads.lead')]),
            ], 400);
        }
    }

    /**
     * Mass Update the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function massUpdate()
    {
        $data = request()->all();

        foreach ($data['rows'] as $leadId) {
            $lead = $this->leadRepository->find($leadId);

            Event::dispatch('lead.update.before', $leadId);

            $lead->update(['lead_pipeline_stage_id' => $data['value']]);

            Event::dispatch('lead.update.before', $leadId);
        }

        return response()->json([
            'message' => trans('admin::app.response.update-success', ['name' => trans('admin::app.leads.title')])
        ]);
    }

    /**
     * Mass Delete the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy()
    {
        foreach (request('rows') as $leadId) {
            Event::dispatch('lead.delete.before', $leadId);

            $this->leadRepository->delete($leadId);

            Event::dispatch('lead.delete.after', $leadId);
        }

        return response()->json([
            'message' => trans('admin::app.response.destroy-success', ['name' => trans('admin::app.leads.title')]),
        ]);
    }
}
