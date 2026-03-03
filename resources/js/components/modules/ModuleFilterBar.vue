<template>
    <div class="form-inline mb-3">
        <input
            :value="searchValue"
            type="text"
            class="form-control mr-2"
            :placeholder="searchPlaceholder"
            @input="$emit('update:searchValue', $event.target.value)"
            style="min-width: 350px; width: 350px; max-width: 100%;"
            size="40"
        >
        <select
            :value="statusValue"
            class="form-control mr-2"
            @input="$emit('update:statusValue', $event.target.value)"
            @change="$emit('change')"
        >
            <option value="">All statuses</option>
            <option
                v-for="option in statusOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: 'ModuleFilterBar',
    props: {
        searchValue: {
            type: String,
            default: '',
        },
        searchPlaceholder: {
            type: String,
            default: '',
        },
        statusValue: {
            type: String,
            default: '',
        },
        statusOptions: {
            type: Array,
            default: () => [],
        },
    },
};
</script>
