@if ($paginator->hasPages())
    <nav class="flex justify-center">
        <ul class="flex space-x-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="px-3 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" 
                       class="px-3 py-2 text-sm font-medium bg-white text-sky-600 border border-sky-300 rounded-md hover:bg-sky-50 hover:border-sky-400 transition-colors duration-200">&lsaquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li aria-disabled="true">
                        <span class="px-3 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span class="px-3 py-2 text-sm font-medium bg-sky-600 text-white border border-sky-600 rounded-md">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium bg-white text-sky-600 border border-sky-300 rounded-md hover:bg-sky-50 hover:border-sky-400 transition-colors duration-200">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"
                       class="px-3 py-2 text-sm font-medium bg-white text-sky-600 border border-sky-300 rounded-md hover:bg-sky-50 hover:border-sky-400 transition-colors duration-200">&rsaquo;</a>
                </li>
            @else
                <li aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="px-3 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
