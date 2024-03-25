<?php

namespace App\Traits\Contact;

/**
 * Single place for all dropdown options. Sets of sorted dropdown
 * options methods. Use as per your need.
 */
trait ProvideDropdownOptions
{
    /**
     * Dropdown choices.
     *
     * @var array
     */
    public $booleanDropdownChoices = [
        'active_inactive',
        'yes_no'
    ];

    /**
     * Is bolean dropdown choice exists.
     *
     * @param  string  $choice
     * @return bool
     */
    public function isBooleanDropdownChoiceExists($choice): bool
    {
        return in_array($choice, $this->booleanDropdownChoices);
    }

    /**
     * Get boolean dropdown options.
     *
     * @param  string  $choice
     * @return array
     */
    public function getBooleanDropdownOptions($choice = 'active_inactive'): array
    {
        return $this->isBooleanDropdownChoiceExists($choice) && $choice == 'active_inactive'
            ? $this->getActiveInactiveDropdownOptions()
            : $this->getYesNoDropdownOptions();
    }

    /**
     * Get active/inactive dropdown options.
     *
     * @return array
     */
    public function getActiveInactiveDropdownOptions(): array
    {
        return [
            [
                'value'    => '',
                'label'    => __('app.common.select-options'),
                'disabled' => true,
                'selected' => true,
            ],
            [
                'label'    => trans('app.datagrid.active'),
                'value'    => 1,
                'disabled' => false,
                'selected' => false,
            ], [
                'label'    => trans('app.datagrid.inactive'),
                'value'    => 0,
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Get yes/no dropdown options.
     *
     * @return array
     */
    public function getYesNoDropdownOptions(): array
    {
        return [
            [
                'value'    => '',
                'label'    => __('app.common.select-options'),
                'disabled' => true,
                'selected' => true,
            ],
            [
                'value'    => 0,
                'label'    => __('app.common.no'),
                'disabled' => false,
                'selected' => false,
            ], [
                'value'    => 1,
                'label'    => __('app.common.yes'),
                'disabled' => false,
                'selected' => false,
            ]
        ];
    }

    /**
     * Get user dropdown options.
     *
     * @return array
     */
    public function getUserDropdownOptions(): array
    {
        $options = app(\App\Repositories\User\UserRepository::class)
            ->get(['id as value', 'name as label'])
            ->map(function ($item, $key) {
                $item->disabled = false;

                $item->selected = false;

                return $item;
            })
            ->toArray();

        return [
            [
                'label'    => __('app.common.select-users'),
                'value'    => '',
                'disabled' => true,
                'selected' => true,
            ],
            ...$options
        ];
    }

    /**
     * Get organization dropdown options.
     *
     * @return array
     */
    public function getOrganizationDropdownOptions(): array
    {
        $options = app(\App\Repositories\Contact\OrganizationRepository::class)
            ->get(['id as value', 'name as label'])
            ->map(function ($item, $key) {
                $item->disabled = false;

                $item->selected = false;

                return $item;
            })
            ->toArray();

        return [
            [
                'label'    => __('app.common.select-organization'),
                'value'    => '',
                'disabled' => true,
                'selected' => true,
            ],
            ...$options
        ];
    }

    /**
     * Get role dropdown options.
     *
     * @return array
     */
    public function getRoleDropdownOptions(): array
    {
        return [
            [
                'label'    => trans('app.settings.roles.all'),
                'value'    => 'all',
                'disabled' => false,
                'selected' => false,
            ], [
                'label'    => trans('app.settings.roles.custom'),
                'value'    => 'custom',
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Get activity type dropdown options.
     *
     * @return array
     */
    public function getActivityTypeDropdownOptions(): array
    {
        return [
            [
                'label'    => trans('app.common.select-type'),
                'value'    => '',
                'disabled' => true,
                'selected' => true,
            ], [
                'label'    => trans('app.common.select-call'),
                'value'    => 'call',
                'disabled' => false,
                'selected' => false,
            ], [
                'label'    => trans('app.common.select-meeting'),
                'value'    => 'meeting',
                'disabled' => false,
                'selected' => false,
            ], [
                'label'    => trans('app.common.select-lunch'),
                'value'    => 'lunch',
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }
}
