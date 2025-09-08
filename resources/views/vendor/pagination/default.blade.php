@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        <ul class="flex justify-center space-x-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="px-4 py-2 text-gray-400 cursor-not-allowed rounded-lg">
                        &laquo; Previous
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                        &laquo; Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li>
                        <span class="px-4 py-2 text-gray-400">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="px-4 py-2 bg-blue-600 text-white rounded-lg cursor-default">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="px-4 py-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                        Next &raquo;
                    </a>
                </li>
            @else
                <li>
                    <span class="px-4 py-2 text-gray-400 cursor-not-allowed rounded-lg">
                        Next &raquo;
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif