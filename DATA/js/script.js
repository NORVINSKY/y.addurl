$(document).ready(function() {

    $(document).on('click', '.accordion-row', function(e) {
        // Проверяем, был ли клик на интерактивном элементе, который НЕ должен открывать аккордеон
        if ($(e.target).closest('a, .api-action, .copy-token').length > 0) {
            // Если да, ничего не делаем и выходим
            return;
        }

        // Если клик был на "безопасной" области (включая .accordion-toggle), вручную управляем аккордеоном
        const collapseTargetSelector = $(this).attr('data-bs-target');
        if (collapseTargetSelector) {
            const collapseElement = $(collapseTargetSelector);
            if (collapseElement.length > 0) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseElement[0]);
                bsCollapse.toggle();
            }
        }
    });

    // Обработчик клика по чекбоксу
    $(document).on('click', '.mass-delete-checkbox', function(e) {
        e.stopPropagation(); // Предотвращаем раскрытие аккордеона
        $(this).closest('.accordion-item').toggleClass('selected', this.checked);
        updateSelectionCount();
    });

    // Обновление счетчика выбранных
    function updateSelectionCount() {
        const count = $('.mass-delete-checkbox:checked').length;

        $('#selectedCount').text(count);
        $('#massDeleteBtn').prop('disabled', count === 0);
    }


