<template>
    <div class="filter-builder">
        <div class="filter-builder-header">
            <h3 class="filter-builder-title">{{ title }}</h3>
            <div class="filter-builder-actions">
                <button @click="addFilter" class="filter-builder-add-btn">Add Filter</button>
                <button @click="applyFilters" class="filter-builder-apply-btn">Apply Filters</button>
                <button @click="resetFilters" class="filter-builder-reset-btn">Reset</button>
            </div>
        </div>

        <div class="filter-builder-content">
            <!-- Search field -->
            <div class="filter-builder-search">
                <input type="text" v-model="searchQuery" placeholder="Search..." @keyup.enter="applyFilters"
                    class="filter-builder-search-input" />
            </div>

            <!-- Active filters -->
            <div class="filter-builder-filters">
                <div v-for="(filter, index) in filters" :key="index" class="filter-builder-filter">
                    <div class="filter-builder-filter-row">
                        <!-- Field selection -->
                        <select v-model="filter.field" class="filter-builder-select">
                            <option v-for="field in availableFields" :key="field" :value="field">
                                {{ fieldLabels[field] || field }}
                            </option>
                        </select>

                        <!-- Operator selection -->
                        <select v-model="filter.operator" class="filter-builder-select">
                            <option v-for="op in getOperatorsForField(filter.field)" :key="op" :value="op">
                                {{ operatorLabels[op] || op }}
                            </option>
                        </select>

                        <!-- Value input based on field type -->
                        <div class="filter-builder-value">
                            <!-- Text input -->
                            <input v-if="getFieldType(filter.field) === 'text'" type="text" v-model="filter.value"
                                class="filter-builder-input" />

                            <!-- Number input -->
                            <input v-else-if="getFieldType(filter.field) === 'number'" type="number"
                                v-model.number="filter.value" class="filter-builder-input" />

                            <!-- Date input -->
                            <input v-else-if="getFieldType(filter.field) === 'date'" type="date" v-model="filter.value"
                                class="filter-builder-input" />

                            <!-- Boolean input -->
                            <select v-else-if="getFieldType(filter.field) === 'boolean'" v-model="filter.value"
                                class="filter-builder-select">
                                <option :value="true">Yes</option>
                                <option :value="false">No</option>
                            </select>

                            <!-- Enum input -->
                            <select v-else-if="getFieldType(filter.field) === 'enum'" v-model="filter.value"
                                class="filter-builder-select">
                                <option v-for="option in getFieldOptions(filter.field)" :key="option.value"
                                    :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Remove button -->
                        <button @click="removeFilter(index)" class="filter-builder-remove-btn">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'FilterBuilder',

    props: {
        title: {
            type: String,
            default: 'Filter Data'
        },
        schema: {
            type: Object,
            required: true
        },
        initialFilters: {
            type: Array,
            default: () => []
        },
        initialSearch: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            filters: this.initialFilters.length ? [...this.initialFilters] : [],
            searchQuery: this.initialSearch,
            fieldLabels: {},
            operatorLabels: {
                eq: 'Equals',
                neq: 'Not equals',
                gt: 'Greater than',
                gte: 'Greater than or equal',
                lt: 'Less than',
                lte: 'Less than or equal',
                like: 'Contains',
                in: 'In list',
                between: 'Between',
                null: 'Is empty',
                not_null: 'Is not empty'
            },
            fieldTypes: {} // Will be populated from schema
        };
    },

    computed: {
        availableFields() {
            return Object.keys(this.schema.allowed_filters || {});
        }
    },

    created() {
        this.initializeSchema();
    },

    methods: {
        initializeSchema() {
            // Process field labels and types from schema
            if (this.schema.metadata) {
                this.schema.metadata.forEach(field => {
                    this.fieldLabels[field.name] = field.label || field.name;
                    this.fieldTypes[field.name] = field.type || 'text';
                });
            }
        },

        addFilter() {
            const defaultField = this.availableFields[0];
            const defaultOperator = this.schema.allowed_filters[defaultField]?.[0] || 'eq';

            this.filters.push({
                field: defaultField,
                operator: defaultOperator,
                value: this.getDefaultValueForType(this.getFieldType(defaultField))
            });
        },

        removeFilter(index) {
            this.filters.splice(index, 1);
        },

        resetFilters() {
            this.filters = [];
            this.searchQuery = '';
            this.$emit('reset');
        },

        applyFilters() {
            const filterParams = {
                filters: this.filters.map(f => ({
                    field: f.field,
                    operator: f.operator,
                    value: f.value
                })),
                search: this.searchQuery
            };

            this.$emit('apply', filterParams);
        },

        getOperatorsForField(field) {
            return this.schema.allowed_filters[field] || [];
        },

        getFieldType(field) {
            return this.fieldTypes[field] || 'text';
        },

        getFieldOptions(field) {
            const fieldSchema = (this.schema.metadata || []).find(f => f.name === field);
            return fieldSchema?.options || [];
        },

        getDefaultValueForType(type) {
            switch (type) {
                case 'number':
                    return 0;
                case 'boolean':
                    return true;
                case 'date':
                    return new Date().toISOString().split('T')[0];
                case 'enum':
                    return '';
                default:
                    return '';
            }
        }
    }
};
</script>

<style scoped>
.filter-builder {
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8fafc;
}

.filter-builder-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.filter-builder-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.filter-builder-actions {
    display: flex;
    gap: 0.5rem;
}

.filter-builder-add-btn,
.filter-builder-apply-btn,
.filter-builder-reset-btn,
.filter-builder-remove-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.filter-builder-add-btn {
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
}

.filter-builder-apply-btn {
    background-color: #3b82f6;
    border: 1px solid #2563eb;
    color: white;
}

.filter-builder-reset-btn {
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #64748b;
}

.filter-builder-remove-btn {
    background-color: #fee2e2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}

.filter-builder-search {
    margin-bottom: 1rem;
}

.filter-builder-search-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    border: 1px solid #cbd5e1;
}

.filter-builder-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.filter-builder-filter {
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    padding: 0.75rem;
}

.filter-builder-filter-row {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.filter-builder-select,
.filter-builder-input {
    padding: 0.375rem 0.5rem;
    border-radius: 0.25rem;
    border: 1px solid #cbd5e1;
    background-color: white;
}

.filter-builder-select {
    min-width: 8rem;
}

.filter-builder-value {
    flex: 1;
}

.filter-builder-input {
    width: 100%;
}
</style>
