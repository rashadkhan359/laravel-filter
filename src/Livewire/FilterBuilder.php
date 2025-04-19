<?php

namespace RashadKhan\LaravelFilter\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Blade;

class FilterBuilder extends Component
{
    /**
     * The available fields for filtering.
     *
     * @var array
     */
    public $fields = [];

    /**
     * The current filter state.
     *
     * @var array
     */
    public $filters = [
        'logic' => 'and',
        'conditions' => []
    ];

    /**
     * The search term.
     *
     * @var string
     */
    public $searchTerm = '';

    /**
     * The field to sort by.
     *
     * @var string
     */
    public $sortField = '';

    /**
     * The sort direction.
     *
     * @var string
     */
    public $sortDirection = 'asc';

    /**
     * The theme to use.
     *
     * @var string
     */
    public $theme = 'light';

    /**
     * Event listeners for the component.
     *
     * @var array
     */
    protected $listeners = ['refreshFilters'];

    /**
     * Mount the component.
     *
     * @param array $fields
     * @param array $initialFilters
     * @param string $theme
     * @return void
     */
    public function mount($fields = [], $initialFilters = null, $theme = 'light')
    {
        $this->fields = $fields;

        if ($initialFilters) {
            $this->filters = $initialFilters;
        }

        $this->theme = $theme;
    }

    /**
     * Add a new filter condition.
     *
     * @return void
     */
    public function addCondition()
    {
        if (empty($this->fields)) {
            return;
        }

        $defaultField = $this->fields[0];

        $this->filters['conditions'][] = [
            'field' => $defaultField['name'],
            'operator' => $this->getDefaultOperator($defaultField['type']),
            'value' => '',
        ];
    }

    /**
     * Remove a filter condition.
     *
     * @param int $index
     * @return void
     */
    public function removeCondition($index)
    {
        unset($this->filters['conditions'][$index]);
        $this->filters['conditions'] = array_values($this->filters['conditions']);
    }

    /**
     * Update a condition field.
     *
     * @param int $index
     * @param string $fieldName
     * @return void
     */
    public function updateConditionField($index, $fieldName)
    {
        foreach ($this->fields as $field) {
            if ($field['name'] === $fieldName) {
                $this->filters['conditions'][$index]['field'] = $fieldName;
                $this->filters['conditions'][$index]['operator'] = $this->getDefaultOperator($field['type']);
                $this->filters['conditions'][$index]['value'] = '';
                break;
            }
        }
    }

    /**
     * Update a condition operator.
     *
     * @param int $index
     * @param string $operator
     * @return void
     */
    public function updateConditionOperator($index, $operator)
    {
        $this->filters['conditions'][$index]['operator'] = $operator;
    }

    /**
     * Update a condition value.
     *
     * @param int $index
     * @param mixed $value
     * @return void
     */
    public function updateConditionValue($index, $value)
    {
        $this->filters['conditions'][$index]['value'] = $value;
    }

    /**
     * Toggle the logic type (AND/OR).
     *
     * @param string $logic
     * @return void
     */
    public function toggleLogic($logic)
    {
        $this->filters['logic'] = $logic;
    }

    /**
     * Apply the current filters.
     *
     * @return void
     */
    public function applyFilters()
    {
        $filterData = [
            'filters' => $this->filters,
            'search' => $this->searchTerm,
            'sort' => $this->sortField ? $this->sortField . ':' . $this->sortDirection : null,
        ];

        $this->emit('filterChanged', $filterData);
    }

    /**
     * Reset all filters.
     *
     * @return void
     */
    public function resetFilters()
    {
        $this->filters = [
            'logic' => 'and',
            'conditions' => []
        ];
        $this->searchTerm = '';
        $this->sortField = '';
        $this->sortDirection = 'asc';

        $this->applyFilters();
    }

    /**
     * Refresh the filters from an external source.
     *
     * @param array $filterData
     * @return void
     */
    public function refreshFilters($filterData)
    {
        if (isset($filterData['filters'])) {
            $this->filters = $filterData['filters'];
        }

        if (isset($filterData['search'])) {
            $this->searchTerm = $filterData['search'];
        }

        if (isset($filterData['sort'])) {
            $parts = explode(':', $filterData['sort']);
            $this->sortField = $parts[0] ?? '';
            $this->sortDirection = $parts[1] ?? 'asc';
        }
    }

    /**
     * Get the default operator for a field type.
     *
     * @param string $fieldType
     * @return string
     */
    protected function getDefaultOperator($fieldType)
    {
        switch ($fieldType) {
            case 'text':
                return 'contains';
            case 'number':
                return 'eq';
            case 'date':
                return 'eq';
            case 'boolean':
                return 'eq';
            default:
                return 'eq';
        }
    }

    /**
     * Get available operators for a field.
     *
     * @param string $fieldName
     * @return array
     */
    public function getOperatorsForField($fieldName)
    {
        foreach ($this->fields as $field) {
            if ($field['name'] === $fieldName) {
                return $this->getOperatorsForFieldType($field['type']);
            }
        }

        return [];
    }

    /**
     * Get available operators for a field type.
     *
     * @param string $fieldType
     * @return array
     */
    protected function getOperatorsForFieldType($fieldType)
    {
        switch ($fieldType) {
            case 'text':
                return [
                    ['value' => 'eq', 'label' => 'Equals'],
                    ['value' => 'neq', 'label' => 'Not Equals'],
                    ['value' => 'contains', 'label' => 'Contains'],
                    ['value' => 'starts_with', 'label' => 'Starts With'],
                    ['value' => 'ends_with', 'label' => 'Ends With'],
                    ['value' => 'in', 'label' => 'In'],
                    ['value' => 'not_in', 'label' => 'Not In'],
                    ['value' => 'is_null', 'label' => 'Is Null'],
                    ['value' => 'is_not_null', 'label' => 'Is Not Null'],
                ];
            case 'number':
                return [
                    ['value' => 'eq', 'label' => 'Equals'],
                    ['value' => 'neq', 'label' => 'Not Equals'],
                    ['value' => 'gt', 'label' => 'Greater Than'],
                    ['value' => 'gte', 'label' => 'Greater Than or Equal'],
                    ['value' => 'lt', 'label' => 'Less Than'],
                    ['value' => 'lte', 'label' => 'Less Than or Equal'],
                    ['value' => 'in', 'label' => 'In'],
                    ['value' => 'not_in', 'label' => 'Not In'],
                    ['value' => 'is_null', 'label' => 'Is Null'],
                    ['value' => 'is_not_null', 'label' => 'Is Not Null'],
                ];
            case 'date':
                return [
                    ['value' => 'eq', 'label' => 'Equals'],
                    ['value' => 'neq', 'label' => 'Not Equals'],
                    ['value' => 'gt', 'label' => 'After'],
                    ['value' => 'gte', 'label' => 'After or On'],
                    ['value' => 'lt', 'label' => 'Before'],
                    ['value' => 'lte', 'label' => 'Before or On'],
                    ['value' => 'between', 'label' => 'Between'],
                    ['value' => 'is_null', 'label' => 'Is Null'],
                    ['value' => 'is_not_null', 'label' => 'Is Not Null'],
                ];
            case 'boolean':
                return [
                    ['value' => 'eq', 'label' => 'Equals'],
                    ['value' => 'is_null', 'label' => 'Is Null'],
                    ['value' => 'is_not_null', 'label' => 'Is Not Null'],
                ];
            default:
                return [
                    ['value' => 'eq', 'label' => 'Equals'],
                    ['value' => 'neq', 'label' => 'Not Equals'],
                ];
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('laravel-filter::livewire.filter-builder');
    }
}
