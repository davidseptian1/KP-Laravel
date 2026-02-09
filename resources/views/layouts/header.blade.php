<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="e-Sistem Monitoring Transaksi - PT. CHIKA MULYA MULTIMEDIA">
    <meta name="author" content="PT. CHIKA MULYA MULTIMEDIA">

    <title>{{ $title }} | e-SMT</title>

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('mantis/images/favicon.svg') }}" type="image/x-icon">

    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">

    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="{{ asset('mantis/fonts/tabler-icons.min.css') }}">
    <style>
        @font-face {
            font-family: 'tabler-icons';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset("mantis/fonts/tabler/tabler-icons.woff2") }}') format('woff2');
        }

        .ti {
            font-family: 'tabler-icons' !important;
            speak: never;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: inline-block;
        }
    </style> <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="{{ asset('mantis/fonts/feather.css') }}">

    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="{{ asset('mantis/fonts/fontawesome.css') }}">

    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="{{ asset('mantis/fonts/material.css') }}">

    <!-- [Mantis CSS Files] -->
    <link rel="stylesheet" href="{{ asset('mantis/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('mantis/css/style-preset.css') }}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('mantis/css/custom.css') }}">

    <!-- DataTables -->
    <link href="{{ asset('sbadmin2/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('sweetalert2/sweetalert2.min.css') }}">



</head>

<script>
    (function() {
        const state = localStorage.getItem('sidebarState');
        if (state === 'hidden') {
            document.documentElement.classList.add('pc-sidebar-hide');
        }
    })();
</script>