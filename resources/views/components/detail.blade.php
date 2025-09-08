@props(['label', 'value'])

<div>
    <label class="block text-xs text-gray-600 font-semibold mb-1">{{ $label }}</label>
    <div class="px-4 py-2 bg-white rounded-md text-gray-800 border border-gray-300 shadow-sm text-sm">
        {{ $value }}
    </div>
</div>
