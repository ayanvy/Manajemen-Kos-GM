<button {{ $attributes->merge(['type' => 'submit', 'class' => 'kos-btn kos-btn-danger']) }}>
    {{ $slot }}
</button>
