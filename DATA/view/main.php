<div class="container mt-4 mb-5">
    <header class="text-center mb-4 pt-3">
        <i class="bi bi-robot" style="font-size: 2.5rem; color: #0d6efd;"></i>
        <h1 class="display-5 fw-bold"><?=_APP_NAME_?></h1>
    </header>
    <?=getLOG()?>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="sites-tab" data-bs-toggle="tab" data-bs-target="#sites" type="button" role="tab">Сайты</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab">API Ключи</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">Логи</button>
        </li>
        <li class="ms-auto">
            <a href="/?logout" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </li>

    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- Таб Управление сайтами -->
        <div class="tab-pane fade show active" id="sites" role="tabpanel">
            <div class="d-flex justify-content-between mb-4 flex-wrap gap-2">
                <!-- Левая часть - кнопки действий -->
                <div class="d-flex flex-wrap gap-2">
                    <!-- Кнопка добавления -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSitesModal" title="Добавить сайты">
                        <i class="fas fa-plus me-2 me-md-0"></i>
                        <span class="d-none d-md-inline">Добавить сайты</span>
                    </button>

                    <!-- Кнопка массового удаления -->
                    <button id="massDeleteBtn" class="btn btn-danger" disabled title="Удалить выбранные">
                        <i class="fas fa-trash me-1 me-md-0"></i>
                        <span class="d-none d-md-inline">Удалить выбранные</span>
                    </button>
                </div>

                <!-- Правая часть - информация -->
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <!-- Счетчики -->
                    <div class="d-flex gap-3">

            <span class="text-nowrap">
                <i class="fas fa-check-circle d-inline d-md-none" title="Выбрано"></i>
                <span class="d-none d-md-inline">Выбрано:</span>
                <strong id="selectedCount">0</strong>
            </span>

            <span class="text-nowrap">
                <i class="fas fa-globe d-inline d-md-none" title="Всего сайтов"></i>
                <span class="d-none d-md-inline">Всего сайтов:</span>
                <strong><?php echo $sites_cnt; ?></strong>
            </span>

                    </div>

                    <!-- <div class="input-group d-none d-md-flex" style="width: 200px;">
                        <input type="text" class="form-control" placeholder="Поиск">
                        <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                    </div> -->

                </div>
            </div>
            <style>
                /* Чекбокс */
                .mass-delete-checkbox {
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                    margin-top: 0;
                }

                /* Выделение выбранного элемента */
                .accordion-item.selected {
                    background-color: rgba(220, 53, 69, 0.05);
                    border-left: 2px solid #dc3545;
                }

                /* Фикс для кнопки аккордиона */
                .accordion-button {
                    padding-left: 0.5rem !important;
                }
            </style>
            <div class="accordion" id="sitesAccordion">
                <?php foreach ($SITES as $site):
                    $sqi = 'N/A';
                    $indexed = 'N/A';
                    $errors = 'N/A';
                    $excluded = 'N/A';

                    if($site['meta_data'] !== null){
                        $metaData = json_decode($site['meta_data'], true);

                        if (is_array($metaData)) {
                            $sqi     = $metaData['sqi'] ?? 'N/A';
                            $indexed = $metaData['indexed'] ?? 'N/A';
                            $excluded = $metaData['excl_pages'] ?? 'N/A';
                            $errors  = $metaData['errors_fatal'] ?? 'N/A';
                        }
                    }

                    // Определяем параметры бейджа на основе статуса
                    $badgeClass = 'bg-secondary';
                    $badgeText  = 'Остановлен';

                    if ($site['status'] == 1) {
                        $badgeClass = 'bg-success';
                        $badgeText  = 'Активно';
                    } elseif ($site['status'] == 3) {
                        $badgeClass = 'bg-warning'; // или bg-warning
                        $badgeText  = 'Верификация';
                    }

                    ?>
                    <div class="accordion-item" data-site-id="<?php echo $site['id']; ?>">
                        <div class="d-flex align-items-center">
                            <!-- Чекбокс с отдельным контейнером -->
                            <div class="flex-shrink-0 ps-2 pe-2">
                                <input class="form-check-input mass-delete-checkbox" type="checkbox"
                                       data-site-id="<?php echo $site['id']; ?>">
                            </div>

                            <!-- Аккордион занимает оставшееся пространство -->
                            <div class="flex-grow-1">
                                <h2 class="accordion-header m-0">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#site-<?php echo $site['id']; ?>">
                                        <span class="me-3"><?php echo htmlspecialchars((string)$site['domain'], ENT_QUOTES, 'UTF-8'); ?></span>

                                        <span class="badge <?= $badgeClass ?> me-2 site-status-badge" data-site-id="<?= $site['id'] ?>"><?= $badgeText ?></span>

                                        <?php echo formatErrorsCount($errors); ?>

                                    </button>
                                </h2>
                            </div>
                        </div>

                        <div id="site-<?php echo $site['id']; ?>" class="accordion-collapse collapse">
                            <div class="accordion-body">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-3">
                                <div class="col">
                                    <div class="stat-card shadow-sm">
                                        <span class="stat-label">ИКС</span>
                                        <span class="stat-value"><?php echo $sqi; ?></span>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="stat-card shadow-sm">
                                        <span class="stat-label">Ссылок в базе</span>
                                        <span class="stat-value fs-6"><?php echo $site['linkz']; ?></span>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="stat-card shadow-sm">
                                        <span class="stat-label">Страниц в индексе</span>
                                        <span class="stat-value"><?php echo $indexed; ?></span>
                                    </div>
                                </div>
                                <!-- Last re-crawl card removed, info in title -->

                                <div class="col">
                                    <div class="stat-card shadow-sm">
                                        <span class="stat-label">Страниц исключено</span>
                                        <span class="stat-value "><?php echo $excluded ?></span>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="stat-card shadow-sm">
                                        <span class="stat-label">API Ключ</span>
                                        <span class="stat-value fs-6"><?php echo shortenApiKey($site['api_key_value'], 5);?></span>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="stat-card shadow-sm">
                                        <span class="stat-label">Протокол</span>
                                        <span class="stat-value"><?php echo strtoupper($site['proto']); ?></span>
                                    </div>
                                </div>
                            </div>

                                <!-- <div class="mb-4">
                                <h5>Последние действия</h5>
                                <ul class="list-unstyled">
                                    <li><span class="status-active">[Активно]</span> Переобход: 2025-06-01</li>
                                    <li><span class="status-error">[Ошибка]</span> Переобход: 2025-06-20</li>
                                </ul>
                            </div> -->

                            <div class="d-flex flex-wrap gap-2">

                                <div class="site-actions-container d-inline-block" data-site-id="<?= $site['id'] ?>">
                                    <?php if ($site['status'] == 1): ?>
                                        <button class="btn btn-sm btn-warning site-action" data-action="stop">
                                            <i class="fas fa-stop me-1"></i>Остановить
                                        </button>
                                    <?php else: ?>
                                        <!-- Для статусов 0, 3 и любых других показываем кнопку Запустить -->
                                        <button class="btn btn-sm btn-success site-action" data-action="start">
                                            <i class="fas fa-play me-1"></i>Запустить
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <button class="btn btn-sm btn-primary site-action" data-action="add-links" data-bs-toggle="modal" data-bs-target="#addLinksModal">
                                    <i class="fas fa-plus me-1"></i>Добавить ссылки
                                </button>
                                <button class="btn btn-sm btn-danger site-action" data-action="delete">
                                    <i class="fas fa-trash me-1"></i>Удалить сайт
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
<?php endforeach; ?>

            </div>



            <!-- Пагинация -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Кнопка "Назад" -->
                    <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=<?= $currentPage - 1 ?>" tabindex="-1">Prev</a>
                    </li>

                    <!-- Основные страницы -->
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    // Первая страница с многоточием если нужно
                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?p=1">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif;
                    endif;

                    // Основной диапазон страниц
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor;

                    // Последняя страница с многоточием если нужно
                    if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?p=<?= $totalPages ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Кнопка "Вперед" -->
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=<?= $currentPage + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>



        <style>
            .accordion-row {
                cursor: pointer;
                transition: background-color 0.2s;
            }
            .accordion-row:hover {
                background-color: rgba(0, 0, 0, 0.03);
            }
            .accordion-toggle {
                width: 36px;
            }
            .accordion-collapse td {
                border-top: none;
            }
            /* Иконка с плавной анимацией */
            .accordion-icon {
                transition: transform 0.2s ease-in-out;
            }
        </style>



        <div class="tab-pane fade" id="api" role="tabpanel">
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addApiKeyModal">
                        <i class="fas fa-plus me-2"></i>Добавить API
                    </button>
                    <button class="btn btn-secondary" id="refreshAllStatuses">
                        <i class="fas fa-sync-alt me-2"></i>Обновить статус API
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>ClientID</th>
                        <th style="width: 120px;">Статус</th>
                        <th style="width: 100px;">Сайтов</th>
                        <th style="width: 110px;">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($APIS as $api_k): ?>
                        <tr data-api-key="<?php echo $api_k['id']; ?>"
                            class="accordion-row"

                            data-bs-target="#details-<?php echo $api_k['id']; ?>"
                            aria-expanded="false"
                            aria-controls="details-<?php echo $api_k['id']; ?>">

                            <!-- Основная строка -->
                            <td><?php echo $api_k['id']; ?></td>
                            <td>
                                <span class="api-key"><?php echo htmlspecialchars(shortenApiKey($api_k['value'], 5), ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td><?php apiKeyStatus($api_k['is_active'], $api_k['value']); ?></td>
                            <td><?php echo $api_k['sites']; ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary api-action" data-action="toggle-status" title="Изменить статус">
                                        <i class="fas fa-arrows-turn-to-dots"></i>
                                    </button>
                                    <button class="btn btn-outline-danger api-action" data-action="delete" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-outline-info accordion-toggle" type="button">
                                        <i class="fas fa-chevron-down accordion-icon"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Строка с деталями -->
                        <tr class="accordion-collapse collapse" id="details-<?php echo $api_k['id']; ?>">
                            <td colspan="5" class="p-4 bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Детали токена</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Полный ClientID:</strong>
                                                <span class="text-muted font-monospace"><?php echo htmlspecialchars((string)$api_k['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <br><button class="btn btn-sm btn-outline-secondary ms-2 copy-token" id="clid-data-<?php echo $api_k['id']; ?>"
                                                        data-token="<?php echo htmlspecialchars($api_k['value']); ?>">
                                                    <i class="fas fa-copy"></i> Копировать
                                                </button>
                                            </li>
                                            <li><strong>Токен:</strong>
                                                <span class="text-muted font-monospace"><?php echo htmlspecialchars(shortenApiKey($api_k['token'], 19), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <br><button class="btn btn-sm btn-outline-secondary ms-2 copy-token"
                                                        data-token="<?php echo htmlspecialchars($api_k['token']); ?>">
                                                    <i class="fas fa-copy"></i> Копировать
                                                </button>
                                            </li>

                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Информация</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Добавлен:</strong> <?php echo htmlspecialchars((string)$api_k['created_at'], ENT_QUOTES, 'UTF-8'); ?></li>
                                            <li><strong>Истекает:</strong>
                                                <?php echo htmlspecialchars((string)($api_k['expired'] ?? 'Неизвестно'), ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (isset($api_k['expired'])): ?>
                                                    <span class="badge bg-<?php echo isKeyExpired($api_k['expired']) ? 'danger' : 'success'; ?> ms-2">
                                            <?php echo isKeyExpired($api_k['expired']) ? 'Просрочен' : 'Активен'; ?>
                                        </span>
                                                <?php endif; ?>
                                            </li>
                                            <li><strong>Разрешения:</strong> Полный доступ</li>
                                            <li><strong>IP ограничения:</strong> Нет</li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="logs" role="tabpanel">

            <div id="logPull">

            <!-- jQuery -->
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <style>
                /* Кастомные стили для консоли логов (Замена Tailwind) */
                .log-container {
                    background-color: #111827;
                    color: #d1d5db;
                    font-family: var(--bs-font-monospace);
                    font-size: 0.875rem;
                }
                .log-table {
                    width: 100%;
                    border-collapse: collapse;
                    text-align: left;
                }
                .log-table th {
                    background-color: #1f2937;
                    color: #9ca3af;
                    padding: 0.5rem 1rem;
                    border-bottom: 1px solid #374151;
                    position: sticky;
                    top: 0;
                    z-index: 10;
                    box-shadow: 0 1px 2px rgba(0,0,0,0.5);
                    font-weight: normal;
                }
                .log-table td {
                    padding: 0.25rem 1rem;
                    border-bottom: 1px solid #1f2937;
                }
                .log-context {
                    font-size: 0.75rem;
                    color: #6b7280;
                }

                /* Цветовая индикация строк логов */
                .log-row-INFO { background-color: transparent; }
                .log-row-WARNING { background-color: #fffbeb; color: #b45309; }
                .log-row-ERROR { background-color: #fef2f2; color: #b91c1c; }

                .log-level-INFO { color: #3b82f6; font-weight: bold; }
                .log-level-WARNING { color: #d97706; font-weight: bold; }
                .log-level-ERROR { color: #dc2626; font-weight: bold; }

                /* CSS анимация индикатора Live (Замена animate-ping) */
                .live-indicator {
                    position: relative;
                    display: flex;
                    width: 8px;
                    height: 8px;
                }
                .live-indicator-ping {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    background-color: #4ade80;
                    opacity: 0.75;
                    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
                }
                .live-indicator-dot {
                    position: relative;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background-color: #22c55e;
                }
                @keyframes ping {
                    75%, 100% { transform: scale(2); opacity: 0; }
                }
            </style>

            <div id="ui-tab" style="margin-top: 0px!important;" class="d-flex flex-column overflow-hidden bg-white rounded shadow-sm border mt-4">

                <!-- Панель управления (переписана на Bootstrap 5) -->
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                    <div class="d-flex gap-2">
                        <select id="log-filter" class="form-select form-select-sm" style="width: auto;">
                            <option value="ALL">All levels</option>
                            <option value="INFO">INFO</option>
                            <option value="WARNING">WARNING</option>
                            <option value="ERROR">ERROR</option>
                        </select>
                        <select id="sort-direction" class="form-select form-select-sm" style="width: auto;">
                            <option value="DESC" selected>DESC</option>
                            <option value="ASC">ASC</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 small">
                        <div class="live-indicator">
                            <span class="live-indicator-ping"></span>
                            <span class="live-indicator-dot"></span>
                        </div>
                        <span class="text-secondary me-3">Live (Polling: 3s)</span>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="auto-scroll" checked style="cursor: pointer;">
                            <label class="form-check-label" for="auto-scroll" style="cursor: pointer;">Autoscroll</label>
                        </div>
                    </div>
                </div>

                <!-- Контейнер с таблицей логов -->
                <div class="overflow-auto log-container" id="log-container" style="height: 600px;">
                    <table class="log-table">
                        <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 170px;">Date</th>
                            <th style="width: 90px;">Level</th>
                            <th style="width: 50%;">Message</th>
                            <th>Context</th>
                        </tr>
                        </thead>
                        <tbody id="log-body">
                        <!-- Логи будут добавляться сюда -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

<script>
    $(document).ready(function() {
        let lastLogId = 0;
        let pollTimeout; // Заменили pollInterval на pollTimeout

        function fetchLogs() {
            $.ajax({
                url: '/?logs=get',
                method: 'GET',
                data: { last_id: lastLogId },
                dataType: 'json',
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        renderLogs(response.data);
                        // Обновляем ID только после успешного рендера
                        lastLogId = response.data[response.data.length - 1].id;
                    }
                },
                error: function() {
                    console.error("Network error.");
                },
                complete: function() {
                    // ВАЖНО: Планируем следующий запрос только после завершения (успешного или с ошибкой) текущего
                    pollTimeout = setTimeout(fetchLogs, 3000);
                }
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function renderLogs(logs) {
            const tbody = $('#log-body');
            const filter = $('#log-filter').val();
            const sortDir = $('#sort-direction').val();

            logs.forEach(log => {
                // Защита от потенциальных ошибок JSON.parse при кривом контексте
                let contextStr = "";
                try {
                    contextStr = log.context !== "{}" && log.context ? JSON.stringify(JSON.parse(log.context)) : "";
                } catch (e) {
                    contextStr = log.context;
                }

                const isHidden = (filter !== 'ALL' && log.level !== filter) ? 'd-none' : '';
                const dateObj = new Date(log.created_at * 1000);
                const timeStr = dateObj.toLocaleString('ru-RU');

                const safeLevel = escapeHtml(log.level);
                const safeMessage = escapeHtml(log.message);
                const safeContext = escapeHtml(contextStr);

                const tr = `
            <tr class="log-row-${safeLevel} ${isHidden}" data-level="${safeLevel}">
                <td>${log.id}</td>
                <td>${escapeHtml(timeStr)}</td>
                <td class="log-level-${safeLevel}">${safeLevel}</td>
                <td>${safeMessage}</td>
                <td class="log-context">${safeContext}</td>
            </tr>
            `;

                if (sortDir === 'DESC') {
                    tbody.prepend(tr);
                } else {
                    tbody.append(tr);
                }
            });

            if ($('#auto-scroll').is(':checked')) {
                const container = $('#log-container');
                if (sortDir === 'ASC') {
                    container.scrollTop(container[0].scrollHeight);
                } else {
                    container.scrollTop(0);
                }
            }
        }

        $(document).on('change', '#sort-direction', function() {
            const tbody = $('#log-body');
            const rows = tbody.find('tr').toArray();
            rows.reverse();
            tbody.empty().append(rows);

            if ($('#auto-scroll').is(':checked')) {
                const container = $('#log-container');
                if ($(this).val() === 'ASC') {
                    container.scrollTop(container[0].scrollHeight);
                } else {
                    container.scrollTop(0);
                }
            }
        });

        $(document).on('change', '#log-filter', function() {
            const filter = $(this).val();
            if (filter === 'ALL') {
                $('#log-body tr').removeClass('d-none');
            } else {
                $('#log-body tr').addClass('d-none');
                $(`#log-body tr[data-level="${filter}"]`).removeClass('d-none');
            }
        });

        $(document).on('click', '#clear-logs', function() {
            $('#log-body').empty();
            // lastLogId не сбрасываем, иначе сервер снова отдаст старые логи
        });

        // Запускаем первый опрос
        fetchLogs();
    });

</script>

    </div>
</div>





<!-- Модальное окно добавления сайтов -->
<div class="modal fade" id="addSitesModal" tabindex="-1" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить сайты</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="addMethodTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="file-tab" data-bs-toggle="pill" data-bs-target="#file-method" type="button">Загрузить файл</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="text-tab" data-bs-toggle="pill" data-bs-target="#text-method" type="button">Вставить ссылки</button>
                    </li>
                </ul>

                <select id="selApiKeys" class="form-select" >
                    <option disabled selected>Выбери API ключ</option>
                    <?php foreach ($APIS as $a_k): ?>:?>
                    <?php if($a_k['is_active'] == '1'): ?>
                    <option value="<?php echo $a_k['id']?>"><?php echo shortenApiKey($a_k['value'], 5); ?></option>
                    <?php endif; ?>
                    <?php endforeach;?>
                </select>

                <label for="siteProto" class="form-label">Основной протокол сайта:</label>

                <select id="siteProto" class="form-select" >
                    <option value="https">HTTPS</option>
                    <option value="http">HTTP</option>
                </select>

                <div class="tab-content" id="addMethodTabContent">
                    <div class="tab-pane fade show active" id="file-method" role="tabpanel">
                        <div class="mb-3">
                            <label for="sitesFile" class="form-label">TXT файл с ссылками (каждая ссылка на новой строке)</label>
                            <input class="form-control" type="file" id="sitesFile" accept=".txt">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="text-method" role="tabpanel">
                        <div class="mb-3">
                            <label for="sitesText" class="form-label">Список ссылок (каждая ссылка на новой строке)</label>
                            <textarea class="form-control" id="sitesText" rows="5"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="submitSites">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления ссылок -->
<div class="modal fade" id="addLinksModal" tabindex="-1" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить ссылки для <span id="currentSiteName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="addLinksMethodTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="links-file-tab" data-bs-toggle="pill" data-bs-target="#links-file-method" type="button">Загрузить файл</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="links-text-tab" data-bs-toggle="pill" data-bs-target="#links-text-method" type="button">Вставить ссылки</button>
                    </li>
                </ul>

                <div class="tab-content" id="addLinksMethodTabContent">
                    <div class="tab-pane fade show active" id="links-file-method" role="tabpanel">
                        <div class="mb-3">
                            <label for="linksFile" class="form-label">TXT файл с ссылками (каждая ссылка на новой строке)</label>
                            <input class="form-control" type="file" id="linksFile" accept=".txt">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="links-text-method" role="tabpanel">
                        <div class="mb-3">
                            <label for="linksText" class="form-label">Список ссылок (каждая ссылка на новой строке)</label>
                            <textarea class="form-control" id="linksText" rows="5"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="submitLinks">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления API ключа -->
<div class="modal fade" id="addApiKeyModal" tabindex="-1" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавление API</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="apiKeyValue" class="form-label">ClientID <a href="<?php echo _OAUTH_INFO_URL_; ?>" target="_blank">[?]</a></label>
                    <input type="text" class="form-control" id="apiKeyValue" placeholder="Введите ClientID">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="submitApiKey">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Подтверждение удаления -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Подтверждение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                Вы уверены, что хотите выполнить это действие?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmAction">Подтвердить</button>
            </div>
        </div>
    </div>
</div>








