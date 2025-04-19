<div x-data="filterBuilder(@js($fields), {
    theme: '{{ $theme ?? 'light' }}',
    initialFilters: @js($initialFilters ?? null),
    onFilterChange: function(filterData) {
        // This function will be called when filters change
        // You can add custom JavaScript here if needed
    }
})"
    x-init=""
    @filter-changed.window="if($event.detail.origin !== $el) refreshFilters($event.detail)"
    :class="getThemeClasses().container"
>
    <h3 :class="getThemeClasses().header">Filter Builder</h3>

    <!-- Search -->
    <div :class="getThemeClasses().searchContainer">
        <label :class="getThemeClasses().label">Search</label>
        <input
            type="text"
            :class="getThemeClasses().input"
            x-model="searchTerm"
            placeholder="Search term..."
        />
    </div>

    <!-- Sort -->
    <div :class="getThemeClasses().sortContainer">
        <div>
            <label :class="getThemeClasses().label">Sort By</label>
            <select
                :class="getThemeClasses().select"
                x-model="sortField"
            >
                <option value="">None</option>
                <template x-for="field in fields" :key="field.name">
                    <option :value="field.name" x-text="field.label || field.name"></option>
                </template>
            </select>
        </div>

        <div x-show="sortField">
            <label :class="getThemeClasses().label">Direction</label>
            <select
                :class="getThemeClasses().select"
                x-model="sortDirection"
            >
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </div>
    </div>

    <!-- Logic toggle -->
    <div :class="getThemeClasses().logicSwitch">
        <span>Combine filters with:</span>
        <button
            type="button"
            :class="[getThemeClasses().button, 'text-sm px-2 py-1', filters.logic === 'and' ? 'opacity-100' : 'opacity-50']"
            @click="toggleLogic('and')"
        >
            AND
        </button>
        <button
            type="button"
            :class="[getThemeClasses().button, 'text-sm px-2 py-1', filters.logic === 'or' ? 'opacity-100' : 'opacity-50']"
            @click="toggleLogic('or')"
        >
            OR
        </button>
    </div>

    <!-- Filter conditions -->
    <template x-for="(condition, index) in filters.conditions" :key="index">
        <div :class="getThemeClasses().filterRow">
            <!-- Field selector -->
            <select
                :class="getThemeClasses().select"
                x-model="condition.field"
                @change="updateConditionField(index, condition.field)"
            >
                <template x-for="field in fields" :key="field.name">
                    <option :value="field.name" x-text="field.label || field.name"></option>
                </template>
            </select>

            <!-- Operator selector -->
            <select
                :class="getThemeClasses().select"
                x-model="condition.operator"
            >
                <template x-for="op in getOperatorsForField(condition.field)" :key="op.value">
                    <option :value="op.value" x-text="op.label"></option>
                </template>
            </select>

            <!-- Value input -->
            <template x-if="needsValueInput(condition.operator)">
                <input
                    x-show="fields.find(f => f.name === condition.field)?.type !== 'boolean' &&
                           !['between'].includes(condition.operator)"
                    type="text"
                    :class="getThemeClasses().input"
                    x-model="condition.value"
                />

                <div
                    x-show="condition.operator === 'between'"
                    class="flex space-x-2"
                >
                    <input
                        type="date"
                        :class="getThemeClasses().input"
                        x-model="condition.value[0]"
                        @focus="if (!Array.isArray(condition.value)) condition.value = ['', '']"
                    />
                    <input
                        type="date"
                        :class="getThemeClasses().input"
                        x-model="condition.value[1]"
                        @focus="if (!Array.isArray(condition.value)) condition.value = ['', '']"
                    />
                </div>

                <select
                    x-show="fields.find(f => f.name === condition.field)?.type === 'boolean'"
                    :class="getThemeClasses().select"
                    x-model="condition.value"
                >
                    <option value="true">True</option>
                    <option value="false">False</option>
                </select>
            </template>

            <!-- Remove button -->
            <button
                type="button"
                :class="getThemeClasses().removeButton"
                @click="removeCondition(index)"
            >
                &times;
            </button>
        </div>
    </template>

    <!-- Add button -->
    <button
        type="button"
        :class="getThemeClasses().addButton"
        @click="addCondition"
    >
        + Add Condition
    </button>

    <!-- Action buttons -->
    <div class="flex justify-end mt-4 space-x-2">
        <button
            type="button"
            :class="getThemeClasses().button"
            @click="resetFilters"
        >
            Reset
        </button>
        <button
            type="button"
            :class="getThemeClasses().button"
            @click="emitFilterChange"
        >
            Apply Filters
        </button>
    </div>
</div>
