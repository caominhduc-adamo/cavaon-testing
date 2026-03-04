<template>
    <nav v-if="show" :aria-label="ariaLabel">
        <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: isFirstPage || loading }">
                <button class="page-link" type="button" @click="onPrevious">
                    Previous
                </button>
            </li>
            <li
                v-for="item in pageItems"
                :key="item.key"
                class="page-item"
                :class="{ active: item.type === 'page' && item.value === currentPage, disabled: item.type === 'ellipsis' || loading }"
            >
                <button
                    v-if="item.type === 'page'"
                    class="page-link"
                    type="button"
                    @click="onPageClick(item.value)"
                >
                    {{ item.value }}
                </button>
                <span v-else class="page-link">...</span>
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
    computed: {
        pageItems() {
            const pages = [];
            const maxButtons = 7;

            if (this.lastPage <= maxButtons) {
                for (let page = 1; page <= this.lastPage; page += 1) {
                    pages.push({ type: 'page', value: page, key: `page-${page}` });
                }

                return pages;
            }

            const start = Math.max(2, this.currentPage - 1);
            const end = Math.min(this.lastPage - 1, this.currentPage + 1);

            pages.push({ type: 'page', value: 1, key: 'page-1' });

            if (start > 2) {
                pages.push({ type: 'ellipsis', key: 'ellipsis-start' });
            }

            for (let page = start; page <= end; page += 1) {
                pages.push({ type: 'page', value: page, key: `page-${page}` });
            }

            if (end < this.lastPage - 1) {
                pages.push({ type: 'ellipsis', key: 'ellipsis-end' });
            }

            pages.push({ type: 'page', value: this.lastPage, key: `page-${this.lastPage}` });

            return pages;
        },
    },
    methods: {
        onPrevious() {
            if (this.isFirstPage || this.loading) {
                return;
            }

            this.$emit('go-to-page', this.currentPage - 1);
        },
        onNext() {
            if (this.isLastPage || this.loading) {
                return;
            }

            this.$emit('go-to-page', this.currentPage + 1);
        },
        onPageClick(page) {
            if (this.loading || page === this.currentPage) {
                return;
            }

            this.$emit('go-to-page', page);
        },
    },
};
</script>
