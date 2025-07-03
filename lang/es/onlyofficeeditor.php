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
 * Strings for component 'onlyofficeeditor', language 'es'.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['banner_description'] = 'Inicie fácilmente los editores en la nube sin tener que descargarlos ni instalarlos';
$string['banner_link_title'] = 'Obtener ahora';
$string['banner_title'] = 'ONLYOFFICE Docs Cloud';
$string['checkdocserverbutton'] = 'Comprobar la conexión a Docs';
$string['connectionerror'] = 'Error de conexión';
$string['connectionerror:command'] = 'Error al intentar comprobar CommandService';
$string['connectionerror:convert'] = 'Error al intentar comprobar ConvertService';
$string['connectionerror:unexpected'] = 'Se ha producido un error inesperado al comprobar la conexión';
$string['connectionsuccess'] = 'La conexión es estable';
$string['disable_verify_ssl'] = 'Desactivar la verificación de certificados (inseguro)';
$string['disable_verify_ssl:description'] = 'Utilizar solo al acceder al Servidor de documentos con un certificado autofirmad';
$string['docserverunreachable'] = 'No se puede acceder al Servidor de Documentos de ONLYOFFICE. Por favor, póngase en contacto con el administrador';
$string['documentpermissions'] = 'Permisos para los documentos';
$string['documentservererror'] = 'No se puede conectar a ONLYOFFICE Docs. Por favor, compruebe si el servidor está funcionando y accesible.';
$string['documentserverinternal'] = 'Dirección de ONLYOFFICE Docs para solicitudes internas del servidor';
$string['documentserverinternal:description'] = 'Fill in only when using internal routing between the Moodle server and ONLYOFFICE Docs, without relying on public addresses';
$string['documentserversecret'] = 'Secreto del Servidor de Documentos';
$string['documentserversecret_desc'] = 'El secreto se utiliza para generar el token (una firma cifrada) en el navegador para que el editor de documentos abra y llame a los métodos y las solicitudes al servicio de comando de documentos y al servicio de conversión de documentos. El token bloquea la sustitución de parámetros importantes en las solicitudes del Servidor de Documentos de ONLYOFFICE.';
$string['documentserverurl'] = 'Dirección del Servicio de Edición de Documentos';
$string['documentserverurl_desc'] = 'La Dirección del Servicio de Edición de Documentos especifica la dirección del servidor con los servicios de documentos instalados. Por favor, reemplace \'https://documentserver.url\' arriba con la dirección correcta del servidor';
$string['docxformname'] = 'Documento';
$string['download'] = 'Documento puede descargarse';
$string['download_help'] = 'Si está desactivado, los documentos no se podrán descargar en la aplicación del editor ONLYOFFICE. Tenga en cuenta que los usuarios con capacidad para <strong>course:manageactivities</strong> siempre pueden descargar documentos a través de la aplicación.';
$string['editor_security'] = 'Seguridad';
$string['editor_security_macros'] = 'Ejecutar macros de documentos';
$string['editor_security_plugin'] = 'Habilitar plugins';
$string['editor_view'] = 'Ajustes del editor';
$string['editor_view_chat'] = 'Mostrar el botón de Chat';
$string['editor_view_description'] = 'Learn more about customizing the editor <a href="{$a->url}" target="_blank">here</a>.';
$string['editor_view_feedback'] = 'Mostrar el botón de Feedback y Soporte';
$string['editor_view_header'] = 'Mostrar el encabezado más compacto';
$string['editor_view_help'] = 'Mostrar el botón de Ayuda';
$string['editor_view_toolbar'] = 'Mostrar el encabezado monocromático de la barra de herramientas';
$string['editorenterfullscreen'] = 'Abrir pantalla completa';
$string['editorexitfullscreen'] = 'Salir de pantalla completa';
$string['forcesave'] = 'Habilitar Forzar guardar';
$string['jwtheader'] = 'Encabezado de autenticación';
$string['mentioncontexturlname'] = 'Enlace al comentario.';
$string['mentionnotifier:notification'] = '{$a->notifier} mencionó en {$a->course}';
$string['messageprovider:mentionnotifier'] = 'Notificación de menciones de ONLYOFFICE en el documento del módulo.';
$string['modulename'] = 'Documento de ONLYOFFICE';
$string['modulename_help'] = 'El módulo ONLYOFFICE permite a los usuarios crear y editar documentos de oficina almacenados localmente en Moodle utilizando el Servidor de Documentos de ONLYOFFICE, haciendo posible que varios usuarios colaboren en tiempo real y guarden los cambios en Moodle.

