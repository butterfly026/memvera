<?php

namespace App\DataGrids\Contact;

use Illuminate\Support\Facades\DB;
use App\Repositories\Contact\PersonRepository;
use App\DataGrids\DataGrid;

class OrganizationDataGrid extends DataGrid
{
    /**
     * Person repository instance.
     *
     * @var \Webkul\Contact\Repositories\PersonRepository
     */
    protected $personRepository;

    /**
     * Create datagrid instance.
     *
     * @return void
     */
    public function __construct(PersonRepository $personRepository)
    {
        parent::__construct();

        $this->personRepository = $personRepository;
    }

    /**
     * Prepare query builder.
     *
     * @return void
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('organizations')
            ->addSelect(
                'organizations.id',
                'organizations.name',
                'organizations.address',
                'organizations.created_at'
            );

        $this->addFilter('id', 'organizations.id');

        $this->setQueryBuilder($queryBuilder);
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function addColumns()
    {
        $this->addColumn([
            'index'    => 'id',
            'label'    => trans('app.datagrid.id'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'name',
            'label'    => trans('app.datagrid.name'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'persons_count',
            'label'      => trans('app.datagrid.persons_count'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => false,
            'filterable' => false,
            'closure'    => function ($row) {
                $personsCount = $this->personRepository->findWhere(['organization_id' => $row->id])->count();

                $route = urldecode(route('contacts.persons.index', ['organization[in]' => $row->id]));

                return "<a href='" . $route . "'>" . $personsCount . "</a>";
            },
        ]);

        $this->addColumn([
            'index'    => 'created_at',
            'label'    => trans('app.datagrid.created_at'),
            'type'     => 'date_range',
            'sortable' => true,
            'closure'  => function ($row) {
                return core()->formatDate($row->created_at);
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'title'  => trans('ui::app.datagrid.edit'),
            'method' => 'GET',
            'route'  => 'contacts.organizations.edit',
            'icon'   => 'pencil-icon',
        ]);

        $this->addAction([
            'title'        => trans('ui::app.datagrid.delete'),
            'method'       => 'DELETE',
            'route'        => 'contacts.organizations.delete',
            'confirm_text' => trans('ui::app.datagrid.massaction.delete', ['resource' => 'user']),
            'icon'         => 'trash-icon',
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        $this->addMassAction([
            'type'   => 'delete',
            'label'  => trans('ui::app.datagrid.delete'),
            'action' => route('contacts.organizations.mass_delete'),
            'method' => 'PUT',
        ]);
    }
}