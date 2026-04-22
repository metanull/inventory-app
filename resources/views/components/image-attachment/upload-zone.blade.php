@props([
    'action',                          // POST URL for file upload (required)
    'multiple' => false,               // Whether multiple files are allowed
    'accept' => 'image/jpeg,image/png,image/webp,image/jpg', // MIME types to accept
    'successRedirect' => null,         // URL to redirect to after successful upload
    'successEvent' => 'image-uploaded', // Browser event name to emit on success
    'csrfToken' => null,               // CSRF token (defaults to Laravel csrf_token())
])

<div
    x-data="imageUploadZone({
        action: {{ json_encode($action) }},
        multiple: {{ $multiple ? 'true' : 'false' }},
        csrfToken: {{ json_encode($csrfToken ?? csrf_token()) }},
        successRedirect: {{ json_encode($successRedirect) }},
        successEvent: {{ json_encode($successEvent) }},
    })"
    class="space-y-4"
>
    {{-- Drop Zone --}}
    <div
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop($event)"
        :class="dragOver
            ? 'border-teal-500 bg-teal-50'
            : 'border-gray-300 bg-white hover:border-teal-400 hover:bg-gray-50'"
        class="relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed px-6 py-10 text-center transition-colors duration-200 cursor-pointer"
        @click="$refs.fileInput.click()"
    >
        <div class="space-y-2">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="text-sm text-gray-600">
                <span class="font-medium text-teal-600">Click to upload</span>
                <span> or drag and drop</span>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, JPEG, WEBP accepted</p>
        </div>

        <input
            x-ref="fileInput"
            type="file"
            class="sr-only"
            :multiple="{{ $multiple ? 'true' : 'false' }}"
            accept="{{ $accept }}"
            @change="handleFileSelect($event)"
        >
    </div>

    {{-- Upload Button --}}
    <div class="flex items-center justify-between">
        <x-ui.button
            type="button"
            variant="secondary"
            @click.prevent="$refs.fileInput.click()"
        >
            <svg class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            Select Image{{ $multiple ? 's' : '' }}
        </x-ui.button>

        <template x-if="files.length > 0">
            <span class="text-sm text-gray-500" x-text="files.length + ' file' + (files.length > 1 ? 's' : '') + ' selected'"></span>
        </template>
    </div>

    {{-- Per-file upload queue --}}
    <template x-if="files.length > 0">
        <div class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center gap-4 px-4 py-3">
                    {{-- Thumbnail preview --}}
                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded border border-gray-200 bg-gray-50">
                        <img
                            x-show="file.preview"
                            :src="file.preview"
                            :alt="file.name"
                            class="h-full w-full object-cover"
                        >
                        <div x-show="!file.preview" class="flex h-full w-full items-center justify-center">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    {{-- File info + progress --}}
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900" x-text="file.name"></p>

                        {{-- Progress bar --}}
                        <div x-show="file.status === 'uploading'" class="mt-1">
                            <div class="h-1.5 w-full rounded-full bg-gray-200">
                                <div
                                    class="h-1.5 rounded-full bg-teal-500 transition-all duration-300"
                                    :style="'width: ' + file.progress + '%'"
                                ></div>
                            </div>
                            <p class="mt-0.5 text-xs text-gray-500" x-text="file.progress + '% uploaded'"></p>
                        </div>

                        {{-- Success message --}}
                        <p x-show="file.status === 'done'" class="mt-0.5 text-xs text-green-600">Upload complete</p>

                        {{-- Validation/error messages --}}
                        <template x-if="file.status === 'error' && file.errors.length > 0">
                            <ul class="mt-0.5 space-y-0.5">
                                <template x-for="err in file.errors" :key="err">
                                    <li class="text-xs text-red-600" x-text="err"></li>
                                </template>
                            </ul>
                        </template>
                    </div>

                    {{-- Status icon --}}
                    <div class="shrink-0">
                        <template x-if="file.status === 'pending'">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="file.status === 'uploading'">
                            <svg class="h-5 w-5 animate-spin text-teal-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="file.status === 'done'">
                            <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="file.status === 'error'">
                            <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Upload All button --}}
    <template x-if="hasPendingFiles()">
        <div class="flex justify-end">
            <button
                type="button"
                @click.prevent="uploadAll()"
                x-bind:disabled="uploading"
                class="inline-flex items-center rounded-md border border-transparent bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <template x-if="uploading">
                    <svg class="-ml-1 mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="uploading ? 'Uploading...' : 'Upload'"></span>
            </button>
        </div>
    </template>
