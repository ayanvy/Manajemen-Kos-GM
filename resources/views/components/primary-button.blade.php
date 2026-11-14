<button {{ $attributes->merge(['type' => 'submit', 'class' => 'kos-btn kos-btn-primary']) }}>
    {{ $slot }}
</button>
