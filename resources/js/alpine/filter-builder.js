/**
 * Laravel Filter - Alpine.js Integration
 *
 * Usage:
 * <div x-data="filterBuilder(fields)">
 *   <!-- Filter UI -->
 * </div>
 */

window.filterBuilder = function(fields, options = {}) {
  return {
    fields: fields || [],
    filters: {
      logic: 'and',
      conditions: []
    },
    searchTerm: '',
    sortField: '',
    sortDirection: 'asc',
    theme: options.theme || 'light',

    init() {
      if (options.initialFilters) {
        this.filters = options.initialFilters;
      }

      // Listen for filter changes
      this.$watch('filters', () => this.emitFilterChange());
      this.$watch('searchTerm', () => this.emitFilterChange());
      this.$watch('sortField', () => this.emitFilterChange());
      this.$watch('sortDirection', () => this.emitFilterChange());
    },

    addCondition() {
      if (this.fields.length === 0) return;

      const defaultField = this.fields[0];

      this.filters.conditions.push({
        field: defaultField.name,
        operator: this.getDefaultOperator(defaultField.type),
        value: '',
      });
    },

    removeCondition(index) {
      this.filters.conditions.splice(index, 1);
    },

    updateConditionField(index, fieldName) {
      const field = this.fields.find(f => f.name === fieldName);
      if (!field) return;

      this.filters.conditions[index].field = fieldName;
      this.filters.conditions[index].operator = this.getDefaultOperator(field.type);
      this.filters.conditions[index].value = '';
    },

    updateConditionOperator(index, operator) {
      this.filters.conditions[index].operator = operator;
    },

    updateConditionValue(index, value) {
      this.filters.conditions[index].value = value;
    },

    toggleLogic(logic) {
      this.filters.logic = logic;
    },

    getDefaultOperator(fieldType) {
      switch (fieldType) {
        case 'text': return 'contains';
        case 'number': return 'eq';
        case 'date': return 'eq';
        case 'boolean': return 'eq';
        default: return 'eq';
      }
    },

    getOperatorsForField(fieldName) {
      const field = this.fields.find(f => f.name === fieldName);
      if (!field) return [];

      return this.getOperatorsForFieldType(field.type);
    },

    getOperatorsForFieldType(fieldType) {
      switch (fieldType) {
        case 'text':
          return [
            { value: 'eq', label: 'Equals' },
            { value: 'neq', label: 'Not Equals' },
            { value: 'contains', label: 'Contains' },
            { value: 'starts_with', label: 'Starts With' },
            { value: 'ends_with', label: 'Ends With' },
            { value: 'in', label: 'In' },
            { value: 'not_in', label: 'Not In' },
            { value: 'is_null', label: 'Is Null' },
            { value: 'is_not_null', label: 'Is Not Null' },
          ];
        case 'number':
          return [
            { value: 'eq', label: 'Equals' },
            { value: 'neq', label: 'Not Equals' },
            { value: 'gt', label: 'Greater Than' },
            { value: 'gte', label: 'Greater Than or Equal' },
            { value: 'lt', label: 'Less Than' },
            { value: 'lte', label: 'Less Than or Equal' },
            { value: 'in', label: 'In' },
            { value: 'not_in', label: 'Not In' },
            { value: 'is_null', label: 'Is Null' },
            { value: 'is_not_null', label: 'Is Not Null' },
          ];
        case 'date':
          return [
            { value: 'eq', label: 'Equals' },
            { value: 'neq', label: 'Not Equals' },
            { value: 'gt', label: 'After' },
            { value: 'gte', label: 'After or On' },
            { value: 'lt', label: 'Before' },
            { value: 'lte', label: 'Before or On' },
            { value: 'between', label: 'Between' },
            { value: 'is_null', label: 'Is Null' },
            { value: 'is_not_null', label: 'Is Not Null' },
          ];
        case 'boolean':
          return [
            { value: 'eq', label: 'Equals' },
            { value: 'is_null', label: 'Is Null' },
            { value: 'is_not_null', label: 'Is Not Null' },
          ];
        default:
          return [
            { value: 'eq', label: 'Equals' },
            { value: 'neq', label: 'Not Equals' },
          ];
      }
    },

    needsValueInput(operator) {
      return !['is_null', 'is_not_null'].includes(operator);
    },

    resetFilters() {
      this.filters = {
        logic: 'and',
        conditions: []
      };
      this.searchTerm = '';
      this.sortField = '';
      this.sortDirection = 'asc';

      this.emitFilterChange();
    },

    emitFilterChange() {
      const filterData = {
        filters: this.filters,
        search: this.searchTerm,
        sort: this.sortField ? `${this.sortField}:${this.sortDirection}` : '',
      };

      // Emit custom event for filter changes
      this.$dispatch('filter-changed', filterData);

      // Execute callback if provided
      if (typeof options.onFilterChange === 'function') {
        options.onFilterChange(filterData);
      }
    },

    getThemeClasses() {
      const themes = {
        light: {
          container: 'bg-white border border-gray-200 rounded-lg p-4 shadow-sm',
          header: 'text-gray-800 text-lg font-medium mb-4',
          button: 'bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded',
          input: 'border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
          select: 'border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
          label: 'block text-gray-700 text-sm font-medium mb-1',
          removeButton: 'text-red-500 hover:text-red-700',
          addButton: 'text-blue-500 hover:text-blue-700',
          logicSwitch: 'flex items-center space-x-2 mb-4',
          filterRow: 'flex items-center space-x-2 mb-2',
          searchContainer: 'mb-4',
          sortContainer: 'mb-4 flex items-center space-x-2',
        },
        dark: {
          container: 'bg-gray-800 border border-gray-700 rounded-lg p-4 shadow-md',
          header: 'text-white text-lg font-medium mb-4',
          button: 'bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded',
          input: 'bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
          select: 'bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
          label: 'block text-gray-300 text-sm font-medium mb-1',
          removeButton: 'text-red-400 hover:text-red-300',
          addButton: 'text-blue-400 hover:text-blue-300',
          logicSwitch: 'flex items-center space-x-2 mb-4 text-white',
          filterRow: 'flex items-center space-x-2 mb-2',
          searchContainer: 'mb-4',
          sortContainer: 'mb-4 flex items-center space-x-2',
        }
      };

      return themes[this.theme] || themes.light;
    }
  };
};
