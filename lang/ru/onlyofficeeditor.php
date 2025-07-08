<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'onlyofficeeditor', language 'ru'.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['banner_description'] = 'Легко запускайте редакторы в облаке без скачивания и установки';
$string['banner_link_title'] = 'Получить сейчас';
$string['banner_title'] = 'ONLYOFFICE Docs Cloud';
$string['checkdocserverbutton'] = 'Проверьте подключение Docs';
$string['connectionerror'] = 'Ошибка соединения';
$string['connectionerror:command'] = 'Ошибка при попытке проверить CommandService';
$string['connectionerror:convert'] = 'Ошибка при попытке проверить ConvertService';
$string['connectionerror:unexpected'] = 'При проверке соединения произошла непредвиденная ошибка';
$string['connectionsuccess'] = 'Соединение стабильное';
$string['disable_verify_ssl'] = 'Отключить проверку сертификата (небезопасно)';
$string['disable_verify_ssl:description'] = 'Использовать только при доступе к Серверу документов с самоподписанным сертификатом';
$string['docserverunreachable'] = 'Сервер документов ONLYOFFICE недоступен. Пожалуйста, свяжитесь с администратором';
$string['documentpermissions'] = 'Разрешения на действия с документом';
$string['documentservererror'] = 'Невозможно подключиться к ONLYOFFICE Docs. Проверьте, запущен ли сервер и доступен ли он.';
$string['documentserverinternal'] = 'Адрес ONLYOFFICE Docs для внутренних запросов сервера';
$string['documentserverinternal:description'] = 'Заполнять только при использовании внутренней маршрутизации между сервером Moodle и ONLYOFFICE Docs без ссылок на публичные адреса';
$string['documentserversecret'] = 'Секретный ключ Сервера документов';
$string['documentserversecret_desc'] = 'Секретный ключ используется для генерирования токена (шифрованной подписи) в браузере, чтобы открыть редактор документов и вызвать методы и запросы к сервису управления документами и сервису преобразования документов. Токен предотвращает замену важных параметров в запросах от Сервера документов ONLYOFFICE.';
$string['documentserverurl'] = 'Адрес службы редактирования документов';
$string['documentserverurl_desc'] = 'Адрес службы редактирования документов определяет адрес сервера с установленными сервисами документов. Пожалуйста, измените \'https://documentserver.url\' выше на верный адрес сервера';
$string['docxformname'] = 'Документ';
$string['download'] = 'Документ может быть скачан';
$string['download_help'] = 'Если данная функция отключена, документы не будут скачиваться в приложении ONLYOFFICE editor. Пожалуйста, обратите внимание, что пользователи с уровнем доступа <strong>course:manageactivities</strong> при этом могут скачивать документы с помощью приложения';
$string['editor_security'] = 'Безопасность';
$string['editor_security_macros'] = 'Запускать макросы документа';
$string['editor_security_plugin'] = 'Включить работу с плагинами';
$string['editor_view'] = 'Настройка редактора';
$string['editor_view_chat'] = 'Отображать кнопку чата';
$string['editor_view_description'] = 'Узнайте больше о настройке редактора <a href="{$a->url}" target="_blank">здесь</a>.';
$string['editor_view_feedback'] = 'Отображать кнопку Обратной связи и поддержки';
$string['editor_view_header'] = 'Отображать заголовок компактным';
$string['editor_view_help'] = 'Отображать кнопку справки';
$string['editor_view_toolbar'] = 'Отображать монохромный заголовок панели инструментов';
$string['editorenterfullscreen'] = 'Открыть в полноэкранном режиме';
$string['editorexitfullscreen'] = 'Выйти из полноэкранного режима';
$string['forcesave'] = 'Включить Force Save';
$string['jwtheader'] = 'Заголовок авторизации';
$string['mentioncontexturlname'] = 'Ссылка на комментарий.';
$string['mentionnotifier:notification'] = '{$a->notifier} упоминается в {$a->course}';
$string['messageprovider:mentionnotifier'] = 'Уведомление с упоминанием ONLYOFFICE в модуле Документы.';
$string['modulename'] = 'Документ ONLYOFFICE';
$string['modulename_help'] = 'Модуль ONLYOFFICE позволяет пользователям создавать и редактировать офисные документы, которые хранятся локально, в Moodle с помощью Сервера документов ONLYOFFICE, а также предоставляет возможность нескольким пользователям работать совместно в режиме реального времени и сохранять изменения в Moodle

