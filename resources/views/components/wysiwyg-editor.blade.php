@props([
    'name' => 'content',
    'value' => '',
    'label' => 'Content',
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <label for="{{ $id }}" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        {{ $label }}
    </label>

    <div
        data-wysiwyg-editor
        data-wysiwyg-mode="visual"
        @class([
            'wysiwyg-editor rounded-xl border border-slate-200 bg-white shadow-xs transition-all focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-transparent',
            'border-red-400 focus-within:ring-red-500' => $errors->has($name),
        ])
    >
        <div class="wysiwyg-editor-modes">
            <button
                type="button"
                data-wysiwyg-mode="visual"
                class="wysiwyg-mode-active"
                aria-pressed="true"
            >
                Visual
            </button>
            <button
                type="button"
                data-wysiwyg-mode="html"
                aria-pressed="false"
            >
                HTML
            </button>
        </div>

        <div data-wysiwyg-visual>
            <div data-wysiwyg-target class="min-h-[18rem] text-sm text-slate-800"></div>
        </div>

        <textarea
            data-wysiwyg-html
            class="wysiwyg-html-source"
            rows="12"
            spellcheck="false"
            aria-label="HTML source"
        >{{ $value }}</textarea>

        <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            data-wysiwyg-input
            class="hidden"
            @required($required)
        >{{ $value }}</textarea>
    </div>

    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