// Обработчик массового удаления
    $('#massDeleteBtn').click(function() {
        const selectedSites = [];
        $('.mass-delete-checkbox:checked').each(function() {
            selectedSites.push($(this).data('site-id'));
        });

        if (selectedSites.length === 0) return;

        if (!confirm(`Вы уверены, что хотите удалить ${selectedSites.length} сайтов?`)) {
            return;
        }

        const $btn = $(this);
        const currentPage = new URLSearchParams(window.location.search).get('p') || 1;

        // Показываем индикатор загрузки
        $btn.prop('disabled', true);
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Удаление...');

        // Отправка запроса
        $.ajax({
            url: '/api/sites/mass-delete',
            type: 'POST',
            data: {
                site_ids: selectedSites,
                current_page: currentPage,
                csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('success', `Удалено ${selectedSites.length} сайтов`);

                    if (response.redirect) {
                        // Редирект на предыдущую страницу если текущая пуста
                        window.location.href = response.redirect;
                    } else {
                        // Просто перезагружаем текущую страницу
                        window.location.reload();
                    }
                } else {
                    showAlert('danger', response.message || 'Ошибка удаления');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Удалить выбранные');
                }
            },
            error: function() {
                showAlert('danger', 'Ошибка сервера');
                $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Удалить выбранные');
            }
        });
    });


    // Конфигурация API endpoints
    const API_ENDPOINTS = {
        sites: {
            add: '/api/sites/add',
            start: '/api/sites/start',
            stop: '/api/sites/stop',
            delete: '/api/sites/delete',
            addLinks: '/api/links/add'
        },
        keys: {
            add: '/api/keys/add',
            toggleStatus: '/api/keys/toggle-status',
            delete: '/api/keys/delete',
            refreshAll: '/api/keys/refresh-all'
        }
    };

    // Хранилище текущего контекста действий
    let currentActionContext = {
        type: null,
        action: null,
        targetId: null,
        targetName: null
    };

    // Вспомогательные функции ==============================================

    /**
     * Показывает всплывающее уведомление
     * @param {string} type - Тип alert (success, danger, warning)
     * @param {string} message - Текст сообщения
     * @param {number} duration - Время показа в ms (по умолчанию 5 сек)
     */
    const showAlert = (type, message, duration = 5000) => {
        const alertId = `alert-${Date.now()}`;
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 1100;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        $('body').append(alertHtml);

        setTimeout(() => $(`#${alertId}`).alert('close'), duration);
    };

    /**
     * Управление состоянием кнопки (лоадер)
     * @param {jQuery|string} button - Кнопка или селектор
     * @param {boolean} isLoading - Состояние загрузки
     * @param {string} loadingText - Текст при загрузке
     */
    const toggleButtonLoading = (button, isLoading, loadingText = 'Загрузка...') => {
        const $button = $(button);
        $button.prop('disabled', isLoading);

        if (isLoading) {
            $button.data('original-text', $button.html());
            $button.html(`<span class="spinner-border spinner-border-sm" role="status"></span> ${loadingText}`);
        } else {
            $button.html($button.data('original-text'));
        }
    };

    /**
     * Универсальный обработчик API запросов
     * @param {object} options - Параметры запроса
     */
    const handleApiRequest = async (options) => {
        const {
            url,
            method = 'POST',
            data = {},
            buttonSelector,
            successMessage,
            errorMessage,
            onSuccess
        } = options;

        try {
            if (buttonSelector) toggleButtonLoading(buttonSelector, true);

            // Добавляем CSRF-токен к запросу
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken && data instanceof FormData) {
                data.append('csrf_token', csrfToken);
            }

            // Преобразуем FormData в объект для логирования
            if (data instanceof FormData) {
                const plainData = data instanceof FormData ? Object.fromEntries(data) : data;
                data.forEach((value, key) => plainData[key] = value);
                console.log('Request data:', plainData);
            }

            const response = await $.ajax({
                url: url,
                type: method,
                data: data,
                processData: false, // Важно для FormData
                contentType: false, // Важно для FormData
                dataType: 'json'
            });

            if (response.success) {
                if (successMessage) showAlert('success', successMessage);
                if (onSuccess) onSuccess(response);
                return response;
            } else {
                const errorMsg = errorMessage || response.message || 'Операция не выполнена';
                showAlert('danger', errorMsg);
                return null;
            }
        } catch (error) {
            console.error('API Error:', error);
            const errorMsg = error.responseJSON?.message || error.statusText || 'Неизвестная ошибка';
            showAlert('danger', errorMessage || errorMsg);
            return null;
        } finally {
            if (buttonSelector) toggleButtonLoading(buttonSelector, false);
        }
    };

    // Обработчики форм =====================================================

    /**
     * Инициализация обработчика формы
     * @param {string} formId - ID формы
     * @param {object} options - Параметры обработки
     */
    const setupFormHandler = (formId, options) => {
        const {
            submitButton,
            validate,
            prepareData,
            successMessage,
            errorMessage,
            onSuccess,
            resetForm = true,
            modal = null
        } = options;

        $(submitButton).click(async function(e) {
            e.preventDefault();

            const $form = $(`#${formId}`);
            if (validate && !validate($form)) return;

            const formData = prepareData ? prepareData($form) : new FormData($form[0]);

            const response = await handleApiRequest({
                url: options.url,
                data: formData,
                buttonSelector: submitButton,
                successMessage,
                errorMessage,
                onSuccess
            });

            if (response && response.success) {
                if (resetForm) $form.trigger('reset');
                if (modal) $(modal).modal('hide');

                // Обновляем страницу после успешного добавления
                if (formId === 'addSitesForm' || formId === 'addLinksForm') {
                    setTimeout(() => location.reload(), 1000);
                }
            }
        });
    };


    function isValidDomainOrUrl(line) {
        line = line.trim();
        if (!line) return true; // пустые строки игнорируем

        if (!/^https?:\/\//i.test(line)) {
            return false;
        }

        let hostname;
        try {
            hostname = new URL(line).hostname;
        } catch {
            return false; // URL не парсится
        }

        // TLD (зона) теперь проверяется на длину от 2 символов и защищена от дефиса на конце (например, xn--p1ai пройдет)
        const regex = /^([\p{L}\p{N}]([\p{L}\p{N}\-]{0,61}[\p{L}\p{N}])?\.)+[\p{L}\p{N}][\p{L}\p{N}\-]{0,61}[\p{L}\p{N}]$/iu;

        return regex.test(hostname);
    }

