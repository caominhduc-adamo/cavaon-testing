<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">{{ title }}</h1>
        <div>
            <button
                v-if="showCreateButton"
                type="button"
                class="btn btn-success"
                @click="$emit('create')"
            >
                {{ createLabel }}
            </button>
            <slot name="actions"></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ModulePageHeader',
    props: {
        title: {
            type: String,
            required: true,
        },
        showCreateButton: {
            type: Boolean,
            default: false,
        },
        createLabel: {
            type: String,
            default: 'Create',
        },
    },
};
</script>
