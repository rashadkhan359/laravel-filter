<div class="filter-builder {{ $theme === 'dark' ? 'bg-gray-800 text-white' : 'bg-white' }} rounded-lg shadow p-4">
    <h3 class="text-lg font-medium mb-4">Filter Builder</h3>

    <!-- Search -->
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Search</label>
        <input
            type="text"
            class="w-full border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
            wire:model.defer="searchTerm"
            placeholder="Search term..."
        />
    </div>

    <!-- Sort -->
    <div class="mb-4 grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Sort By</label>
            <select
                class="w-full border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                wire:model.defer="sortField"
            >
                <option value="">None</option>
                @foreach($fields as $field)
                    <option value="{{ $field['name'] }}">{{ $field['label'] ?? $field['name'] }}</option>
                @endforeach
            </select>
        </div>

        @if($sortField)
            <div>
                <label class="block text-sm font-medium mb-1">Direction</label>
                <select
                    class="w-full border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                    wire:model.defer="sortDirection"
                >
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
            </div>
        @endif
    </div>

    <!-- Logic toggle -->
    <div class="flex items-center space-x-2 mb-4">
        <span>Combine filters with:</span>
        <button
            type="button"
            class="{{ $theme === 'dark' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-500 hover:bg-blue-600' }} text-white font-medium py-1 px-2 rounded text-sm {{ $filters['logic'] === 'and' ? 'opacity-100' : 'opacity-50' }}"
            wire:click="toggleLogic('and')"
        >
            AND
        </button>
        <button
            type="button"
            class="{{ $theme === 'dark' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-500 hover:bg-blue-600' }} text-white font-medium py-1 px-2 rounded text-sm {{ $filters['logic'] === 'or' ? 'opacity-100' : 'opacity-50' }}"
            wire:click="toggleLogic('or')"
        >
            OR
        </button>
    </div>

    <!-- Filter conditions -->
    @foreach($filters['conditions'] as $index => $condition)
        <div class="flex items-center space-x-2 mb-2">
            <!-- Field selector -->
            <select
                class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                wire:model="filters.conditions.{{ $index }}.field"
                wire:change="updateConditionField({{ $index }}, $event.target.value)"
            >
                @foreach($fields as $field)
                    <option value="{{ $field['name'] }}">{{ $field['label'] ?? $field['name'] }}</option>
                @endforeach
            </select>

            <!-- Operator selector -->
            <select
                class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                wire:model="filters.conditions.{{ $index }}.operator"
            >
                @foreach($this->getOperatorsForField($condition['field']) as $op)
                    <option value="{{ $op['value'] }}">{{ $op['label'] }}</option>
                @endforeach
            </select>

            <!-- Value input -->
            @if(!in_array($condition['operator'], ['is_null', 'is_not_null']))
                @php
                    $fieldType = collect($fields)->firstWhere('name', $condition['field'])['type'] ?? 'text';
                @endphp

                @if($fieldType === 'boolean')
                    <select
                        class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                        wire:model="filters.conditions.{{ $index }}.value"
                    >
                        <option value="1">True</option>
                        <option value="0">False</option>
                    </select>
                @elseif($condition['operator'] === 'between' && $fieldType === 'date')
                    <div class="flex space-x-2">
                        <input
                            type="date"
                            class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                            wire:model="filters.conditions.{{ $index }}.value.0"
                        />
                        <input
                            type="date"
                            class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                            wire:model="filters.conditions.{{ $index }}.value.1"
                        />
                    </div>
                @elseif($fieldType === 'date')
                    <input
                        type="date"
                        class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                        wire:model="filters.conditions.{{ $index }}.value"
                    />
                @elseif($fieldType === 'number')
                    <input
                        type="number"
                        class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                        wire:model="filters.conditions.{{ $index }}.value"
                    />
                @else
                    <input
                        type="text"
                        class="border {{ $theme === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300' }} rounded px-3 py-2"
                        wire:model="filters.conditions.{{ $index }}.value"
                    />
                @endif
            @endif

            <!-- Remove button -->
            <button
                type="button"
                class="{{ $theme === 'dark' ? 'text-red-400 hover:text-red-300' : 'text-red-500 hover:text-red-700' }}"
                wire:click="removeCondition({{ $index }})"
            >
                &times;
            </button>
        </div>
    @endforeach

    <!-- Add button -->
    <button
        type="button"
        class="{{ $theme === 'dark' ? 'text-blue-400 hover:text-blue-300' : 'text-blue-500 hover:text-blue-700' }} mb-4"
        wire:click="addCondition"
    >
        + Add Condition
    </button>

    <!-- Action buttons -->
    <div class="flex justify-end mt-4 space-x-2">
        <button
            type="button"
            class="{{ $theme === 'dark' ? 'bg-gray-600 hover:bg-gray-700' : 'bg-gray-200 hover:bg-gray-300' }} font-medium py-2 px-4 rounded"
            wire:click="resetFilters"
        >
            Reset
        </button>
        <button
            type="button"
            class="{{ $theme === 'dark' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-500 hover:bg-blue-600' }} text-white font-medium py-2 px-4 rounded"
            wire:click="applyFilters"
        >
            Apply Filters
        </button>
    </div>
</div>
