
<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>


    /**
     * Функция для активации таба на основе текущего хеша в URL.
     */
    function activateTabFromHash() {
        const hash = window.location.hash;

        // Проверяем, что хеш существует, чтобы не было ошибок
        if (hash) {
            // Ищем кнопку таба, у которой `data-bs-target` соответствует хешу.
            // Например, для хеша #api будет найден элемент с `data-bs-target="#api"`.
            const tabTrigger = $('.nav-tabs button[data-bs-target="' + hash + '"]');

            // Если такой таб найден, программно "нажимаем" на него.
            if (tabTrigger.length) {
                // Используем нативный API Bootstrap 5 для показа таба.
                // Конструктору Tab нужен DOM-элемент, а не jQuery-объект, поэтому [0].
                const tab = new bootstrap.Tab(tabTrigger[0]);
                tab.show();
            }
        }
    }

    // Вызываем функцию при первой загрузке страницы.
    activateTabFromHash();

    /**
     * Обработчик, который обновляет хеш в URL при переключении табов.
     * Это позволяет копировать ссылку с уже открытым табом.
     */
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        // e.target - это активированная кнопка таба.
        const hash = $(e.target).data('bs-target');

        // Аккуратно обновляем хеш в URL без перезагрузки страницы.
        // history.replaceState предпочтительнее, чем pushState,
        // чтобы не засорять историю браузера каждым кликом по табу.
        if (history.replaceState) {
            history.replaceState(null, null, hash);
        } else {
            // Старый fallback
            window.location.hash = hash;
        }
    });

    $(document).ready(function() {




        // Обработчик для копирования токена
        $(document).on('click', '.copy-token', function(e) {
            e.stopPropagation();
            const $btn = $(this);
            const token = $btn.data('token'); // Правильное обращение к data-атрибуту

            // Создаем временный textarea для копирования
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(token).select();

            try {
                // Пытаемся использовать современный API
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(token).then(() => {
                        showCopyFeedback($btn);
                    }).catch(() => {
                        fallbackCopy($temp, $btn);
                    });
                } else {
                    // Fallback для старых браузеров
                    fallbackCopy($temp, $btn);
                }
            } catch (err) {
                fallbackCopy($temp, $btn);
            } finally {
                $temp.remove();
            }
        });

        function fallbackCopy($temp, $btn) {
            try {
                document.execCommand('copy');
                showCopyFeedback($btn);
            } catch (err) {
                alert('Не удалось скопировать токен. Скопируйте его вручную.');
            }
        }

        function showCopyFeedback($btn) {
            $btn.html('<i class="fas fa-check"></i> Скопировано');
            setTimeout(() => {
                $btn.html('<i class="fas fa-copy"></i> Копировать');
            }, 2000);
        }



        // Обновление иконок при открытии/закрытии аккордеона
        $('.accordion-collapse').on('show.bs.collapse', function () {
            const targetId = $(this).attr('id');
            $(`[data-bs-target="#${targetId}"] .accordion-icon`)
                .removeClass('fa-chevron-down')
                .addClass('fa-chevron-up');
        }).on('hide.bs.collapse', function () {
            const targetId = $(this).attr('id');
            $(`[data-bs-target="#${targetId}"] .accordion-icon`)
                .removeClass('fa-chevron-up')
                .addClass('fa-chevron-down');
        });
    });
</script>
<!-- Custom JS -->
<script src="/DATA/js/script.js"></script>

</body>
</html>