// Инициализация обработчиков форм
setupFormHandler('addSitesForm', {
    submitButton: '#submitSites',
    modal: '#addSitesModal',
    validate: ($form) => {
        const method = $('#addMethodTab .nav-link.active').attr('id');

        if (method === 'file-tab' && !$('#sitesFile')[0].files[0]) {
            showAlert('warning', 'Пожалуйста, выберите файл');
            return false;
        }
        if (method === 'text-tab' && !$('#sitesText').val().trim()) {
            showAlert('warning', 'Пожалуйста, введите ссылки');
            return false;
        }

        // Проверяем чтоб был выбран ключ
        const selectedValue = $('#selApiKeys').val();
        if (!selectedValue || selectedValue === '') {
            showAlert('warning', 'Пожалуйста, выберите ключ');
            return false;
        }

        // Валидация доменов/ссылок (только для текстового ввода)
        // Для файла — валидация на бэкенде (FileReader асинхронный)
        if (method === 'text-tab') {
            const lines = $('#sitesText').val().split(/[\n,;]+/);
            const invalidLines = lines
                .map(l => l.trim())
                .filter(l => l && !isValidDomainOrUrl(l));

            if (invalidLines.length > 0) {
                const preview = invalidLines
                    .slice(0, 5)
                    .map(l => `<code>${l}</code>`)
                    .join('<br>');
                const more = invalidLines.length > 5
                    ? `<br>...и ещё ${invalidLines.length - 5}`
                    : '';
                showAlert('warning', `Невалидные строки (${invalidLines.length}):<br>${preview}${more}`);
                return false;
            }
        }

        return true;
    },
    prepareData: () => {
        const formData = new FormData();
        const method = $('#addMethodTab .nav-link.active').attr('id');

        if (method === 'file-tab') {
            formData.append('sites_file', $('#sitesFile')[0].files[0]);
        } else {
            formData.append('sites_text', $('#sitesText').val());
        }

        formData.append('api_id', $('#selApiKeys').val());
        formData.append('proto', $('#siteProto').val());

        return formData;
    },
    url: API_ENDPOINTS.sites.add,
    successMessage: 'Сайты успешно добавлены'
});

    setupFormHandler('addLinksForm', {
        submitButton: '#submitLinks',
        modal: '#addLinksModal',
        validate: ($form) => {
            const method = $('#addLinksMethodTab .nav-link.active').attr('id');
            if (method === 'links-file-tab' && !$('#linksFile')[0].files[0]) {
                showAlert('warning', 'Пожалуйста, выберите файл');
                return false;
            }
            if (method === 'links-text-tab' && !$('#linksText').val().trim()) {
                showAlert('warning', 'Пожалуйста, введите ссылки');
                return false;
            }
            return true;
        },
        prepareData: () => {
            const formData = new FormData();
            const method = $('#addLinksMethodTab .nav-link.active').attr('id');

            formData.append('site_id', currentActionContext.targetId);

            if (method === 'links-file-tab') {
                formData.append('links_file', $('#linksFile')[0].files[0]);
            } else {
                formData.append('links_text', $('#linksText').val());
            }

            return formData;
        },
        url: API_ENDPOINTS.sites.addLinks,
        successMessage: 'Ссылки успешно добавлены'
    });

    setupFormHandler('addApiKeyForm', {
        submitButton: '#submitApiKey',
        modal: '#addApiKeyModal',
        validate: ($form) => {
            const keyValue = $('#apiKeyValue').val().trim();
            if (!keyValue) {
                showAlert('warning', 'Пожалуйста, введите API ключ');
                return false;
            }
            return true;
        },
        prepareData: () => {
            const formData = new FormData();
            formData.append('add_api_key', $('#apiKeyValue').val());
            return formData;
        },
        url: API_ENDPOINTS.keys.add,
        successMessage: 'API ключ успешно добавлен',
        onSuccess: (response) => {
            // Ключ добавляем в таблицу ТОЛЬКО если сервер вернул данные
            if (response.success && response.key_id && response.key_value) {
                addKeyToTable({
                    id: response.key_id,
                    value: response.key_value,
                    is_active: 1,
                    sites_count: 'N/A'
                });
                $('#apiKeyValue').val(''); // Очищаем поле
                $('#addApiKeyModal').modal('hide'); // Закрываем модалку
            } else {
                showAlert('danger', response.message || 'Ключ не был добавлен');
            }
        }
    });