Чтобы узнать больше, перейдите в <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">Справочный центр</a>.';
$string['modulenameplural'] = 'Документы ONLYOFFICE';
$string['oldversion'] = 'Обновите сервер ONLYOFFICE Docs до версии 7.0 для работы с формами онлайн';
$string['onlyofficeactivityicon'] = 'Открыть в ONLYOFFICE';
$string['onlyofficeeditor:addinstance'] = 'Добавить новый элемент Документ ONLYOFFICE';
$string['onlyofficeeditor:editdocument'] = 'Редактирование элемента Документ ONLYOFFICE';
$string['onlyofficeeditor:view'] = 'Просмотр элемента Документ ONLYOFFICE';
$string['onlyofficename'] = 'Наименование элемента';
$string['onmentionerror'] = 'Ошибка при упоминании.';
$string['pdfformname'] = 'PDF-форма';
$string['pluginadministration'] = 'Управление элементом Документ ONLYOFFICE';
$string['pluginname'] = 'Документ ONLYOFFICE';
$string['pptxformname'] = 'Презентация';
$string['print'] = 'Документ может быть напечатан';
$string['print_help'] = 'Если данная функция отключена, документы не будут напечатаны с помощью приложения ONLYOFFICE editor. Пожалуйста, обратите внимание, что пользователи с уровнем доступа <strong>course:manageactivities</strong> при этом могут печатать документы с помощью приложения';
$string['privacy:metadata'] = 'Информация о персональных данных пользователей не сохраняется.';
$string['privacy:metadata:onlyofficeeditor'] = 'Сведения о документах, отредактированных с помощью ONLYOFFICE.';
$string['privacy:metadata:onlyofficeeditor:core_files'] = 'Элемент Документ ONLYOFFICE сохраняет отредактированные документы.';
$string['privacy:metadata:onlyofficeeditor:course'] = 'Курс, к которому относятся элементы ONLYOFFICE';
$string['privacy:metadata:onlyofficeeditor:intro'] = 'Общее представление элементов ONLYOFFICE';
$string['privacy:metadata:onlyofficeeditor:introformat'] = 'Формат поля ввода (MOODLE, HTML, MARKDOWN...).';
$string['privacy:metadata:onlyofficeeditor:name'] = 'Наименование элемента ONLYOFFICE.';
$string['privacy:metadata:onlyofficeeditor:permissions'] = 'Разрешения на действия с документом.';
$string['privacy:metadata:onlyofficeeditor:userid'] = 'Текущий ID пользователя не отправлен в ONLYOFFICE editor.';
$string['protect'] = 'Скрыть вкладку Защита';
$string['protect_help'] = 'Если данная функция отключена, пользователям предоставляется доступ к настройкам защиты в ONLYOFFICE editor. Пожалуйста, обратите внимание, что пользователям с уровнем доступа <strong>course:manageactivities</strong> при этом предоставлен доступ к настройкам защиты.';
$string['readmore'] = 'Подробнее';
$string['returntodocument'] = 'Вернуться к странице курса';
$string['saveasbutton'] = 'Выбрать';
$string['saveaserror'] = 'Что-то пошло не так.';
$string['saveassuccess'] = 'Документ был успешно сохранен.';
$string['saveastitle'] = 'Выберите раздел Курсы, чтобы сохранить документ';
$string['savewarning'] = 'Не забудьте сохранить изменения внизу страницы.';
$string['selectfile'] = 'Выберите существующий файл или создайте новый нажатием на одну из иконок';
$string['settingsintro'] = 'Редактируйте офисные документы и работайте над ними совместно онлайн прямо в структуре курса Moodle. Предоставляйте доступ к документам для просмотра или совместного редактирования в режиме реального времени. Создавайте задания для студентов, предоставляйте к ним доступ и оценивайте их.';
$string['storageurl'] = 'Адрес сервера для внутренних запросов ONLYOFFICE Docs';
$string['suggestfeature'] = 'Предложить функцию';
$string['unsupportedfileformat'] = 'Формат файла не поддерживается';
$string['uploadformname'] = 'Загрузить файл';
$string['validationerror:apijsunavailable'] = 'Невозможно получить файл API JavaScript.';
$string['validationerror:documentserverunreachable'] = 'Невозможно подключиться к ONLYOFFICE Docs. Проверьте, запущен ли сервер и доступен ли он.';
$string['validationerror:emptyurl'] = 'URL-адрес ONLYOFFICE Docs не может быть пустым.';
$string['validationerror:incorrectjwtheader'] = 'Невозможно подключиться к ONLYOFFICE Docs. Проверьте правильность заголовка авторизации.';
$string['validationerror:incorrectsecret'] = 'Невозможно подключиться к ONLYOFFICE Docs. Проверьте правильность секретного ключа.';
$string['validationerror:invalidurl'] = 'Недействительный URL. Проверьте формат URL.';
$string['validationerror:mixedcontent'] = 'Смешанное активное содержимое запрещено. Для ONLYOFFICE Docs необходимо использовать HTTPS-адрес.';
$string['xlsxformname'] = 'Таблица';
