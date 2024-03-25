<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Event;
use App\Http\Requests\AttributeForm;
use Inertia\Inertia;
use App\Repositories\Contact\OrganizationRepository;

class OrganizationController extends Controller
{
    /**
     * OrganizationRepository object
     *
     * @var \App\Product\Repositories\OrganizationRepository
     */
    protected $organizationRepository;

    /**
     * Create a new controller instance.
     *
     * @param \App\Repositories\Product\OrganizationRepository  $organizationRepository
     *
     * @return void
     */
    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;

        request()->request->add(['entity_type' => 'organizations']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(\App\DataGrids\Contact\OrganizationDataGrid::class)->toJson();
        }

        return Inertia::render('dashboards/contacts/organizations/index', [
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $customAttributes = app('App\Repositories\Attribute\AttributeRepository')->findWhere([
            'entity_type' => 'organizations',
        ]);
        $currencyCode = core()->currencySymbol(config('app.currency'));
        return Inertia::render('dashboards/contacts/organizations/create', [
            'customAttributes' => $customAttributes,
            'currencyCode' => $currencyCode,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\AttributeForm $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttributeForm $request)
    {
        Event::dispatch('contacts.organization.create.before');

        $organization = $this->organizationRepository->create(request()->all());

        Event::dispatch('contacts.organization.create.after', $organization);

        session()->flash('success', trans('admin::app.contacts.organizations.create-success'));

        return redirect()->route('contacts.organizations.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $organization = $this->organizationRepository->findOrFail($id);

        return view('admin::contacts.organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Attribute\Http\Requests\AttributeForm $request
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AttributeForm $request, $id)
    {
        Event::dispatch('contacts.organization.update.before', $id);

        $organization = $this->organizationRepository->update(request()->all(), $id);

        Event::dispatch('contacts.organization.update.after', $organization);

        session()->flash('success', trans('admin::app.contacts.organizations.update-success'));

        return redirect()->route('contacts.organizations.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->organizationRepository->findOrFail($id);

        try {
            Event::dispatch('contact.organization.delete.before', $id);

            $this->organizationRepository->delete($id);

            Event::dispatch('contact.organization.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.response.destroy-success', ['name' => trans('admin::app.contacts.organizations.organization')]),
            ], 200);
        } catch(\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.response.destroy-failed', ['name' => trans('admin::app.contacts.organizations.organization')]),
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
        foreach (request('rows') as $organizationId) {
            Event::dispatch('contact.organization.delete.before', $organizationId);

            $this->organizationRepository->delete($organizationId);

            Event::dispatch('contact.organization.delete.after', $organizationId);
        }

        return response()->json([
            'message' => trans('admin::app.response.destroy-success', ['name' => trans('admin::app.contacts.organizations.title')])
        ]);
    }
}
