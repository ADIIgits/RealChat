@props(['disabled' => false])
<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm w-full px-3 py-2 text-sm']) }}>
