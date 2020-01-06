<script>
    window.App = {
        csrfToken: '{{ csrf_token() }}',
        stripePublicKey: '{{ config('cashier.key') }}',
    }
</script>
