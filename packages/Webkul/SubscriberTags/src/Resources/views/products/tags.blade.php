<div
    class="mt-4 rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
    id="v-product-subscriber-tags-wrap"
>
    <p class="mb-3 font-semibold text-gray-700 dark:text-gray-300">Newsletter Tags</p>
    <p class="mb-3 text-sm text-gray-500">Tags assigned to subscribers when this product is purchased.</p>

    <div id="v-product-subscriber-tags-component">
        <v-product-subscriber-tags product-id="{{ $product->id }}" />
    </div>
</div>

@pushOnce('scripts')
    <script type="text/x-template" id="v-product-subscriber-tags-template">
        <div>
            <template v-if="isLoading">
                <p class="text-sm text-gray-400">Loading tags…</p>
            </template>
            <template v-else>
                <div class="flex flex-wrap gap-2 mb-3">
                    <template v-if="allTags.length === 0">
                        <p class="text-sm text-gray-400">No tags defined yet. Create them under Marketing → Communications → Newsletter Tags.</p>
                    </template>
                    <label
                        v-for="tag in allTags"
                        :key="tag.id"
                        class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-sm transition-all"
                        :class="selectedIds.includes(tag.id)
                            ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                            : 'border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-400'"
                    >
                        <input
                            type="checkbox"
                            class="hidden"
                            :value="tag.id"
                            v-model="selectedIds"
                        />
                        @{{ tag.name }}
                    </label>
                </div>

                <button
                    type="button"
                    class="primary-button"
                    :disabled="isSaving"
                    @click="save"
                >
                    @{{ isSaving ? 'Saving…' : 'Save Tags' }}
                </button>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-product-subscriber-tags', {
            template: '#v-product-subscriber-tags-template',

            props: { productId: { type: Number, required: true } },

            data() {
                return { allTags: [], selectedIds: [], isLoading: true, isSaving: false };
            },

            mounted() {
                this.$axios.get(`/admin/catalog/products/${this.productId}/subscriber-tags`)
                    .then(r => {
                        this.allTags    = r.data.all;
                        this.selectedIds = r.data.selected;
                        this.isLoading  = false;
                    });
            },

            methods: {
                save() {
                    this.isSaving = true;
                    this.$axios.put(`/admin/catalog/products/${this.productId}/subscriber-tags`, {
                        tag_ids: this.selectedIds,
                    })
                    .then(r => {
                        this.isSaving = false;
                        this.$emitter.emit('add-flash', { type: 'success', message: r.data.message });
                    })
                    .catch(() => { this.isSaving = false; });
                },
            },
        });
    </script>
@endPushOnce
