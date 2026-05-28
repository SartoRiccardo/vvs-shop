<x-admin::layouts>
    <x-slot:title>Newsletter Tags</x-slot>

    <v-subscriber-tags>
        <div class="flex justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">Newsletter Tags</p>
            <div class="primary-button">Create Tag</div>
        </div>
        <x-admin::shimmer.datagrid />
    </v-subscriber-tags>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-subscriber-tags-template">
            <div>
                <div class="flex justify-between gap-4 max-sm:flex-wrap">
                    <p class="text-xl font-bold text-gray-800 dark:text-white">Newsletter Tags</p>
                    <div class="primary-button" @click="openCreate">Create Tag</div>
                </div>

                <!-- Table -->
                <div class="mt-4 rounded-lg border bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="grid grid-cols-4 gap-2.5 border-b px-4 py-3 font-semibold text-gray-600 dark:border-gray-800 dark:text-gray-300">
                        <p>ID</p>
                        <p>Name</p>
                        <p>Slug</p>
                        <p>Auto-assign on purchase</p>
                    </div>

                    <template v-if="isLoading">
                        <div class="px-4 py-6 text-center text-gray-400">Loading…</div>
                    </template>

                    <template v-else-if="tags.length === 0">
                        <div class="px-4 py-6 text-center text-gray-400">No tags yet.</div>
                    </template>

                    <template v-else>
                        <div
                            v-for="tag in tags"
                            :key="tag.id"
                            class="grid grid-cols-4 items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                        >
                            <p>@{{ tag.id }}</p>
                            <p>@{{ tag.name }}</p>
                            <p class="font-mono text-sm">@{{ tag.slug }}</p>
                            <div class="flex items-center justify-between">
                                <span
                                    :class="tag.auto_assign_on_purchase ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                >
                                    @{{ tag.auto_assign_on_purchase ? 'Yes' : 'No' }}
                                </span>
                                <div class="flex gap-1">
                                    <span
                                        class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                        @click="openEdit(tag)"
                                    ></span>
                                    <span
                                        class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                        @click="destroy(tag.id)"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Create / Edit Modal -->
                <x-admin::form v-slot="{ meta, errors, handleSubmit }" as="div" ref="modalForm">
                    <form @submit="handleSubmit($event, save)" ref="tagForm">
                        <x-admin::modal ref="tagModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @{{ form.id ? 'Edit Tag' : 'Create Tag' }}
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <x-admin::form.control-group.control type="hidden" name="id" v-model="form.id" />

                                <!-- Name -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">Name</x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        v-model="form.name"
                                        placeholder="e.g. Contest Fans"
                                        label="Name"
                                    />
                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>

                                <!-- Slug -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="slug"
                                        v-model="form.slug"
                                        placeholder="auto-generated if empty"
                                        label="Slug"
                                    />
                                    <x-admin::form.control-group.error control-name="slug" />
                                </x-admin::form.control-group>

                                <!-- Auto-assign -->
                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>Auto-assign on any purchase</x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="auto_assign_on_purchase"
                                        v-model="form.auto_assign_on_purchase"
                                        label="Auto-assign on purchase"
                                    >
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </x-admin::form.control-group.control>
                                </x-admin::form.control-group>
                            </x-slot>

                            <x-slot:footer>
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    title="Save"
                                    ::loading="isSaving"
                                    ::disabled="isSaving"
                                />
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-subscriber-tags', {
                template: '#v-subscriber-tags-template',

                data() {
                    return {
                        tags: [],
                        isLoading: true,
                        isSaving: false,
                        form: { id: null, name: '', slug: '', auto_assign_on_purchase: 0 },
                    };
                },

                mounted() {
                    this.fetch();
                },

                methods: {
                    fetch() {
                        this.isLoading = true;
                        this.$axios.get("{{ route('admin.marketing.communications.subscriber_tags.all') }}")
                            .then(r => { this.tags = r.data; this.isLoading = false; });
                    },

                    openCreate() {
                        this.form = { id: null, name: '', slug: '', auto_assign_on_purchase: 0 };
                        this.$refs.tagModal.toggle();
                    },

                    openEdit(tag) {
                        this.form = { ...tag, auto_assign_on_purchase: tag.auto_assign_on_purchase ? 1 : 0 };
                        this.$refs.tagModal.toggle();
                    },

                    save(params, { setErrors }) {
                        this.isSaving = true;
                        const url = params.id
                            ? `/admin/marketing/communications/subscriber-tags/${params.id}`
                            : "{{ route('admin.marketing.communications.subscriber_tags.store') }}";

                        const formData = new FormData(this.$refs.tagForm);
                        if (params.id) formData.append('_method', 'put');

                        this.$axios.post(url, formData)
                            .then(r => {
                                this.isSaving = false;
                                this.$refs.tagModal.toggle();
                                this.fetch();
                                this.$emitter.emit('add-flash', { type: 'success', message: r.data.message });
                            })
                            .catch(err => {
                                this.isSaving = false;
                                if (err.response?.status === 422) setErrors(err.response.data.errors);
                            });
                    },

                    destroy(id) {
                        if (! confirm('Delete this tag?')) return;
                        this.$axios.delete(`/admin/marketing/communications/subscriber-tags/${id}`)
                            .then(r => {
                                this.fetch();
                                this.$emitter.emit('add-flash', { type: 'success', message: r.data.message });
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
