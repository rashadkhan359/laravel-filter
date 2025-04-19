import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';

/**
 * FilterBuilder Component for React
 *
 * A UI component to build and manage filters for API requests
 */
const FilterBuilder = ({
  fields,
  onFilterChange,
  initialFilters = { logic: 'and', conditions: [] },
  theme = 'light',
}) => {
  const [filters, setFilters] = useState(initialFilters);
  const [searchTerm, setSearchTerm] = useState('');
  const [sortField, setSortField] = useState('');
  const [sortDirection, setSortDirection] = useState('asc');

  // Define theme styles
  const themeStyles = {
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
    },
  };

  const styles = themeStyles[theme] || themeStyles.light;

  // Effect to notify parent component of filter changes
  useEffect(() => {
    const filterData = {
      filters,
      search: searchTerm,
      sort: sortField ? `${sortField}:${sortDirection}` : '',
    };
    onFilterChange(filterData);
  }, [filters, searchTerm, sortField, sortDirection]);

  // Add a new filter condition
  const addCondition = () => {
    if (fields.length === 0) return;

    const newFilters = { ...filters };
    const defaultField = fields[0];

    newFilters.conditions = [
      ...newFilters.conditions,
      {
        field: defaultField.name,
        operator: getDefaultOperator(defaultField.type),
        value: '',
      }
    ];

    setFilters(newFilters);
  };

  // Remove a filter condition
  const removeCondition = (index) => {
    const newFilters = { ...filters };
    newFilters.conditions = newFilters.conditions.filter((_, i) => i !== index);
    setFilters(newFilters);
  };

  // Update a condition field
  const updateConditionField = (index, field) => {
    const newFilters = { ...filters };
    const fieldConfig = fields.find(f => f.name === field);

    newFilters.conditions[index] = {
      ...newFilters.conditions[index],
      field,
      operator: getDefaultOperator(fieldConfig.type),
      // Reset value when changing field type
      value: '',
    };

    setFilters(newFilters);
  };

  // Update a condition operator
  const updateConditionOperator = (index, operator) => {
    const newFilters = { ...filters };
    newFilters.conditions[index] = {
      ...newFilters.conditions[index],
      operator,
    };
    setFilters(newFilters);
  };

  // Update a condition value
  const updateConditionValue = (index, value) => {
    const newFilters = { ...filters };
    newFilters.conditions[index] = {
      ...newFilters.conditions[index],
      value,
    };
    setFilters(newFilters);
  };

  // Toggle the logic type (AND/OR)
  const toggleLogic = () => {
    setFilters({
      ...filters,
      logic: filters.logic === 'and' ? 'or' : 'and',
    });
  };

  // Get default operator based on field type
  const getDefaultOperator = (fieldType) => {
    switch (fieldType) {
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
  };

  // Get available operators for a field type
  const getOperatorsForFieldType = (fieldType) => {
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
  };

  // Render the value input based on field type and operator
  const renderValueInput = (condition, index) => {
    const field = fields.find(f => f.name === condition.field);
    if (!field) return null;

    // For null operators, don't show value input
    if (['is_null', 'is_not_null'].includes(condition.operator)) {
      return null;
    }

    switch (field.type) {
      case 'text':
        return (
          <input
            type="text"
            className={styles.input}
            value={condition.value || ''}
            onChange={(e) => updateConditionValue(index, e.target.value)}
          />
        );
      case 'number':
        return (
          <input
            type="number"
            className={styles.input}
            value={condition.value || ''}
            onChange={(e) => updateConditionValue(index, e.target.value)}
          />
        );
      case 'date':
        if (condition.operator === 'between') {
          return (
            <div className="flex space-x-2">
              <input
                type="date"
                className={styles.input}
                value={(condition.value && condition.value[0]) || ''}
                onChange={(e) => {
                  const newValue = [...(Array.isArray(condition.value) ? condition.value : ['', ''])];
                  newValue[0] = e.target.value;
                  updateConditionValue(index, newValue);
                }}
              />
              <input
                type="date"
                className={styles.input}
                value={(condition.value && condition.value[1]) || ''}
                onChange={(e) => {
                  const newValue = [...(Array.isArray(condition.value) ? condition.value : ['', ''])];
                  newValue[1] = e.target.value;
                  updateConditionValue(index, newValue);
                }}
              />
            </div>
          );
        }
        return (
          <input
            type="date"
            className={styles.input}
            value={condition.value || ''}
            onChange={(e) => updateConditionValue(index, e.target.value)}
          />
        );
      case 'boolean':
        return (
          <select
            className={styles.select}
            value={condition.value}
            onChange={(e) => updateConditionValue(index, e.target.value === 'true')}
          >
            <option value="true">True</option>
            <option value="false">False</option>
          </select>
        );
      default:
        return (
          <input
            type="text"
            className={styles.input}
            value={condition.value || ''}
            onChange={(e) => updateConditionValue(index, e.target.value)}
          />
        );
    }
  };

  return (
    <div className={styles.container}>
      <h3 className={styles.header}>Filter Builder</h3>

      {/* Search */}
      <div className={styles.searchContainer}>
        <label className={styles.label}>Search</label>
        <input
          type="text"
          className={styles.input}
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          placeholder="Search term..."
        />
      </div>

      {/* Sort */}
      <div className={styles.sortContainer}>
        <div>
          <label className={styles.label}>Sort By</label>
          <select
            className={styles.select}
            value={sortField}
            onChange={(e) => setSortField(e.target.value)}
          >
            <option value="">None</option>
            {fields.map((field) => (
              <option key={field.name} value={field.name}>
                {field.label || field.name}
              </option>
            ))}
          </select>
        </div>

        {sortField && (
          <div>
            <label className={styles.label}>Direction</label>
            <select
              className={styles.select}
              value={sortDirection}
              onChange={(e) => setSortDirection(e.target.value)}
            >
              <option value="asc">Ascending</option>
              <option value="desc">Descending</option>
            </select>
          </div>
        )}
      </div>

      {/* Logic toggle */}
      <div className={styles.logicSwitch}>
        <span>Combine filters with:</span>
        <button
          type="button"
          className={`${styles.button} text-sm px-2 py-1 ${filters.logic === 'and' ? 'opacity-100' : 'opacity-50'}`}
          onClick={toggleLogic}
        >
          AND
        </button>
        <button
          type="button"
          className={`${styles.button} text-sm px-2 py-1 ${filters.logic === 'or' ? 'opacity-100' : 'opacity-50'}`}
          onClick={toggleLogic}
        >
          OR
        </button>
      </div>

      {/* Filter conditions */}
      {filters.conditions.map((condition, index) => {
        const field = fields.find(f => f.name === condition.field);

        return (
          <div key={index} className={styles.filterRow}>
            {/* Field selector */}
            <select
              className={styles.select}
              value={condition.field}
              onChange={(e) => updateConditionField(index, e.target.value)}
            >
              {fields.map((field) => (
                <option key={field.name} value={field.name}>
                  {field.label || field.name}
                </option>
              ))}
            </select>

            {/* Operator selector */}
            <select
              className={styles.select}
              value={condition.operator}
              onChange={(e) => updateConditionOperator(index, e.target.value)}
            >
              {field && getOperatorsForFieldType(field.type).map(op => (
                <option key={op.value} value={op.value}>
                  {op.label}
                </option>
              ))}
            </select>

            {/* Value input */}
            {renderValueInput(condition, index)}

            {/* Remove button */}
            <button
              type="button"
              className={styles.removeButton}
              onClick={() => removeCondition(index)}
            >
              &times;
            </button>
          </div>
        );
      })}

      {/* Add button */}
      <button
        type="button"
        className={styles.addButton}
        onClick={addCondition}
      >
        + Add Condition
      </button>
    </div>
  );
};

FilterBuilder.propTypes = {
  fields: PropTypes.arrayOf(
    PropTypes.shape({
      name: PropTypes.string.isRequired,
      label: PropTypes.string,
      type: PropTypes.oneOf(['text', 'number', 'date', 'boolean']).isRequired,
    })
  ).isRequired,
  onFilterChange: PropTypes.func.isRequired,
  initialFilters: PropTypes.object,
  theme: PropTypes.oneOf(['light', 'dark']),
};

export default FilterBuilder;