Para más información, visite <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">nuestro Centro de ayuda</a>.';
$string['modulenameplural'] = 'Documentos de ONLYOFFICE';
$string['oldversion'] = 'Por favor, actualice ONLYOFFICE Docs a la versión 7.0 para poder trabajar con formularios rellenables en línea';
$string['onlyofficeactivityicon'] = 'Abrir en ONLYOFFICE';
$string['onlyofficeeditor:addinstance'] = 'Añadir una nueva actividad documental de ONLYOFFICE';
$string['onlyofficeeditor:editdocument'] = 'Editar la actividad documental de ONLYOFFICE';
$string['onlyofficeeditor:view'] = 'Ver la actividad documental de ONLYOFFICE';
$string['onlyofficename'] = 'Nombre de la actividad';
$string['onmentionerror'] = 'Error al mencionar.';
$string['pdfformname'] = 'Formulario PDF';
$string['pluginadministration'] = 'Administración de la actividad documental de ONLYOFFICE';
$string['pluginname'] = 'Documento de ONLYOFFICE';
$string['pptxformname'] = 'Presentación';
$string['print'] = 'Documento puede imprimirse';
$string['print_help'] = 'Si está desactivado, los documentos no se podrán imprimir en la aplicación del editor ONLYOFFICE. Tenga en cuenta que los usuarios con capacidad para <strong>course:manageactivities</strong> siempre pueden imprimir documentos a través de la aplicación.';
$string['privacy:metadata'] = 'No se almacena ninguna información sobre los datos personales de los usuarios.';
$string['privacy:metadata:onlyofficeeditor'] = 'Información sobre los documentos editados con ONLYOFFICE.';
$string['privacy:metadata:onlyofficeeditor:core_files'] = 'La actividad documental de ONLYOFFICE almacena los documentos que se editaron.';
$string['privacy:metadata:onlyofficeeditor:course'] = 'Curso al que pertenece la actividad de ONLYOFFICE';
$string['privacy:metadata:onlyofficeeditor:intro'] = 'Introducción general de la actividad de ONLYOFFICE';
$string['privacy:metadata:onlyofficeeditor:introformat'] = 'Formato del campo de introducción (MOODLE, HTML, MARKDOWN...).';
$string['privacy:metadata:onlyofficeeditor:name'] = 'Nombre de la actividad de ONLYOFFICE.';
$string['privacy:metadata:onlyofficeeditor:permissions'] = 'Permisos para los documentos.';
$string['privacy:metadata:onlyofficeeditor:userid'] = 'El ID de usuario actual no se envía al editor ONLYOFFICE.';
$string['protect'] = 'Ocultar la pestaña Protección';
$string['protect_help'] = 'Si está desactivado, los usuarios tienen acceso a la configuración de protección en el editor ONLYOFFICE. Tenga en cuenta que los usuarios con capacidad para <strong>course:manageactivities</strong> siempre tienen acceso a la configuración de protección.';
$string['readmore'] = 'Más información';
$string['returntodocument'] = 'Volver a la página del curso';
$string['saveasbutton'] = 'Seleccionar';
$string['saveaserror'] = 'Se ha producido un error.';
$string['saveassuccess'] = 'El documento se ha guardado correctamente.';
$string['saveastitle'] = 'Elija la sección del curso para guardar el documento';
$string['savewarning'] = 'Don\'t forget to Save changes at the bottom of the page';
$string['selectfile'] = 'Seleccione un archivo existente o cree uno nuevo haciendo clic en uno de los iconos';
$string['settingsintro'] = 'Edit and collaborate on office documents online directly within Moodle course structure. Share documents for viewing or real-time co-editing. Create, share and grade students\' assignments.';
$string['storageurl'] = 'Dirección de servidor para solicitudes internas del ONLYOFFICE Docs';
$string['suggestfeature'] = 'Sugerir una función';
$string['unsupportedfileformat'] = 'Formato de archivo no compatible';
$string['uploadformname'] = 'Subir archivo';
$string['validationerror:apijsunavailable'] = 'No se puede recuperar el archivo JavaScript de la API.';
$string['validationerror:documentserverunreachable'] = 'No se puede conectarse a ONLYOFFICE Docs. Por favor, compruebe si el servidor está funcionando y accesible.';
$string['validationerror:emptyurl'] = 'La URL de ONLYOFFICE Docs no puede estar vacía.';
$string['validationerror:incorrectjwtheader'] = 'No se puede conectarse a ONLYOFFICE Docs. Por favor, compruebe si el encabezado de autorización es correcto.';
$string['validationerror:incorrectsecret'] = 'No se puede conectar a ONLYOFFICE Docs. Por favor, compruebe si la clave secreta es correcta.';
$string['validationerror:invalidurl'] = 'The URL is invalid. Please check the URL format.';
$string['validationerror:mixedcontent'] = 'Contenido mixto activo no está permitido. Se requiere la dirección HTTPS para ONLYOFFICE Docs.';
$string['xlsxformname'] = 'Libro de Excel';
