@props([
    'paginator',
    'label' => 'data',
])

@php
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();
    $total = $paginator->total();
    $perPage = $paginator->perPage();
    $selectId = 'per-page-' . uniqid();
@endphp

@if ($total > 0)
    <div {{ $attributes->merge(['class' => 'table-footer']) }}>
        <div class="table-footer__info">
            Menampilkan <strong>{{ number_format($from, 0, ',', '.') }}</strong>&ndash;<strong>{{ number_format($to, 0, ',', '.') }}</strong>
            dari <strong>{{ number_format($total, 0, ',', '.') }}</strong> {{ $label }}
        </div>

        <div class="table-footer__controls">
            <form method="GET" action="{{ url()->current() }}" class="table-footer__per-page">
                @foreach (request()->except(['page', 'per_page']) as $key => $value)
                    @if (is_scalar($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <label class="table-footer__label" for="{{ $selectId }}">Tampilkan</label>
                <select class="form-select table-footer__select" name="per_page" id="{{ $selectId }}"
                    onchange="this.form.submit()">
                    @foreach (config('pagination.options', [25, 50, 100, 250]) as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <span class="table-footer__label">baris</span>
            </form>

            {{ $paginator->withQueryString()->links('pagination.numbers') }}
        </div>
    </div>
@endif
