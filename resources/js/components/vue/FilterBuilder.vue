<template>
  <div :class="themeClasses.container">
    <h3 :class="themeClasses.header">Filter Builder</h3>

    <!-- Search -->
    <div :class="themeClasses.searchContainer">
      <label :class="themeClasses.label">Search</label>
      <input
        type="text"
        :class="themeClasses.input"
        v-model="searchTerm"
        placeholder="Search term..."
      />
    </div>

    <!-- Sort -->
    <div :class="themeClasses.sortContainer">
      <div>
        <label :class="themeClasses.label">Sort By</label>
        <select
          :class="themeClasses.select"
          v-model="sortField"
        >
          <option value="">None</option>
          <option
            v-for="field in fields"
            :key="field.name"
            :value="field.name"
          >
            {{ field.label || field.name }}
          </option>
        </select>
      </div>

      <div v-if="sortField">
        <label :class="themeClasses.label">Direction</label>
        <select
          :class="themeClasses.select"
          v-model="sortDirection"
        >
          <option value="asc">Ascending</option>
          <option value="desc">Descending</option>
        </select>
      </div>
    </div>

    <!-- Logic toggle -->
    <div :class="themeClasses.logicSwitch">
      <span>Combine filters with:</span>
      <button
        type="button"
        :class="[themeClasses.button, 'text-sm px-2 py-1', filters.logic === 'and' ? 'opacity-100' : 'opacity-50']"
        @click="toggleLogic('and')"
      >
        AND
      </button>
      <button
        type="button"
        :class="[themeClasses.button, 'text-sm px-2 py-1', filters.logic === 'or' ? 'opacity-100' : 'opacity-50']"
        @click="toggleLogic('or')"
      >
        OR
      </button>
    </div>

    <!-- Filter conditions -->
    <div
      v-for="(condition, index) in filters.conditions"
      :key="index"
      :class="themeClasses.filterRow"
    >
      <!-- Field selector -->
      <select
        :class="themeClasses.select"
        v-model="condition.field"
        @change="updateConditionField(index, condition.field)"
      >
        <option
          v-for="field in fields"
          :key="field.name"
          :value="field.name"
        >
          {{ field.label || field.name }}
        </option>
      </select>

      <!-- Operator selector -->
      <select
        :class="themeClasses.select"
        v-model="condition.operator"
      >
        <option
          v-for="op in getOperatorsForField(condition.field)"
          :key="op.value"
          :value="op.value"
        >
          {{ op.label }}
        </option>
      </select>

      <!-- Value input -->
      <component
        :is="getValueInputComponent(condition)"
        v-if="!['is_null', 'is_not_null'].includes(condition.operator)"
        :condition="condition"
        :index="index"
        :class="themeClasses.input"
        @update:value="updateConditionValue(index, $event)"
      />

      <!-- Remove button -->
      <button
        type="button"
        :class="themeClasses.removeButton"
        @click="removeCondition(index)"
      >
        &times;
      </button>
    </div>

    <!-- Add button -->
    <button
      type="button"
      :class="themeClasses.addButton"
      @click="addCondition"
    >
      + Add Condition
    </button>
  </div>
</template>

<script>
export default {
  name: 'FilterBuilder',
  props: {
    fields: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(field =>
          field.name &&
          ['text', 'number', 'date', 'boolean'].includes(field.type)
        );
      }
    },
    initialFilters: {
      type: Object,
      default: () => ({ logic: 'and', conditions: [] })
    },
    theme: {
      type: String,
      default: 'light',
      validator: (value) => ['light', 'dark'].includes(value)
    }
  },

  data() {
    return {
      filters: this.initialFilters,
      searchTerm: '',
      sortField: '',
      sortDirection: 'asc',

      themes: {
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
      }
    };
  },

  computed: {
    themeClasses() {
      return this.themes[this.theme] || this.themes.light;
    },

    filterData() {
      return {
        filters: this.filters,
        search: this.searchTerm,
        sort: this.sortField ? `${this.sortField}:${this.sortDirection}` : '',
      };
    }
  },

  watch: {
    filterData: {
      deep: true,
      handler(newValue) {
        this.$emit('filter-change', newValue);
      }
    }
  },

  methods: {
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

      this.filters.conditions[index].operator = this.getDefaultOperator(field.type);
      this.filters.conditions[index].value = '';
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

    getValueInputComponent(condition) {
      const field = this.fields.find(f => f.name === condition.field);
      if (!field) return 'input';

      switch (field.type) {
        case 'date':
          return condition.operator === 'between' ? 'date-range-input' : 'date-input';
        case 'boolean':
          return 'boolean-input';
        default:
          return 'text-input';
      }
    }
  },

  components: {
    'text-input': {
      props: ['condition', 'index'],
      template: `
        <input
          type="text"
          :value="condition.value || ''"
          @input="$emit('update:value', $event.target.value)"
        />
      `
    },
    'date-input': {
      props: ['condition', 'index'],
      template: `
        <input
          type="date"
          :value="condition.value || ''"
          @input="$emit('update:value', $event.target.value)"
        />
      `
    },
    'date-range-input': {
      props: ['condition', 'index'],
      computed: {
        startDate() {
          return Array.isArray(this.condition.value) ? this.condition.value[0] || '' : '';
        },
        endDate() {
          return Array.isArray(this.condition.value) ? this.condition.value[1] || '' : '';
        }
      },
      methods: {
        updateRange(index, value) {
          const range = Array.isArray(this.condition.value) ? [...this.condition.value] : ['', ''];
          range[index] = value;
          this.$emit('update:value', range);
        }
      },
      template: `
        <div class="flex space-x-2">
          <input
            type="date"
            :value="startDate"
            @input="updateRange(0, $event.target.value)"
          />
          <input
            type="date"
            :value="endDate"
            @input="updateRange(1, $event.target.value)"
          />
        </div>
      `
    },
    'boolean-input': {
      props: ['condition', 'index'],
      template: `
        <select
          :value="condition.value === true ? 'true' : condition.value === false ? 'false' : ''"
          @change="$emit('update:value', $event.target.value === 'true')"
        >
          <option value="true">True</option>
          <option value="false">False</option>
        </select>
      `
    }
  }
};
</script>
