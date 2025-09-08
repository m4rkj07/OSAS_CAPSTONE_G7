<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150']) }} 
    style="background-color: #155dfc; hover:background-color: #0f44bf; focus:background-color: #0c3aa3;">
{{ $slot }}
</button>





