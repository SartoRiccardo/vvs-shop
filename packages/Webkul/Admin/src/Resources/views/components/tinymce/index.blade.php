<v-tinymce {{ $attributes }}></v-tinymce>

@pushOnce('scripts')
    <!--
        Markdown conversion for markdown mode. Loaded as a classic script so
        it executes before the deferred module below mounts any editors.
    -->
    <script
        src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    ></script>

    <script
        type="text/x-template"
        id="v-tinymce-template"
    >
        <div>
            <!-- Mode switcher -->
            <div class="mb-1.5 inline-flex overflow-hidden rounded-md border border-gray-300 dark:border-gray-600">
                <button
                    type="button"
                    @click="setMode('html')"
                    :class="mode === 'html'
                        ? 'bg-gray-800 font-semibold text-white dark:bg-gray-100 dark:text-gray-900'
                        : 'bg-transparent text-gray-600 dark:text-gray-300'"
                    class="px-3 py-1 text-xs transition-all"
                >
                    HTML
                </button>

                <button
                    type="button"
                    @click="setMode('markdown')"
                    :class="mode === 'markdown'
                        ? 'bg-gray-800 font-semibold text-white dark:bg-gray-100 dark:text-gray-900'
                        : 'bg-transparent text-gray-600 dark:text-gray-300'"
                    class="px-3 py-1 text-xs transition-all"
                >
                    Markdown
                </button>
            </div>

            <!-- Editor surface -->
            <textarea
                ref="editor"
                v-model="content"
                @input="push"
                class="block min-h-[60vh] w-full resize-y rounded-md border border-gray-300 px-3 py-2.5 font-mono text-[13px] leading-relaxed text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
            ></textarea>

            <!-- Markdown live preview -->
            <div
                v-if="mode === 'markdown' && content"
                class="mt-2 rounded-md border border-gray-200 p-4 text-sm leading-relaxed text-gray-600 dark:border-gray-800 dark:text-gray-300 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-gray-300 [&_blockquote]:pl-3 [&_code]:font-mono [&_img]:max-w-full [&_pre]:overflow-x-auto [&_pre]:rounded [&_pre]:bg-gray-100 [&_pre]:p-3 dark:[&_blockquote]:border-gray-700 dark:[&_pre]:bg-gray-800"
                v-html="preview"
            ></div>

            <p class="mt-1 text-xs italic text-gray-600 dark:text-gray-300">
                <span v-if="mode === 'markdown'">
                    @lang('admin::app.components.tinymce.markdown-hint')
                </span>

                <span v-else>
                    @lang('admin::app.components.tinymce.html-hint')
                </span>
            </p>
        </div>
    </script>

    <script type="module">
        app.component('v-tinymce', {
            template: '#v-tinymce-template',

            props: ['selector', 'field'],

            data() {
                return {
                    mode: localStorage.getItem('bagisto-editor-mode') || 'html',

                    content: '',
                };
            },

            computed: {
                preview() {
                    if (this.mode !== 'markdown' || ! window.marked) {
                        return '';
                    }

                    return window.marked.parse(this.content ?? '');
                },
            },

            mounted() {
                /**
                 * The caller (control component / editor field type) renders a
                 * vee-validate bound textarea holding the initial value; hide
                 * it and take over, pushing values back through field.onInput
                 * like TinyMCE did.
                 */
                const original = document.querySelector(this.selector);

                if (original) {
                    this.content = original.value;

                    original.style.display = 'none';
                }

                /**
                 * Existing content is stored HTML: editing it in markdown mode
                 * would pipe it through the markdown converter on save, so
                 * fall back to HTML mode for HTML-looking content. The saved
                 * mode preference is not overwritten.
                 */
                if (this.mode === 'markdown' && /<\/?[a-z][^>]*>/i.test(this.content)) {
                    this.mode = 'html';
                }

                this.push();
            },

            methods: {
                setMode(mode) {
                    this.mode = mode;

                    localStorage.setItem('bagisto-editor-mode', mode);

                    this.push();
                },

                push() {
                    /**
                     * Markdown converts to HTML before going up; HTML mode
                     * sends the source verbatim. Server-side purification
                     * (clean_content) still applies to whatever arrives.
                     */
                    const outgoing = this.mode === 'markdown' && window.marked
                        ? window.marked.parse(this.content ?? '')
                        : this.content;

                    this.field?.onInput?.(outgoing);
                },
            },
        });
    </script>
@endPushOnce
