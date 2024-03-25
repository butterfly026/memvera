<?php

namespace App\DataGrids\Product;

use App\DataGrids\DataGrid;
use Illuminate\Support\Facades\DB;

class ProductDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return void
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('products')
            ->addSelect(
                'products.id',
                'products.sku',
                'products.name',
                'products.price',
                'products.quantity'
            );

        $this->addFilter('id', 'products.id');

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
            'index'    => 'sku',
            'label'    => trans('app.datagrid.sku'),
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
            'index'    => 'price',
            'label'    => trans('app.datagrid.price'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => function ($row) {
                return round($row->price, 2);
            },
        ]);

        $this->addColumn([
            'index'    => 'quantity',
            'label'    => trans('app.datagrid.quantity'),
            'type'     => 'string',
            'sortable' => true,
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
            'title'  => trans('app.ui.datagrid.edit'),
            'method' => 'GET',
            'route'  => 'products.edit',
            'icon'   => 'ri-edit-2-line',
        ]);

        $this->addAction([
            'title'        => trans('app.ui.datagrid.delete'),
            'method'       => 'DELETE',
            'route'        => 'products.delete',
            'confirm_text' => trans('app.ui.datagrid.massaction.delete', ['resource' => 'user']),
            'icon'         => 'ri-delete-bin-5-line',
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
            'label'  => trans('app.ui.datagrid.delete'),
            'action' => route('products.mass_delete'),
            'method' => 'PUT',
        ]);
    }
}
