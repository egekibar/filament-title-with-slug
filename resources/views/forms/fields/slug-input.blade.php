@php
    $fieldWrapperView = $getFieldWrapperView();
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    class="filament-seo-slug-input-wrapper"
>
    <div
        x-data="{
            context: @js($getContext()),
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            statePersisted: @js($getRecordSlug()),
            stateInitial: '',
            editing: false,
            modified: false,
            init() {
                this.syncInitialState()
                this.detectModification()

                this.$watch('state', () => {
                    if (! this.editing) {
                        this.syncInitialState()
                    }

                    this.detectModification()
                })
            },
            syncInitialState() {
                this.stateInitial = this.state ?? ''
            },
            initModification() {
                this.syncInitialState()
                this.editing = true

                setTimeout(() => this.$refs.slugInput?.focus(), 75)
            },
            submitModification() {
                this.state = (this.stateInitial ?? '').trim()
                this.editing = false
                this.detectModification()
            },
            cancelModification() {
                this.syncInitialState()
                this.editing = false
                this.detectModification()
            },
            resetModification() {
                this.state = this.statePersisted ?? ''
                this.syncInitialState()
                this.editing = false
                this.detectModification()
            },
            detectModification() {
                this.modified = this.context === 'edit' && ((this.state ?? '') !== (this.statePersisted ?? ''))
            },
        }"
        x-on:submit.document="modified = false"
    >
        <div
            {{
                \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                    ->merge($getExtraAttributes(), escape: false)
                    ->class(['flex items-center justify-between gap-3 text-sm'])
            }}
        >
            @if ($getReadonly())
                <span class="flex items-center">
                    <span class="mr-1">{{ $getLabelPrefix() }}</span>
                    <span class="text-gray-400">{{ $getFullBaseUrl() }}</span>
                    <span
                        x-text="state || ''"
                        class="font-semibold text-gray-500"
                    ></span>
                </span>

                @if ($getSlugInputUrlVisitLinkVisible())
                    <a
                        href="{{ $getRecordUrl() }}"
                        target="_blank"
                        class="fi-link fi-size-sm inline-flex items-center justify-center gap-1 underline"
                    >
                        <span>{{ $getVisitLinkLabel() }}</span>

                        <x-filament::icon
                            alias="filament-title-with-slug::visit-link"
                            icon="heroicon-m-arrow-top-right-on-square"
                            class="h-4 w-4"
                        />
                    </a>
                @endif
            @else
                <span class="@if (! $getState()) flex items-center gap-1 @endif">
                    <span>{{ $getLabelPrefix() }}</span>

                    <span
                        x-text="! editing ? '{{ $getFullBaseUrl() }}' : '{{ $getBasePath() }}'"
                        class="text-gray-400"
                    ></span>

                    <a
                        href="#"
                        role="button"
                        title="{{ trans('filament-title-with-slug::package.permalink_action_edit') }}"
                        x-on:click.prevent="initModification()"
                        x-show="! editing"
                        class="cursor-pointer inline-flex items-center justify-center font-semibold text-gray-500 hover:text-primary-500 hover:underline"
                        x-bind:class="context !== 'create' && modified ? 'rounded-md bg-gray-100 px-1 text-gray-700' : ''"
                    >
                        <span
                            x-text="state || ''"
                            class="mr-1"
                        ></span>

                        <x-filament::icon
                            alias="filament-title-with-slug::edit-slug"
                            icon="heroicon-m-pencil-square"
                            class="h-4 w-4 text-primary-600"
                        />

                        <span class="sr-only">{{ trans('filament-title-with-slug::package.permalink_action_edit') }}</span>
                    </a>

                    @if ($getSlugLabelPostfix())
                        <span
                            x-show="! editing"
                            class="ml-0.5 text-gray-400"
                        >{{ $getSlugLabelPostfix() }}</span>
                    @endif

                    <span x-show="! editing && context !== 'create' && modified"> [{{ trans('filament-title-with-slug::package.permalink_status_changed') }}]</span>
                </span>

                <div
                    class="mx-2 flex-1"
                    x-show="editing"
                    style="display: none;"
                >
                    <input
                        type="text"
                        x-ref="slugInput"
                        class="p-0 outline-0"
                        x-model="stateInitial"
                        x-bind:disabled="! editing"
                        x-on:keydown.enter.prevent="submitModification()"
                        x-on:keydown.escape.prevent="cancelModification()"
                        {!! ($autocomplete = $getAutocomplete()) ? "autocomplete=\"{$autocomplete}\"" : null !!}
                        id="{{ $getId() }}"
                        {!! ($placeholder = $getPlaceholder()) ? "placeholder=\"{$placeholder}\"" : null !!}
                        {!! $isRequired() ? 'required' : null !!}
                        {{ $getExtraInputAttributeBag()->class(['fi-input block w-full']) }}
                    />
                </div>

                <div
                    x-show="editing"
                    class="flex space-x-2"
                    style="display: none;"
                >
                    <a
                        href="#"
                        role="button"
                        x-on:click.prevent="submitModification()"
                        class="fi-color fi-color-primary fi-bg-color-600 hover:fi-bg-color-500 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-0 hover:fi-text-color-0 dark:fi-text-color-0 dark:hover:fi-text-color-0 fi-btn fi-size-md fi-ac-btn-action"
                    >
                        {{ trans('filament-title-with-slug::package.permalink_action_ok') }}
                    </a>

                    <x-filament::link
                        x-show="context === 'edit' && modified"
                        x-on:click.prevent="resetModification()"
                        class="cursor-pointer ml-4"
                        icon="heroicon-m-arrow-path"
                        color="gray"
                        size="sm"
                        title="{{ trans('filament-title-with-slug::package.permalink_action_reset') }}"
                    >
                        <span class="sr-only">{{ trans('filament-title-with-slug::package.permalink_action_reset') }}</span>
                    </x-filament::link>

                    <x-filament::link
                        x-on:click.prevent="cancelModification()"
                        class="cursor-pointer"
                        icon="heroicon-m-x-mark"
                        color="gray"
                        size="sm"
                        title="{{ trans('filament-title-with-slug::package.permalink_action_cancel') }}"
                    >
                        <span class="sr-only">{{ trans('filament-title-with-slug::package.permalink_action_cancel') }}</span>
                    </x-filament::link>
                </div>

                <span
                    x-show="context === 'edit'"
                    class="flex items-center space-x-2"
                >
                    @if ($getSlugInputUrlVisitLinkVisible())
                        <template x-if="! editing">
                            <a
                                href="{{ $getRecordUrl() }}"
                                target="_blank"
                                class="fi-link fi-size-sm inline-flex items-center justify-center gap-1 cursor-pointer"
                            >
                                <span>{{ $getVisitLinkLabel() }}</span>

                                <x-filament::icon
                                    alias="filament-title-with-slug::record-link"
                                    icon="heroicon-m-arrow-top-right-on-square"
                                    class="h-4 w-4"
                                />
                            </a>
                        </template>
                    @endif
                </span>
            @endif
        </div>
    </div>
</x-dynamic-component>
