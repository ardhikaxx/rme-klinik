@php
    /**
     * Number-only pagination (no previous/next or first/last buttons).
     * Shows a compact window of page numbers with a visual ellipsis.
     */
    $currentPage = $paginator->currentPage();
    $lastPage = max(1, (int) $paginator->lastPage());

    if ($currentPage < 1) {
        $currentPage = 1;
    }
    if ($currentPage > $lastPage) {
        $currentPage = $lastPage;
    }

    $window = 2; // Pages shown on each side of the current page.

    if ($lastPage <= 7) {
        $pages = range(1, $lastPage);
    } else {
        $start = max(2, $currentPage - $window);
        $end = min($lastPage - 1, $currentPage + $window);

        $pages = [1];

        if ($start > 2) {
            $pages[] = '...';
        }

        for ($page = $start; $page <= $end; $page++) {
            $pages[] = $page;
        }

        if ($end < $lastPage - 1) {
            $pages[] = '...';
        }

        $pages[] = $lastPage;
    }
@endphp

@if ($lastPage > 1)
    <nav class="rme-pagination" aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0">
            @foreach ($pages as $page)
                @if ($page === '...')
                    <li class="page-item disabled" aria-hidden="true">
                        <span class="page-link">&hellip;</span>
                    </li>
                @else
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url(max(1, $page)) }}"@if ($page === $currentPage) aria-current="page"@endif>{{ $page }}</a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif
