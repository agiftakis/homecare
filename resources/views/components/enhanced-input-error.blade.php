@props(['messages' => null, 'field' => null])

@php
    $messages = $messages instanceof \Illuminate\Support\MessageBag ? $messages->get($field ?? '') : (array) $messages;
@endphp

@if ($messages)
    <div {{ $attributes->merge(['class' => 'mt-2']) }}>
        @foreach ((array) $messages as $message)
            <div class="flex items-start space-x-2 p-3 text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                <div class="flex-shrink-0">
                    <svg class="h-4 w-4 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="text-red-800 dark:text-red-200">
                    {{ $message }}
                </div>
            </div>
        @endforeach
    </div>
@endif