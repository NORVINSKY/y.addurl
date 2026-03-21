<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=_APP_NAME_?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa; /* Светло-серый фон */
            font-family: 'Inter', sans-serif; /* Современный шрифт */
        }
        .nav-tabs .nav-link {
            color: #495057; /* Темно-серый цвет для неактивных табов */
            border-radius: 0.3rem 0.3rem 0 0; /* Скругление верхних углов */
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd; /* Синий цвет для активного таба */
            background-color: #fff; /* Белый фон для активного таба */
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .accordion-button {
            font-weight: 500; /* Средняя жирность для заголовков аккордеона */
        }
        .accordion-button:not(.collapsed) {
            color: #0c63e4; /* Синий цвет для открытого аккордеона */
            background-color: #e7f1ff; /* Светло-синий фон для открытого аккордеона */
        }
        .accordion-button .site-title-info {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .accordion-button:not(.collapsed) .site-title-info {
            color: #0a58ca;
        }

        .accordion-item {
            border-radius: 0.3rem; /* Скругление для элементов аккордеона */
            margin-bottom: 0.5rem; /* Отступ между элементами аккордеона */
            overflow: hidden; /* Чтобы скругление работало корректно с внутренними элементами */
        }
        .accordion-body {
            background-color: #ffffff; /* Белый фон для тела аккордеона для контраста */
        }

        .stat-card {
            background-color: #f8f9fa; /* Слегка отличный фон для карточек */
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
            padding: 0.75rem;
            text-align: center;
            height: 100%; /* Для одинаковой высоты карточек в ряду */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            display: block;
        }
        .stat-card .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
        }

        .badge.bg-success-light {
            background-color: rgba(25, 135, 84, 0.15);
            color: #0f5132;
            border: 1px solid rgba(25, 135, 84, 0.3);
        }
        .badge.bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1);
            color: #842029;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        .badge.bg-info-light {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0a58ca;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }
        .badge.bg-warning-light {
            background-color: rgba(255, 193, 7, 0.15);
            color: #664d03;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }


        .action-buttons-group {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }
        .action-buttons-group .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .controls-bar {
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 0.3rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .table th {
            font-weight: 600;
        }
        .page-link {
            border-radius: 0.25rem;
            margin: 0 0.15rem;
            display: flex; /* Added for vertical alignment of icons */
            align-items: center; /* Added for vertical alignment of icons */
            justify-content: center; /* Added for horizontal alignment of icons */
        }
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .pagination .page-link i { /* Ensure icons are vertically centered */
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .action-buttons-group .btn {
                width: calc(50% - 0.25rem); /* Две кнопки в ряд на мобильных */
            }
            .action-buttons-group .btn:nth-child(odd) {
                margin-right: 0.5rem;
            }
            .action-buttons-group .btn:nth-child(even) {
                margin-right: 0;
            }
            .controls-bar {
                flex-direction: column;
            }
            .controls-bar .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .controls-bar .btn:last-child {
                margin-bottom: 0;
            }
            .accordion-button .site-name {
                max-width: 50%; /* Limit site name width on small screens */
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
        .form-check-label {
            cursor: pointer;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
        }
        .status-active {
            color: #198754;
        }
        .status-pending {
            color: #0d6efd;
        }
        .status-error {
            color: #dc3545;
        }
        .api-key {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .tab-content {
            padding: 20px 0;
        }
        .site-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .stat-card {
            border-left: 3px solid #0d6efd;
            padding-left: 10px;
        }

        @media (max-width: 767.98px) {
            .mob-hide-is {display:none!important;}
        }


    </style>
<?php if (isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
</head>
<body>