<template>
    <nav v-if="show" :aria-label="ariaLabel">
        <ul class="pagination">
            <li class="page-item" :class="{ disabled: isFirstPage || loading }">
                <button class="page-link" type="button" @click="onPrevious">
                    Previous
                </button>
            </li>
            <li class="page-item disabled">
                <span class="page-link">
                    Page {{ currentPage }} of {{ lastPage }}
                </span>
            </li>
            <li class="page-item" :class="{ disabled: isLastPage || loading }">
                <button class="page-link" type="button" @click="onNext">
                    Next
                </button>
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: 'ModulePagination',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        ariaLabel: {
            type: String,
            required: true,
        },
        isFirstPage: {
            type: Boolean,
            default: false,
        },
        isLastPage: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        currentPage: {
            type: Number,
            default: 1,
        },
        lastPage: {
            type: Number,
            default: 1,
        },
    },
    methods: {
        onPrevious() {
            this.$emit('go-to-page', this.currentPage - 1);
        },
        onNext() {
            this.$emit('go-to-page', this.currentPage + 1);
        },
    },
};
</script>