// Функция для добавления ключа в таблицу
    function addKeyToTable(key) {
        const shortKey = key.value.substring(0, 5) + '...' + key.value.slice(-5);
        const statusClass = key.is_active ? 'bg-warning' : 'bg-secondary';
        const statusText = key.is_active ? `<a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=${key.value}&state=${key.value}">>>AUTH<<</a>`: 'Error';

        const newRow = `
        <tr data-api-key="${key.id}">
            <td>${$('table tbody tr').length + 1}</td>
            <td><span class="api-key">${shortKey}</span></td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>${key.sites_count}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary api-action" data-action="toggle-status">
                        <i class="fas fa-arrows-turn-to-dots"></i>
                    </button>
                    <button class="btn btn-outline-danger api-action" data-action="delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;

        $('table tbody').append(newRow);
        $(`tr[data-api-key="${key.id}"]`).hide().fadeIn(300);

        // Обновляем счетчик
        updateKeysCounter();
    }

// Обновление счетчика ключей
    function updateKeysCounter() {
        const count = $('table tbody tr').length;
        $('.key-count').text(count);
    }

// Очистка модалки при закрытии
    $('#addApiKeyModal').on('hidden.bs.modal', function() {
        $('#apiKeyValue').val('');
    });

    // Обработчики действий ================================================

    /**
     * Инициализация обработчика действий (удаление, старт, стоп и т.д.)
     * @param {string} selector - Селектор кнопки
     * @param {string} type - Тип объекта (site/key)
     */
// ПРАВИЛЬНЫЙ КОД для script.js
    const setupActionHandler = (selector, type) => {
        $(document).on('click', selector, function(e) {
            e.preventDefault(); // Предотвращаем стандартное поведение кнопки

            const action = $(this).data('action');
            const $targetElement = type === 'site'
                ? $(this).closest('.accordion-item')
                : $(this).closest('tr');

            // Для добавления ссылок особый случай
            if (action === 'add-links') {
                currentActionContext = {
                    type: 'site',
                    action: action,
                    targetId: $targetElement.data('site-id'),
                    targetName: $targetElement.find('.accordion-button span:first').text().trim()
                };
                $('#currentSiteName').text(currentActionContext.targetName);
                e.stopPropagation();
                return;
            }

            currentActionContext = {
                type: type,
                action: action,
                targetId: type === 'site'
                    ? $targetElement.data('site-id')
                    : $targetElement.data('api-key'),
                targetName: type === 'site'
                    ? $targetElement.find('.accordion-button span:first').text().trim()
                    : $targetElement.find('.api-key').text().trim()
            };

            // === БЫСТРОЕ ДЕЙСТВИЕ БЕЗ МОДАЛКИ ДЛЯ СТАРТ/СТОП ===
            if (type === 'site' && (action === 'start' || action === 'stop')) {
                const formData = new FormData();
                formData.append('site_id', currentActionContext.targetId);

                const $btn = $(this); // Сохраняем кнопку для замены

                handleApiRequest({
                    url: API_ENDPOINTS.sites[action],
                    method: 'POST',
                    data: formData,
                    buttonSelector: $btn, // Лоадер повесится прямо на эту кнопку
                    onSuccess: (response) => {
                        showAlert('success', getSuccessMessage(type, action, currentActionContext.targetName));

                        const $accordionItem = $(`.accordion-item[data-site-id="${currentActionContext.targetId}"]`);
                        const $badge = $accordionItem.find('.site-status-badge');

                        if (action === 'start') {
                            // Добавили bg-warning в список на удаление
                            $badge.removeClass('bg-secondary bg-info bg-warning text-dark').addClass('bg-success').text('Активно');
                            $btn.replaceWith(`
                                <button class="btn btn-sm btn-warning site-action" data-action="stop">
                                    <i class="fas fa-stop me-1"></i>Остановить
                                </button>
                            `);
                        } else if (action === 'stop') {
                            // Добавили bg-warning в список на удаление
                            $badge.removeClass('bg-success bg-info bg-warning text-dark').addClass('bg-secondary').text('Остановлен');
                            $btn.replaceWith(`
                                <button class="btn btn-sm btn-success site-action" data-action="start">
                                    <i class="fas fa-play me-1"></i>Запустить
                                </button>
                            `);
                        }
                    }
                });

                return; // Выходим из функции, чтобы код ниже (модалка) не сработал
            }
            // ===================================================

            // Подготовка модального окна подтверждения для остальных действий (Удаление, Ключи)
            const messages = {
                site: {
                    delete: {
                        title: 'Удаление сайта',
                        message: `Вы уверены, что хотите удалить сайт <strong>${currentActionContext.targetName}</strong>? Это действие нельзя отменить.`
                    }
                },
                key: {
                    'toggle-status': {
                        title: 'Изменение статуса ключа',
                        message: `Вы уверены, что хотите изменить статус ключа <strong>${currentActionContext.targetName}</strong>?`
                    },
                    delete: {
                        title: 'Удаление API ключа',
                        message: `Вы уверены, что хотите удалить ключ <strong>${currentActionContext.targetName}</strong>? Это действие нельзя отменить.`
                    }
                }
            };

            const { title, message } = messages[type][action];
            $('#confirmModalTitle').html(title);
            $('#confirmModalBody').html(message);
            $('#confirmModal').modal('show');
        });
    };

    // Инициализация обработчиков действий
    setupActionHandler('.site-action', 'site');
    setupActionHandler('.api-action', 'key');

    // Подтверждение действия
    // Подтверждение действия (теперь только для удаления сайтов и работы с ключами)
    $('#confirmAction').click(async () => {
        const { type, action, targetId } = currentActionContext;

        if (!targetId || isNaN(targetId)) {
            showAlert('danger', 'Ошибка: неверный идентификатор');
            return;
        }

        let url, data;

        if (type === 'site') {
            url = API_ENDPOINTS.sites[action];
            data = new FormData();
            data.append('site_id', targetId);

            if (action === 'delete') {
                const currentPage = new URLSearchParams(window.location.search).get('p') || 1;
                data.append('current_page', currentPage);
            }
        } else {
            url = API_ENDPOINTS.keys[action === 'toggle-status' ? 'toggleStatus' : action];
            data = new FormData();
            data.append('api_key_id', targetId);
        }

        const response = await handleApiRequest({
            url,
            data,
            method: 'POST',
            buttonSelector: '#confirmAction',
            onSuccess: (response) => {
                $('#confirmModal').modal('hide');

                if (action === 'toggle-status' && response.is_active == '2') {
                    showAlert('danger', getSuccessMessage(type, 'first-auth', currentActionContext.targetName));
                } else {
                    showAlert('success', getSuccessMessage(type, action, currentActionContext.targetName));
                }

                if (type === 'site' && action === 'delete') {
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 800);
                    } else {
                        // Плавно удаляем строку сайта, если редирект не нужен
                        $(`.accordion-item[data-site-id="${targetId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                }
                else if (type === 'key') {
                    if (action === 'delete') {
                        $(`tr[data-api-key="${targetId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            $(`tr#details-${targetId}`).remove();
                            $(`#selApiKeys option[value="${targetId}"]`).remove();
                            updateKeysCounter();
                        });
                    }
                    else if (action === 'toggle-status') {
                        const statusText = { 1: 'Active', 0: 'Inactive' };
                        const $row = $(`tr[data-api-key="${targetId}"]`);
                        const $badge = $row.find('.badge');
                        const isActive = response.is_active;

                        if (isActive == '1') {
                            let tokDat = $(`#clid-data-${targetId}`).data('token');
                            $('#selApiKeys').append($('<option>', { value: targetId, text: tokDat.substring(0, 5) + '...' + tokDat.slice(-5)  }));
                        } else if (isActive == '0') {
                            $(`#selApiKeys option[value="${targetId}"]`).remove();
                        }

                        $badge.removeClass('bg-success bg-secondary bg-warning')
                            .addClass(isActive == '1' ? 'bg-success' : 'bg-secondary')
                            .text(statusText[isActive]);
                    }
                }
            }
        });
    });

// Вспомогательная функция для сообщений
    function getSuccessMessage(type, action, name) {
        const messages = {
            site: {
                start: `Сайт "${name}" успешно запущен`,
                stop: `Сайт "${name}" успешно остановлен`,
                delete: `Сайт "${name}" успешно удален`
            },
            key: {
                'toggle-status': `Статус ключа "${name}" изменен`,
                'first-auth': `Сперва авторизуй токен`,
                delete: `Ключ "${name}" успешно удален`
            }
        };
        return messages[type][action];
    }

    // Обработчик обновления статусов всех ключей
    $('#refreshAllStatuses').click(() => handleApiRequest({
        url: API_ENDPOINTS.keys.refreshAll,
        buttonSelector: '#refreshAllStatuses',
        successMessage: 'Статусы всех ключей обновлены',
        errorMessage: 'Ошибка при обновлении статусов',
        onSuccess: () => setTimeout(() => location.reload(), 1000)
    }));

    // Очистка модальных окон при закрытии
    $('.modal').on('hidden.bs.modal', () => {
        currentActionContext = {
            type: null,
            action: null,
            targetId: null,
            targetName: null
        };
    });
});