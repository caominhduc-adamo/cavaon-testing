<template>
    <div class="container py-5">
        <div class="card">
            <div class="card-body text-center py-5">
                <h1 class="display-4 mb-3">404</h1>
                <p class="lead mb-4">The page you are looking for does not exist.</p>
                <router-link :to="{ name: 'tours.index' }" class="btn btn-primary">
                    Go to Tour Management
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NotFoundPage',
};
</script>