</div>

@once
    @push('scripts')
        <script>
            function imageUploadZone(config) {
                return {
                    action: config.action,
                    csrfToken: config.csrfToken,
                    successRedirect: config.successRedirect,
                    successEvent: config.successEvent,
                    dragOver: false,
                    uploading: false,
                    files: [],

                    hasPendingFiles() {
                        return this.files.some(f => f.status === 'pending');
                    },

                    buildPreview(file) {
                        return new Promise(resolve => {
                            if (!file.type.startsWith('image/')) {
                                resolve(null);
                                return;
                            }
                            const reader = new FileReader();
                            reader.onload = e => resolve(e.target.result);
                            reader.onerror = () => resolve(null);
                            reader.readAsDataURL(file);
                        });
                    },

                    async addFiles(fileList) {
                        for (const file of Array.from(fileList)) {
                            const preview = await this.buildPreview(file);
                            this.files.push({
                                file,
                                name: file.name,
                                preview,
                                status: 'pending',
                                progress: 0,
                                errors: [],
                            });
                        }
                    },

                    handleDrop(event) {
                        this.dragOver = false;
                        if (event.dataTransfer?.files?.length) {
                            this.addFiles(event.dataTransfer.files);
                        }
                    },

                    handleFileSelect(event) {
                        if (event.target.files?.length) {
                            this.addFiles(event.target.files);
                            event.target.value = '';
                        }
                    },

                    async uploadFile(fileEntry) {
                        fileEntry.status = 'uploading';
                        fileEntry.progress = 0;
                        fileEntry.errors = [];

                        return new Promise(resolve => {
                            const formData = new FormData();
                            formData.append('file', fileEntry.file);
                            formData.append('_token', this.csrfToken);

                            const xhr = new XMLHttpRequest();

                            xhr.upload.addEventListener('progress', event => {
                                if (event.lengthComputable) {
                                    fileEntry.progress = Math.round((event.loaded / event.total) * 100);
                                }
                            });

                            xhr.addEventListener('load', () => {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    fileEntry.status = 'done';
                                    fileEntry.progress = 100;
                                    resolve(true);
                                } else {
                                    fileEntry.status = 'error';
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.errors?.file) {
                                            fileEntry.errors = Array.isArray(response.errors.file)
                                                ? response.errors.file
                                                : [response.errors.file];
                                        } else if (response.message) {
                                            fileEntry.errors = [response.message];
                                        } else {
                                            fileEntry.errors = ['Upload failed.'];
                                        }
                                    } catch {
                                        fileEntry.errors = ['Upload failed.'];
                                    }
                                    resolve(false);
                                }
                            });

                            xhr.addEventListener('error', () => {
                                fileEntry.status = 'error';
                                fileEntry.errors = ['Network error during upload.'];
                                resolve(false);
                            });

                            xhr.open('POST', this.action);
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                            xhr.send(formData);
                        });
                    },

                    async uploadAll() {
                        this.uploading = true;
                        const pending = this.files.filter(f => f.status === 'pending');
                        let allSucceeded = true;

                        for (const fileEntry of pending) {
                            const success = await this.uploadFile(fileEntry);
                            if (!success) {
                                allSucceeded = false;
                            }
                        }

                        this.uploading = false;

                        if (allSucceeded) {
                            window.dispatchEvent(new CustomEvent(this.successEvent, {
                                detail: { count: pending.length },
                            }));

                            if (this.successRedirect) {
                                window.location.href = this.successRedirect;
                            }
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
