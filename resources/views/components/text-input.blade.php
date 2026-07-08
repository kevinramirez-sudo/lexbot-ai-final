@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-xl shadow-sm']) }}>
